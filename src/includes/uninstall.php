<?php
/**
 * Uninstaller.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerceIntercom\Pro;

use WebSharks\WpSharks\WooCommerceIntercom\Pro\Classes\App;

if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
require __DIR__.'/wp-sharks-core-rv.php';

if (require(dirname(__FILE__, 2).'/vendor/websharks/wp-sharks-core-rv/src/includes/check.php')) {
    require_once __DIR__.'/stub.php';
    new App(['§uninstall' => true]);
}
