<?php
/**
 * Event utils.
 *
 * @author @raamdev
 * @copyright WP Sharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerceIntercom\Pro\Classes\Utils;

use WebSharks\WpSharks\WooCommerceIntercom\Pro\Classes;
use WebSharks\WpSharks\WooCommerceIntercom\Pro\Interfaces;
use WebSharks\WpSharks\WooCommerceIntercom\Pro\Traits;
#
use WebSharks\WpSharks\WooCommerceIntercom\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\WooCommerceIntercom\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WooCommerceIntercom\Pro\Classes\CoreFacades as c;
#
use WebSharks\WpSharks\Core\Classes as SCoreClasses;
use WebSharks\WpSharks\Core\Interfaces as SCoreInterfaces;
use WebSharks\WpSharks\Core\Traits as SCoreTraits;
#
use WebSharks\Core\WpSharksCore\Classes as CoreClasses;
use WebSharks\Core\WpSharksCore\Classes\Core\Base\Exception;
use WebSharks\Core\WpSharksCore\Interfaces as CoreInterfaces;
use WebSharks\Core\WpSharksCore\Traits as CoreTraits;
#
use function assert as debug;
use function get_defined_vars as vars;
#
use Intercom\IntercomClient;

/**
 * Event utils.
 *
 * @since 000000 Initial release.
 */
class Events extends SCoreClasses\SCore\Base\Core
{
    /**
     * On `woocommerce_order_given` hook.
     *
     * @since 000000 Initial release.
     *
     * @param string|int $order_id Order ID.
     */
    public function onWcOrderGiven($order_id)
    {
        $this->eventCreate((int) $order_id);
    }

    /**
     * On `woocommerce_order_status_changed` hook.
     *
     * @since 000000 Initial release.
     *
     * @param string|int $order_id   Order ID.
     * @param string     $old_status Old status prior to change.
     * @param string     $new_status The new status after this change.
     */
    public function onWcOrderStatusChanged($order_id, string $old_status, string $new_status)
    {
        if (in_array($new_status, ['processing', 'completed'], true)) {
            $this->eventCreate((int) $order_id);
        }
    }

    /**
     * Intercom event create.
     *
     * @since 000000 Initial release.
     *
     * @param string|int $order_id Order ID.
     */
    protected function eventCreate(int $order_id)
    {
        if (!($WC_Order = wc_get_order($order_id))) {
            debug(0, c::issue(vars(), 'Could not acquire order.'));
            return; // Not possible.
        }
        $app_id   = s::getOption('app_id');
        $api_key  = s::getOption('api_key');
        $Intercom = new IntercomClient($app_id, $api_key);

        $user_id = (int) $WC_Order->get_user_id();

        $payment_method       = (string) $WC_Order->payment_method; // e.g., `stripe`.
        $currency_code        = (string) $WC_Order->get_currency(); // e.g., `USD`.

        if ($payment_method === 'stripe') {
            $stripe_customer_id = (string) $WC_Order->stripe_customer_id; // e.g., `cus_xxxx`.
        } else {
            $stripe_customer_id = '';
        }

        $_event_metadata = [ // Maximum of five metadata key values; leave room for possible Stripe Customer ID
                             'order_number' => $WC_Order->get_order_number(),
                             'order_status'   => $WC_Order->get_status(),
                             'payment_method'  => $payment_method,
                             'subtotal' => [
                                 'currency' => $currency_code,
                                 'amount'   => $WC_Order->get_subtotal(),
                             ],
        ];

        if (!empty($stripe_customer_id)) { // Add Stripe Customer Data if available
            $_event_metadata['stripe_customer'] = $stripe_customer_id;
        }

        $Intercom->events->create([
            'created_at' => time(),
            'event_name' => 'order',
            'user_id'    => $user_id,
            'metadata'   => $_event_metadata, // See: <https://developers.intercom.io/reference#event-metadata-types>
        ]);

        foreach ($WC_Order->get_items() ?: [] as $_item_id => $_item) {
            if (!($_WC_Product = s::wcProductByOrderItemId($_item_id, $WC_Order))) {
                continue; // Not a product or not possible.
            }
            $_product['id']    = (int) $_WC_Product->get_id();
            $_product['title'] = (string) $_WC_Product->get_title();

            $_product['sku'] = (string) $_WC_Product->get_sku();
            if (!$_product['sku'] && $_WC_Product->product_type === 'variation' && $_WC_Product->parent) {
                $_product['sku'] = (string) $_WC_Product->parent->get_sku();
            }
            $_product['slug'] = (string) $_WC_Product->post->post_name;

            $_product['qty']   = (int) max(1, (int) ($_item['qty'] ?? 1));
            $_product['total'] = (string) wc_format_decimal($_item['line_total'] ?? 0);

            $_event_metadata = [ // Maximum of five metadata key values.
                'title' => $_product['title'],
                'sku'   => $_product['sku'],
                'slug'  => $_product['slug'],
                'price' => [
                    'currency' => $currency_code,
                    'amount'   => $_product['total'],
                ],
            ];

            if (!empty($stripe_customer_id)) { // Add Stripe Customer Data if available
                $_event_metadata['stripe_customer'] = $stripe_customer_id;
            }

            $Intercom->events->create([
                'created_at' => time(),
                'event_name' => 'purchased-item',
                'user_id'    => $user_id, // Only User ID or Email, not both.
                'metadata'   => $_event_metadata, // See: <https://developers.intercom.io/reference#event-metadata-types>
            ]);
        } unset($_item_id, $_item, $_product, $_event_metadata); // Housekeeping.
    }
}
