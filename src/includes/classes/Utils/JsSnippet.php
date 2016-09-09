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
 * @since 000000 Initial release.
 */
class JsSnippet extends SCoreClasses\SCore\Base\Core
{
    /**
     * On `wp_footer` hook.
     *
     * @since 000000 Initial release.
     */
    public function onWpFooter()
    {
        echo '<script type="text/javascript">';
        echo    'window.intercomSettings = '.json_encode($this->settings()).';';
        echo '</script>';
        // @TODO Create a plugin option that lets you define the scenarios where the widget should be loaded
        echo '<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic(\'reattach_activator\');ic(\'update\',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement(\'script\');s.type=\'text/javascript\';s.async=true;s.src=\'https://widget.intercom.io/widget/'.s::getOption('app_id').'\';var x=d.getElementsByTagName(\'script\')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent(\'onload\',l);}else{w.addEventListener(\'load\',l,false);}}})()</script>';

        // echo snippet.
    }

    /**
     * Settings array.
     *
     * @since 000000 Initial release.
     *
     * @return array Settings.
     */
    protected function settings(): array
    {
        return array_merge($this->standardAttributes(), $this->customAttributes());
    }

    /**
     * Standard Attributes array.
     *
     * @since 000000 Initial release.
     *
     * @return array Standard Attributes.
     */
    protected function standardAttributes(): array
    {
        if (is_user_logged_in()) { // Only logged-in users
            $current_user = wp_get_current_user();

            return [ // Intercom Standard Attributes https://developers.intercom.io/reference#user-model
                     'app_id'     => s::getOption('app_id'), // Intercom App ID
                     'type'       => 'user',
                     'user_id'    => $current_user->ID,
                     'email'      => $current_user->user_email,
                     'name'       => $current_user->user_firstname.' '.$current_user->user_lastname,
                     'created_at' => strtotime($current_user->user_registered),
            ];
        } else { // Not logged in. @TODO Add support for Intercom Engage?
            return [
                'app_id' => s::getOption('app_id'), // Intercom App ID
            ];
        }
    }

    /**
     * Custom Attributes array.
     *
     * @since 000000 Initial release.
     *
     * @return array Custom Attributes.
     */
    protected function customAttributes(): array
    {
        if (is_user_logged_in()) { // Only logged-in users
            $current_user = wp_get_current_user();

            $available_downloads = ''; // Initialize.
            if (!empty($_wc_downloads = wc_get_customer_available_downloads($current_user->ID))) {
                foreach ($_wc_downloads as $_download) {
                    $available_downloads[] = $_download['file']['name'];
                }
            } unset($_wc_downloads, $_download); // Housekeeping.

            return [ // Intercom Custom Attributes http://bit.ly/2aZvEtb
                     'wp_login'            => $current_user->user_login,
                     'wp_user_edit'        => admin_url('user-edit.php?user_id='.$current_user->ID),
                     'available_downloads' => c::clip(implode(', ', $available_downloads), 255),
                     'total_spent'         => sprintf('%0.2f', (float) wc_get_customer_total_spent($current_user->ID)), // Padded value to 2 decimal places, e.g. 0.00 or 5.50.
                     'total_orders'        => wc_get_customer_order_count($current_user->ID),
                     'wp_roles'            => c::clip(implode(', ', $current_user->roles), 255),

                     // @TODO Add other WooCommerce-related user-data, such as products purchased
            ];
        } else { // Not logged in. @TODO Add support for Intercom Engage?
            return [];
        }
    }
}
