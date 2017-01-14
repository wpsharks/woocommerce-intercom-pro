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
        } elseif (!($attrs = $this->attrs())) {
            return; // Not applicable.
        }
        echo '<script type="text/javascript">';
        echo    'window.intercomSettings = '.json_encode($attrs).';';
        echo '</script>';

        $snippet      = c::getTemplate('site/intercom.html')->parse();
        echo $snippet = str_replace('%%app_id%%', $app_id, $snippet);
    }

    /**
     * JS snippet enabled?
     *
     * @since 161209.85885 Display options.
     *
     * @return bool True if enabled.
     */
    protected function enabled(): bool
    {
        $uri = c::currentUri();

        $uri_inclusions    = c::wRegx(s::getOption('uri_inclusions'));
        $uri_exclusions    = c::wRegx(s::getOption('uri_exclusions'));
        $display_if_logged = s::getOption('display_if_logged');

        $included = !$uri_inclusions || preg_match($uri_inclusions, $uri);
        $excluded = !$included || ($uri_exclusions && preg_match($uri_exclusions, $uri));

        $enabled = $included && !$excluded; // Initial checks.

        if ($display_if_logged === 'in' && !is_user_logged_in()) {
            $enabled = false; // Do not display.
        } elseif ($display_if_logged === 'out' && is_user_logged_in()) {
            $enabled = false; // Do not display.
        }
        return s::applyFilters('js_snippet_enable', $enabled);
    }

    /**
     * Attribues array.
     *
     * @since 161209.85885 User utils.
     *
     * @return array Attribues array.
     */
    protected function attrs(): array
    {
        $WP_User = wp_get_current_user();

        $standard_attrs = a::userStandardAttrs(['id' => $WP_User->ID]);
        $custom_attrs   = a::userCustomAttrs($WP_User, get_current_blog_id());
        $attrs          = ['app_id' => s::getOption('app_id')] + $standard_attrs + $custom_attrs;

        if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE) {
            $attrs['language_override'] = ICL_LANGUAGE_CODE;
        } // See: <https://wpml.org/documentation/support/wpml-coding-api/>

        return s::applyFilters('js_snippet_attrs', $attrs);
    }
}
