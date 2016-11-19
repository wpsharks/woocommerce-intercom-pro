## [coming soon]

- Adding `wp_site` and `wp_uri` fields to JS Snippet to make it easier for support representatives to identify the origin of a customer request; e.g., whenever a single Intercom account is being used to support more then one WordPress installation, or multiple child sites/domains in a Multisite Network. See [Issue #22](https://github.com/websharks/woocommerce-intercom-pro/issues/22).

- **Bug Fix:** Call to undefined method `WC_Order::get_currency()` should be `get_order_currency()`. Reported by @raamdev in [this GitHub issue](https://github.com/websharks/woocommerce-intercom-pro/issues/4).

- **Compat:** Adding support for guest checkout events that track a customer by email address instead of by user ID. Closes [this GitHub issue](https://github.com/websharks/woocommerce-intercom-pro/issues/6).

- **Cleanup:** Moving JS snippet into a template file w/ replacement code. Closes [this GitHub issue](https://github.com/websharks/woocommerce-intercom-pro/issues/8).

- **Compat:** Updating `composer.json` to specify that we will use the Intercom SDK v3.x (but not 4.x) until a full review of a new major release has been completed.

- **New Filter:** `add_filter('woocommerce_intercom_js_snippet_enable', '__return_false');` to disable the JS snippet selectively.

- **Bug Fix:** In the Event API calls, `amount` must be given in **cents** (i.e., total x 100) for proper calculation on the Intercom side. Closes [this GitHub issue](https://github.com/websharks/woocommerce-intercom-pro/issues/17).

## v160909.7530

- **Bug Fix**: Fixed a "Notice: Array to string conversion" bug when getting available user downloads from WooCommerce. See [Issue #2](https://github.com/websharks/woocommerce-intercom-pro/issues/2).
- **Enhancement**: The `total_spent` Custom Attribute is now padded to two decimal places, ensuring that a zero value gets passed as `0.00` and values that end in a zero include the padded zero (e.g., `5.50` instead of `5.5`). See [this commit](https://github.com/websharks/woocommerce-intercom-pro/commit/86f8ac436b7f69dab348ab3a0b502284dfd3d121).
- Updated WP PHP RV to v160824.6416.

## 160825.82407

- Initial release.
