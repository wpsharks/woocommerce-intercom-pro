<?php
/**
 * JS snippet utils.
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
     */
    public function onWpFooter()
    {
        if (!($app_id = s::getOption('app_id'))) {
            return; // Not possible.
        } elseif (!$this->enabled()) {
            return; // Not applicable.
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
                'app_id' => s::getOption('app_id'),

                'type'       => 'user',
                'user_id'    => $WP_User->ID,
                'created_at' => strtotime($WP_User->user_registered),

                'email' => c::clip($WP_User->user_email, 255),
                'name'  => c::clip(c::mbTrim($WP_User->first_name.' '.$WP_User->last_name), 255),
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

                'wp_site' => c::midClip(home_url('/'), 255),
                'wp_uri'  => c::midClip(c::currentUri(), 255),

                'wp_login'     => c::clip($WP_User->user_login, 255),
                'wp_roles'     => c::clip(implode(', ', $WP_User->roles), 255),
                'wp_user_edit' => c::midClip(admin_url('/user-edit.php?user_id='.$WP_User->ID), 255),

                'total_orders' => wc_get_customer_order_count($WP_User->ID),
                'total_spent'  => sprintf('%0.2f', (float) wc_get_customer_total_spent($WP_User->ID)),
                // Padded value to 2 decimal places via `sprintf`, e.g. `0.00` or `5.50`.
                'available_downloads' => c::clip(implode(', ', $available_downloads), 255),

                // @TODO Add other WooCommerce-related user-data, such as products purchased.
            ];
        } else { // @TODO Add support for Intercom Engage?
            return [
                'wp_site' => c::midClip(home_url('/'), 255),
                'wp_uri'  => c::midClip(c::currentUri(), 255),
            ];
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

    /**
     * JS snippet enabled?
     *
     * @since 16xxxx Display options.
     *
     * @return bool True if enabled.
     */
    protected function enabled(): bool
    {
        $uri = c::currentUri();

        $uri_inclusions = c::wRegx(s::getOption('uri_inclusions'));
        $uri_exclusions = c::wRegx(s::getOption('uri_exclusions'));

        $included = !$uri_inclusions || preg_match($uri_inclusions, $uri);
        $excluded = !$included || ($uri_exclusions && preg_match($uri_exclusions, $uri));

        return s::applyFilters('js_snippet_enable', $included && !$excluded);
    }
}
