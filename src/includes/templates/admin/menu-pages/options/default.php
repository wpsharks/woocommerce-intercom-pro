<?php
/**
 * Template.
 *
 * @author @raamdev
 * @copyright WP Sharks™
 */
declare(strict_types=1);
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
            'label'       => __('Intercom App ID', 'woocommerce-intercom'),
            'placeholder' => __('e.g., G6z9edJfu3y', 'woocommerce-intercom'),

            'tip' => __('Your App ID is available on the API Keys page on Intercom, accessible from your App Settings menu option.', 'woocommerce-intercom'),

            'name'  => 'app_id',
            'value' => s::getOption('app_id'),
        ]); ?>

        <?= $Form->inputRow([
            'type'        => 'password',
            'label'       => __('Personal Access Token', 'woocommerce-intercom'),
            'placeholder' => __('e.g., yhgFvbENYQVCJETwGkBemk4PD7h3PuSDr5dNUv2dqaVzhpdYPkpTJWLQr5cYaSSx', 'woocommerce-intercom'),

            'tip'  => __('Generate a \'Personal Access Token\' from the App Settings menu option at Intercom.', 'woocommerce-intercom'),
            'note' => sprintf(__('Generate a \'Personal Access Token\' at Intercom. See: <a href="%1$s" target="_blank">KB article/instructions</a>', 'woocommerce-intercom'), esc_url(s::coreUrl('/r/woocommerce-intercom-pro-personal-access-token-instructions'))),

            'name'  => 'api_token',
            'value' => s::getOption('api_token'),
        ]); ?>

    <?= $Form->closeTable(); ?>

    <hr />

    <?= $Form->openTable(
        __('Icon Display Options', 'woocommerce-intercom'),
        __('These settings control which areas of your site should display the clickable Intercom icon. This is accomplished by matching patterns against any given URI. A URI is the <code>/path/</code> part of a URL; i.e., everything after the domain name. In many cases, just adding <code>^/**$</code> to the list of URI Inclusion Patterns is good enough. Note that <code>^/**$</code> matches all URIs. In other words, show the Intercom icon everywhere.', 'woocommerce-intercom')
    ); ?>

        <?= $Form->textareaRow([
            'label'       => __('URI Inclusion Patterns', 'woocommerce-intercom'),
            'placeholder' => __('e.g., ^/**$', 'woocommerce-intercom'),

            'tip'  => __('A line-delimited list of patterns; i.e., one WRegx™ pattern per line please.', 'woocommerce-intercom'),
            'note' => sprintf(__('To learn more about patterns, see: <a href="%1$s" target="_blank">WRegx™ KB Article</a>', 'woocommerce-intercom'), esc_url(s::coreUrl('/r/wregx-patterns'))),

            'name'  => 'uri_inclusions',
            'value' => s::getOption('uri_inclusions'),

            'attrs' => 'wrap="off" spellcheck="false"',
        ]); ?>

        <?= $Form->textareaRow([
            'label'       => __('URI Exclusion Patterns', 'woocommerce-intercom'),
            'placeholder' => __('e.g., ^/blog/**$', 'woocommerce-intercom'),

            'tip'  => __('A line-delimited list of patterns; i.e., one WRegx™ pattern per line please.', 'woocommerce-intercom'),
            'note' => sprintf(__('To learn more about patterns, see: <a href="%1$s" target="_blank">WRegx™ KB Article</a>', 'woocommerce-intercom'), esc_url(s::coreUrl('/r/wregx-patterns'))),

            'name'  => 'uri_exclusions',
            'value' => s::getOption('uri_exclusions'),

            'attrs' => 'wrap="off" spellcheck="false"',
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->submitButton(); ?>
<?= $Form->closeTag(); ?>
