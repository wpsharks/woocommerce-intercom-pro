<?php
/**
 * Template.
 *
 * @author @raamdev
 * @copyright WP Sharks™
 */
declare (strict_types = 1);
namespace WebSharks\WpSharks\WooCommerceIntercom\Pro;

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

$Form = $this->s::menuPageForm('§save-options');
?>
<?= $Form->openTag(); ?>

    <?= $Form->openTable(
        __('General Options', 'woocommerce-intercom'),
        sprintf(__('You can browse <em>our</em> <a href="%1$s" target="_blank">knowledge base</a> to learn more about these options.', 'woocommerce-intercom'), esc_url(s::brandUrl('/kb')))
    ); ?>

        <?= $Form->inputRow([
            'label' => __('Intercom App ID', 'woocommerce-intercom'),
            'tip'   => __('Your App ID is available on the API Keys page on Intercom, accessible from your App Settings menu option.', 'woocommerce-intercom'),

            'name'  => 'app_id',
            'value' => s::getOption('app_id'),
        ]); ?>

        <?= $Form->inputRow([
            'type'  => 'password',
            'label' => __('Intercom API Key', 'woocommerce-intercom'),
            'tip'   => __('Your API Keys are available on the API Keys page on Intercom, accessible from your App Settings menu option.', 'woocommerce-intercom'),

            'name'  => 'api_key',
            'value' => s::getOption('api_key'),
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->submitButton(); ?>
<?= $Form->closeTag(); ?>
