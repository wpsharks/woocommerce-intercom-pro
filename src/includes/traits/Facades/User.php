<?php
/**
 * User.
 *
 * @author @raamdev
 * @copyright WP Sharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WooCommerceIntercom\Pro\Traits\Facades;

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
 * User.
 *
 * @since 161209.85885
 */
trait User
{
    /**
     * @since 161209.85885 User utils.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\User::id()
     */
    public static function userId(...$args)
    {
        return $GLOBALS[static::class]->Utils->User->id(...$args);
    }

    /**
     * @since 161209.85885 User utils.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\User::update()
     */
    public static function userUpdate(...$args)
    {
        return $GLOBALS[static::class]->Utils->User->update(...$args);
    }

    /**
     * @since 161209.85885 User utils.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\User::standardAttrs()
     */
    public static function userStandardAttrs(...$args)
    {
        return $GLOBALS[static::class]->Utils->User->standardAttrs(...$args);
    }

    /**
     * @since 161209.85885 User utils.
     *
     * @param mixed ...$args Variadic args to underlying utility.
     *
     * @see Classes\Utils\User::customAttrs()
     */
    public static function userCustomAttrs(...$args)
    {
        return $GLOBALS[static::class]->Utils->User->customAttrs(...$args);
    }
}
