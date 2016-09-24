<?php
/**
 * JS snippet utils.
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

/**
 * JS snippet utils.
 *
 * @since 160909.7530 Initial release.
 */
class JsSnippet extends SCoreClasses\SCore\Base\Core
{
    /**
     * On `wp_footer` hook.
     *
     * @since 160909.7530 Initial release.
     *
     * @TODO Create a plugin option that lets you define the
     *  scenarios where the widget should be loaded.
     */
    public function onWpFooter()
    {
        if (!($app_id = s::getOption('app_id'))) {
            return; // Not possible.
        } elseif (!($settings = $this->settings())) {
            return; // Not applicable.
        }
        echo '<script type="text/javascript">';
        echo    'window.intercomSettings = '.json_encode($settings).';';
        echo '</script>';

        $snippet      = c::getTemplate('site/intercom.html')->parse();
        echo $snippet = str_replace('%%app_id%%', $app_id, $snippet);
    }

    /**
     * Standard Attributes array.
     *
     * @since 160909.7530 Initial release.
     *
     * @return array Standard Attributes.
     */
    protected function standardAttributes(): array
    {
        if (is_user_logged_in()) {
            $WP_User = wp_get_current_user();

            return [ // Standard Intercom attributes.
                // <https://developers.intercom.io/reference#user-model>.
                'app_id'     => s::getOption('app_id'),
                'created_at' => strtotime($WP_User->user_registered),

                'type'    => 'user',
                'user_id' => $WP_User->ID,
                'email'   => $WP_User->user_email,
                'name'    => c::mbTrim($WP_User->first_name.' '.$WP_User->last_name),
            ];
        } else { // @TODO Add support for Intercom Engage?
            return [
                'app_id' => s::getOption('app_id'),
            ];
        }
    }

    /**
     * Custom Attributes array.
     *
     * @since 160909.7530 Initial release.
     *
     * @return array Custom Attributes.
     */
    protected function customAttributes(): array
    {
        if (is_user_logged_in()) {
            $WP_User = wp_get_current_user();

            $available_downloads = []; // Initialize.
            $downloads           = wc_get_customer_available_downloads($WP_User->ID);
            $downloads           = is_array($downloads) ? $downloads : [];

            foreach ($downloads as $_download) {
                $available_downloads[] = $_download['file']['name'];
            } // unset($_download); // Housekeeping.

            return [ // Intercom Custom Attributes.
                // See: <http://bit.ly/2aZvEtb> for details.

                'wp_login'     => $WP_User->user_login,
                'wp_roles'     => c::clip(implode(', ', $WP_User->roles), 255),
                'wp_user_edit' => admin_url('/user-edit.php?user_id='.$WP_User->ID),

                'total_orders' => wc_get_customer_order_count($WP_User->ID),
                'total_spent'  => sprintf('%0.2f', (float) wc_get_customer_total_spent($WP_User->ID)),
                // Padded value to 2 decimal places via `sprintf`, e.g. `0.00` or `5.50`.
                'available_downloads' => c::clip(implode(', ', $available_downloads), 255),

                // @TODO Add other WooCommerce-related user-data, such as products purchased.
            ];
        } else { // @TODO Add support for Intercom Engage?
            return [];
        }
    }

    /**
     * Settings array.
     *
     * @since 160909.7530 Initial release.
     *
     * @return array Settings.
     */
    protected function settings(): array
    {
        return array_merge($this->standardAttributes(), $this->customAttributes());
    }
}
