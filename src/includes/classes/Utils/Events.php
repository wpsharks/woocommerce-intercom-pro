<?php
/**
 * Event utils.
 *
 * @author @raamdev
 * @copyright WP Sharks™
 */
declare(strict_types=1);
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
 * @since 160909.7530 Initial release.
 */
class Events extends SCoreClasses\SCore\Base\Core
{
    /**
     * On `woocommerce_order_given` hook.
     *
     * @since 160909.7530 Initial release.
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
     * @since 160909.7530 Initial release.
     *
     * @param string|int $order_id   Order ID.
     * @param string     $old_status Old status prior to change.
     * @param string     $new_status The new status after this change.
     */
    public function onWcOrderStatusChanged($order_id, string $old_status, string $new_status)
    {
        if (in_array($new_status, ['processing', 'completed'], true)) {
            $this->eventCreate((int) $order_id, $new_status);
        }
    }

    /**
     * Intercom event create.
     *
     * @since 160909.7530 Initial release.
     *
     * @param string|int $order_id Order ID.
     * @param string     $status   The new status.
     */
    protected function eventCreate(int $order_id, string $status)
    {
        if (!($WC_Order = wc_get_order($order_id))) {
            debug(0, c::issue(vars(), 'Missing order.'));
            return; // Not possible; debug this.
        } elseif (!($app_id = s::getOption('app_id'))) {
            return; // Not possible.
        } elseif (!($api_token = s::getOption('api_token'))
                && !($api_key = s::getOption('api_key'))) {
            return; // Not possible.
        }
        # Instantiate `Intercom` class.

        if ($api_token) { // Prefer token.
            $Intercom = new IntercomClient($api_token, null);
        } else { // Backward compatibility.
            $Intercom = new IntercomClient($app_id, $api_key);
        }
        # Collect a few order variables needed below.

        $user_id       = (int) $WC_Order->get_user_id();
        $billing_email = (string) $WC_Order->billing_email;

        $total          = (float) $WC_Order->get_total();
        $payment_method = (string) $WC_Order->payment_method;
        $currency_code  = (string) $WC_Order->get_order_currency();

        if ($payment_method === 'stripe') {
            $stripe_customer_id = (string) $WC_Order->stripe_customer_id;
        } else {
            $stripe_customer_id = ''; // Not Stripe; not applicable.
        }
        $item_data = []; // Initialize; collect below.

        # Iterate all of the items in this order.

        foreach ($WC_Order->get_items() ?: [] as $_item_id => $_item) {
            if (!($_WC_Product = s::wcProductByOrderItemId($_item_id, $WC_Order))) {
                continue; // Not a product or not possible.
            }
            $_product['id']    = (int) $_WC_Product->get_id();
            $_product['title'] = (string) $_WC_Product->get_title();

            $_product['qty']   = (int) max(1, (int) ($_item['qty'] ?? 1));
            $_product['total'] = (string) wc_format_decimal($_item['line_total'] ?? 0);

            $_product['sku'] = (string) $_WC_Product->get_sku(); // Possible variation SKU.
            if (!$_product['sku'] && $_WC_Product->product_type === 'variation' && $_WC_Product->parent) {
                $_product['sku'] = (string) $_WC_Product->parent->get_sku();
            }
            $_product['slug'] = (string) $_WC_Product->post->post_name;

            if ($_product['sku']) {
                $item_data['skus'][] = $_product['sku'];
            } // Collect SKUs for reporting below.

            if ($_product['slug']) {
                $item_data['slugs'][] = $_product['slug'];
            } // Collect slugs for reporting below.

            /* Per-item event tracking has been disabled for now.

            $_event_metadata = [ // Max of five keys.
                // Leave a slot for Stripe Customer ID.
                'title' => $_product['title'],
                'sku'   => $_product['sku'],
                'slug'  => $_product['slug'],
                'price' => [
                    'currency' => $currency_code,
                    'amount'   => (int)($_product['total'] * 100),
                ],
            ];
            if ($stripe_customer_id) { // Add Stripe customer data if available.
                $_event_metadata['stripe_customer'] = $stripe_customer_id;
            }
            $_event_data = [ // For API call; this pulls everything together.
                // See: <https://developers.intercom.io/reference#submitting-events>
                'event_name' => 'item-'.$status,
                'created_at' => time(),
                'user_id'    => $user_id,
                'metadata'   => $_event_metadata,
            ];
            if (!$_event_data['user_id']) {
                unset($_event_data['user_id']);
                $_event_data['email'] = $billing_email;
            }
            $Intercom->events->create($_event_data);
            */
        } // unset($_item_id, $_item, $_WC_Product, $_product, $_event_metadata, $_event_data);

        # Now create a single `placed-order` event.

        $event_metadata = [ // Max of five keys.
            // Leave a slot for Stripe Customer ID.
             'order_number' => [
                 'value' => $order_id,
                 'url'   => admin_url('/post.php?post='.$order_id.'&action=edit'),
             ],
             'subtotal' => [
                 'currency' => $currency_code,
                 'amount'   => (int) ($total * 100),
             ],
        ];
        if ($item_data['skus']) {
            $event_metadata['skus'] = c::clip(implode(', ', $item_data['skus']), 255);
        } elseif ($item_data['slugs']) {
            $event_metadata['slugs'] = c::clip(implode(', ', $item_data['slugs']), 255);
        }
        if (($item_data['coupons'] = $WC_Order->get_used_coupons())) {
            $event_metadata['coupons'] = c::clip(implode(', ', $item_data['coupons']), 255);
        }
        if ($stripe_customer_id) { // Add Stripe customer data if available.
            $event_metadata['stripe_customer'] = $stripe_customer_id;
        }
        $event_data = [ // For API call; this pulls everything together.
            // See: <https://developers.intercom.io/reference#submitting-events>
            'event_name' => 'order-'.$status,
            'created_at' => time(),
            'user_id'    => $user_id,
            'metadata'   => $event_metadata,
        ];
        if (!$event_data['user_id']) {
            unset($event_data['user_id']);
            $event_data['email'] = $billing_email;
        }
        $Intercom->events->create($event_data);
    }
}
