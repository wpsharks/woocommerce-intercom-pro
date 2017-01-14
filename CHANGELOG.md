## [coming soon]

- Enhancing security by removing `basename(__FILE__)` from direct access notices.

- **New Feature:** It is now possible to configure the Intercom Messenger icon display settings so that it's only shown to logged-in users, only to logged-out users, or to everyone (default behavior). See: **Dashboard → WooCommerce → Intercom → Messenger Display Options**.

- **Intercom `user_id`:** This release changes the way an Intercom `user_id` is generated. Instead of just the WP User ID, the Intercom `user_id` is now formulated in a way that allows a single Intercom account to be used for multiple installations of WordPress. The `user_id` sent to Intercom is now made up of two parts. 1. A hash of the current WordPress `home_url()` (normalized). 2. The current user's WordPress ID (or, in the case of guest checkout, an `e-`, followed by a hash of the user's email address). The final `user_id` becomes: `[location hash].[user ID]`; e.g., `3610a686.123`, `3610a686.e-9943c993` (guest).

- **New People Attribute:** This release adds a new People Attribute by the name of `wp_user_id`, giving support reps. the ability to track WP User IDs in Intercom.

- **Bug Fix:** Before a new Event is generated to track a user's Order or Subscription status on the Intercom side, a new API call is now made to ensure that a matching User does in fact exist on the Intercom side before an Event is created. This corrects a problem reported in the previous release that resulted in an error during checkout: `User Not Found`. See [Issue #6](https://github.com/websharks/woocommerce-intercom-pro/issues/6#issuecomment-263278631)

- **New Filter:** A new filter has been exposed: `js_snippet_attrs`. This allows a developer to add their own custom attributes; above and beyond what is already defined by WooCommerce Intercom Pro itself.

- **Compatibility:** Adding support and compatibility with WPML's `ICL_LANGUAGE_CODE` so the Intercom messenger will always be displayed in an appropriate language.

- **Compatibility:** This release adds compatibility with WooCommerce Subscriptions. See [Issue #1](https://github.com/websharks/woocommerce-intercom-pro/issues/1).

- **Compatibility:** This release adds support for all WooCommerce Order and/or Subscription status changes, including those added by WooCommerce extensions such as the WooCommerce Give Order extension. See [Issue #1](https://github.com/websharks/woocommerce-intercom-pro/issues/1).

- **New Feature:** Adding support for URI Inclusion patterns. See [Issue #12](https://github.com/websharks/woocommerce-intercom-pro/issues/12).

- **New Feature:** Adding support for URI Exclusion patterns. See [Issue #12](https://github.com/websharks/woocommerce-intercom-pro/issues/12).

- **Compatibility:** Removing API Key setting for Intercom in favor of a Personal Access Token. There is now a configuration field under: **WP Dashboard → WooCommerce → Intercom** settings where you can supply a Personal Access Token for API calls to Intercom. Use the instructions provided in this area to generate and fill-in your Personal Access Token. See [Issue #20](https://github.com/websharks/woocommerce-intercom-pro/issues/20).

- **Enhancement:** Standardizing Event names sent to Intercom. New format: `order-[status]`. See [Issue #19](https://github.com/websharks/woocommerce-intercom-pro/issues/19).

- **Enhancement:** Adding `wp_home_url` and `wp_network_home_url` fields to JS Snippet to make it easier for support representatives to identify the origin of a customer request; e.g., whenever a single Intercom account is being used to support more then one WordPress installation, or multiple child sites/domains in a Multisite Network. See [Issue #22](https://github.com/websharks/woocommerce-intercom-pro/issues/22).

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
