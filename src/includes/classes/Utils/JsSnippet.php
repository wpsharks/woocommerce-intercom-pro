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
        echo '<script type="text/javascript>';
        echo    'window.intercomSettings = '.json_encode($this->settings()).';';
        echo '</script>';
        // @TODO Create a plugin option that lets you define the scenarios where the widget should be loaded
        echo '<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic(\'reattach_activator\');ic(\'update\',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement(\'script\');s.type=\'text/javascript\';s.async=true;s.src=\'https://widget.intercom.io/widget/' . s::getOption('app_id') . '\';var x=d.getElementsByTagName(\'script\')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent(\'onload\',l);}else{w.addEventListener(\'load\',l,false);}}})()</script>';

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
        if (is_user_logged_in()) { // Only logged-in users
            $current_user = wp_get_current_user();

            return [ // Information about logged in user that should be appear as Custom Attributes

                     // Intercom Standard Attributes https://developers.intercom.io/reference#user-model
                     'app_id'            => s::getOption('app_id'), // Intercom App ID
                     'type'              => 'user',
                     'id'                => $current_user->user_login,
                     'email'             => $current_user->user_email,
                     'name'              => $current_user->user_firstname.' '.$current_user->user_lastname,

                     // Intercom Custom Attributes http://bit.ly/2aZvEtb
                     'wp_roles'          => implode(', ', $current_user->roles),
                     'wp_edit_user_link' => get_edit_user_link($current_user->ID),

                     // @TODO Add other WooCommerce-related user-data, such has products purchased
            ];
        } else { // Not logged in. @TODO Add support for Intercom Engage?
            return [
                'app_id' => s::getOption('app_id'), // Intercom App ID
            ];
        }

    }
}
