## $v

- **Bug Fix**: Fixed a "Notice: Array to string conversion" bug when getting available user downloads from WooCommerce. See [Issue #2](https://github.com/websharks/woocommerce-intercom-pro/issues/2).
- **Enhancement**: The `total_spent` Custom Attribute is now padded to two decimal places, ensuring that a zero value gets passed as `0.00` and values that end in a zero include the padded zero (e.g., `5.50` instead of `5.5`). See [this commit](https://github.com/websharks/woocommerce-intercom-pro/commit/86f8ac436b7f69dab348ab3a0b502284dfd3d121).
- Updated WP PHP RV to v160824.6416. 

## 160825.82407

- Initial release.
