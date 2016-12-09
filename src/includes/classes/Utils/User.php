<?php
/**
 * User utils.
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
 * User utils.
 *
 * @since 161209.85885 Initial release.
 */
class User extends SCoreClasses\SCore\Base\Core
{
    /**
     * User ID for Intercom.
     *
     * @since 161209.85885 Initial release.
     *
     * @param array $args Input arguments.
     *
     * @return string User ID for Intercom.
     */
    public function id(array $args = []): string
    {
        $cur_WP_User = wp_get_current_user();
        $cur_blog_id = get_current_blog_id();

        $default_args = [
            'blog_id' => $cur_blog_id,
            'id'      => $cur_WP_User->ID,
            'email'   => $cur_WP_User->user_email,
        ];
        $args += $default_args; // Merge defaults.

        $args['blog_id'] = (int) $args['blog_id'];
        $args['id']      = (int) $args['id'];
        $args['email']   = (string) $args['email'];
        $args['email']   = mb_strtolower($args['email']);

        if (!$args['id'] && !$args['email']) {
            return ''; // Not possible.
        }
        $location_id = hash('crc32b', get_home_url($args['blog_id'], '/', 'http'));
        $user_id     = $args['id'] ?: 'e-'.hash('crc32b', $args['email']);
        return $id   = $location_id.'.'.$user_id;
    }

    /**
     * Update Intercom user.
     *
     * @since 161209.85885 Initial release.
     *
     * @param array $args Input arguments.
     *
     * @return bool True if update successfull.
     */
    public function update(array $args = []): bool
    {
        $cur_blog_id = get_current_blog_id();
        $cur_WP_User = wp_get_current_user();

        $default_args = [
            'blog_id' => $cur_blog_id,
            'id'      => $cur_WP_User->ID,
            'name'    => $cur_WP_User->display_name,
            'email'   => $cur_WP_User->user_email,
        ];
        $args += $default_args; // Merge defaults.

        $args['blog_id'] = (int) $args['blog_id'];
        $args['id']      = (int) $args['id'];
        $WP_User         = new \WP_User($args['id']);

        if (!($app_id = s::getOption('app_id'))) {
            return false; // Not possible.
        } elseif (!($api_token = s::getOption('api_token'))) {
            return false; // Not possible.
        } elseif (!($standard_attrs = $this->standardAttrs($args))) {
            return false; // Not possible.
        } elseif (!($custom_attributes = $this->customAttrs($WP_User, $args['blog_id']))) {
            return false; // Not possible.
        }
        $attrs = array_merge($standard_attrs, compact('custom_attributes'));

        try { // Catch Intercom exceptions.
            $Intercom       = new IntercomClient($api_token, null);
            $response       = $Intercom->users->create($attrs);
            return $success = $response && empty($response->errors);
            //
        } catch (\Throwable $Exception) {
            c::review(vars(), 'User update exception.');
            return false; // Soft failure.
        }
    }

    /**
     * User standard attributes.
     *
     * @since 161209.85885 Initial release.
     *
     * @param array $args Input arguments.
     *
     * @return array Standard attributes.
     */
    public function standardAttrs(array $args = []): array
    {
        $cur_blog_id = get_current_blog_id();
        $cur_WP_User = wp_get_current_user();

        $default_args = [
            'blog_id' => $cur_blog_id,
            'id'      => $cur_WP_User->ID,
            'name'    => $cur_WP_User->display_name,
            'email'   => $cur_WP_User->user_email,
        ];
        $args += $default_args; // Merge defaults.

        $args['blog_id'] = (int) $args['blog_id'];
        $args['id']      = (int) $args['id'];
        $args['name']    = (string) $args['name'];
        $args['email']   = (string) $args['email'];
        $args['email']   = mb_strtolower($args['email']);
        $WP_User         = new \WP_User($args['id']);

        if ($WP_User->exists()) { // Prefer user info.
            $args['name']  = $WP_User->display_name ?: $args['name'];
            $args['email'] = $WP_User->user_email ?: $args['email'];
        }
        $attrs = []; // Initialize.

        if (($user_id = $this->id($args))) {
            $attrs = [ // Standard attrs.
                'user_id' => $user_id, // For Intercom.
                'email'   => c::clip($args['email'], 255),
                'name'    => c::clip($args['name'], 255),
            ];
            if ($WP_User->exists() && $WP_User->user_registered && $WP_User->user_registered !== '0000-00-00 00:00:00') {
                $attrs['created_at'] = strtotime($WP_User->user_registered);
            }
        } // Only if possible; i.e., have an Intercom user ID.

        return $attrs = c::removeEmptys($attrs);
    }

    /**
     * User custom attributes.
     *
     * @since 161209.85885 Initial release.
     *
     * @param \WP_User $WP_User User object.
     * @param int      $blog_id Blog ID.
     *
     * @return array Custom attributes.
     */
    public function customAttrs(\WP_User $WP_User, int $blog_id = 0): array
    {
        if (!$WP_User->exists()) {
            return $this->siteAttrs($blog_id);
        }
        $blog_id = $blog_id ?: get_current_blog_id();

        $available_downloads = []; // Initialize available downloads.
        $downloads           = wc_get_customer_available_downloads($WP_User->ID);
        $downloads           = is_array($downloads) ? $downloads : [];

        foreach ($downloads as $_download) {
            $available_downloads[] = $_download['file']['name'];
        } // unset($_download); // Housekeeping.

        return $attrs = array_merge($this->siteAttrs($blog_id), [
            'wp_user_id'       => (int) $WP_User->ID,
            'wp_login'         => c::clip($WP_User->user_login, 255),
            'wp_roles'         => c::clip(implode(', ', $WP_User->roles), 255),
            'wp_user_edit_url' => c::midClip(admin_url('/user-edit.php?user_id='.$WP_User->ID), 255),

            'total_orders'        => wc_get_customer_order_count($WP_User->ID),
            'total_spent'         => sprintf('%0.2f', (float) wc_get_customer_total_spent($WP_User->ID)),
            'available_downloads' => c::clip(implode(', ', $available_downloads), 255),
        ]);
    }

    /**
     * User site attributes.
     *
     * @since 161209.85885 Initial release.
     *
     * @param int $blog_id Blog ID.
     *
     * @return array User site attributes.
     */
    protected function siteAttrs(int $blog_id = 0): array
    {
        $blog_id              = $blog_id ?: get_current_blog_id();
        $attrs['wp_home_url'] = c::midClip(get_home_url($blog_id, '/', 'http'), 255);

        if ($this->Wp->is_multisite) {
            $attrs['wp_network_home_url'] = c::midClip(network_home_url('/', 'http'), 255);
        }
        return $attrs; // Site attributes.
    }
}
