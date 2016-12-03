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
        $this->createOrderEvent('order-given', (int) $order_id, __FUNCTION__);
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
        if ($new_status && !in_array($new_status, ['draft', 'pending'], true)) {
            $this->createOrderEvent('order-'.$new_status, (int) $order_id, __FUNCTION__);
        }
    }

    /**
     * On `woocommerce_subscription_status_changed` hook.
     *
     * @since 16xxxx Adding support for Subscriptions.
     *
     * @param string|int $subscription_id Subscription ID.
     * @param string     $old_status      Old status prior to change.
     * @param string     $new_status      The new status after this change.
     */
    public function onWcSubscriptionStatusChanged($subscription_id, string $old_status, string $new_status)
    {
        if ($new_status && !in_array($new_status, ['draft', 'pending'], true)) {
            $this->createOrderEvent('subscription-'.$new_status, (int) $subscription_id, __FUNCTION__);
        }
    }

    /**
     * On `woocommerce_subscriptions_switched_item` hook.
     *
     * @since 16xxxx Adding support for Subscriptions.
     *
     * @param \WC_Subscription $WC_Subscription Subscription instance.
     * @param array            $new_item        The new item data.
     * @param array            $old_item        The old item data.
     */
    public function onWcSubscriptionItemSwitched(\WC_Subscription $WC_Subscription, array $new_item, array $old_item)
    {
        $this->createOrderEvent('subscription-switched', (int) $WC_Subscription->id, __FUNCTION__);
    }

    /**
     * Create Intercom order event.
     *
     * @since 160909.7530 Initial release.
     *
     * @param string     $event_name Event name.
     * @param string|int $order_id   WooCommerce order ID.
     * @param string     $via        Caller; i.e., event created by.
     */
    protected function createOrderEvent(string $event_name, int $order_id, string $via)
    {
        if (!($app_id = s::getOption('app_id'))) {
            return; // Not possible.
        } elseif (!($api_token = s::getOption('api_token'))) {
            return; // Not possible.
        } elseif (!$event_name) {
            debug(0, c::issue(vars(), 'Missing event name.'));
            return; // Not possible.
        } elseif (!($WC_Order = wc_get_order($order_id))) {
            debug(0, c::issue(vars(), 'Missing order.'));
            return; // Not possible.
        }
        $is_subscription = $WC_Order->order_type === 'shop_subscription';

        # Collect a few variables needed below.

        $current_time = time(); // UTC time.

        $wp_user_id = (int) $WC_Order->get_user_id();
        $WP_User    = new \WP_User($wp_user_id);

        $billing_email = (string) $WC_Order->billing_email;
        $billing_name  = c::mbTrim((string) $WC_Order->get_formatted_billing_full_name());

        $user_id = a::userId([
            'id'    => $WP_User->ID,
            'email' => $WP_User->user_email ?: $billing_email,
        ]);
        $user_info = [
            'id'    => $WP_User->ID,
            'name'  => $WP_User->display_name ?: $billing_name,
            'email' => $WP_User->user_email ?: $billing_email,
        ];
        # Make sure we have this user on the Intercom side.

        if (!a::userUpdate($user_info)) {
            return; // Not possible in this case.
        }
        # Collect additional variables needed below.

        $total          = (float) $WC_Order->get_total();
        $payment_method = (string) $WC_Order->payment_method;
        $currency_code  = (string) $WC_Order->get_order_currency();

        if ($payment_method === 'stripe') {
            $stripe_customer_id = (string) $WC_Order->stripe_customer_id;
        } else {
            $stripe_customer_id = ''; // Not Stripe; not applicable.
        }
        $item_data = []; // Initialize; collect below.

        # Instantiate `Intercom` class.

        try { // Catch Intercom exceptions.
            $Intercom = new IntercomClient($api_token, null);
        } catch (\Throwable $Exception) {
            c::review(vars(), 'Intercom client exception.');
            return; // Stop here; soft failure.
        }
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

            $_event_name = str_replace(
                ['order-', 'subscription-'],
                ['order-item-', 'subscription-item-'],
                $event_name
            ); // Rewrite as order or subscription 'item'.

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
                'user_id'    => $user_id,
                'event_name' => $_event_name,
                'created_at' => $current_time,
                'metadata'   => $_event_metadata,
            ];
            try { // Catch Intercom exceptions.
                $Intercom->events->create($_event_data);
            } catch (\Throwable $Exception) {
                c::review(vars(), 'Event creation exception.');
            }
            */
        } // unset($_item_id, $_item, $_WC_Product, $_product, $_event_metadata, $_event_data, $_event_name);

        # Now create a single `placed-order` event.

        $event_metadata = [ // Max of five keys.
            // Leave a slot for Stripe Customer ID.
             ($is_subscription ? 'subscription' : 'order').'_id' => [
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
            'user_id'    => $user_id,
            'event_name' => $event_name,
            'created_at' => $current_time,
            'metadata'   => $event_metadata,
        ];
        try { // Catch Intercom exceptions.
            $Intercom->events->create($event_data);
        } catch (\Throwable $Exception) {
            c::review(vars(), 'Event creation exception.');
        }
    }
}
