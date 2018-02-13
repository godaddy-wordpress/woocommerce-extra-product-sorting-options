# WooCommerce Extra Product Sorting Options
 
### Description 

WooCommerce Extra Product Sorting Options provides options that extend the default WooCommerce orderby options on the shop page. You can optionally set a new name for the default sorting (helpful if you've used this to create a custom sorting order), and can enable up to **5 new sorting options**: alphabetical, reverse alphabetical, on sale, review count, and availability product sorting.

### Requirements

 - WooCommerce 2.6.14 or newer
 - WordPress 4.4 or newer

### Quick links &amp; info

 - [View plugin page](http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/)
 - [View WordPress.org listing](https://wordpress.org/plugins/woocommerce-extra-product-sorting-options/)
 - [Plugin FAQ](https://wordpress.org/plugins/woocommerce-extra-product-sorting-options/#faq)
 - [Donation link](https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@skyverge.com&item_name=Donation+for+WooCommerce+Extra+Product+Sorting)
 - Text domain: `woocommerce-extra-product-sorting-options`

### Changelog

**2018.02.13 - version 2.7.1**
 * Fix: PHP warnings for themes that don't support WooCommerce product column and row settings

**2018.02.08 - version 2.7.0**   
 * Tweak: Move settings to customizer panel in WooCommerce 3.3+
 * Fix: Ensure default sorting can be renamed if translated
 * Misc: Add support for WooCommerce 3.3
 * Misc: Require WooCommerce 2.6.14 and WordPress 4.4

**2017.08.22 - version 2.6.1**   
 * Fix: PHP warning when WooCommerce is outdated

**2017.03.23 - version 2.6.0**   
 * Feature: Sort products by review count
 * Misc: Removes 'featured first' sorting in shops running WooCommerce 3.0+ since featured meta is no longer available for products ([see notes](http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/other_notes/) for further details)
 * Misc: Added support for WooCommerce 3.0
 * Misc: Removed support for WooCommerce 2.3.x

**2016.07.28 - version 2.5.0**   
 * Misc: removed 'randomized' sorting due to issues with larger catalogs ([see notes](http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/other_notes/) for further details)

**2016.05.31 - version 2.4.0**   
 * Misc: added support for WooCommerce 2.6
 * Misc: removed support for WooCommerce 2.2

**2016.01.18 - version 2.3.0**   
 * Misc: updated textdomain to `woocommerce-extra-product-sorting-options` - **please update translations**!
 * Misc: WooCommerce 2.5 compatibility

**2015.09.07 - version 2.2.3**   
 * Fix: properly use `orderby` attributes when passed in via shortcode

**2015.08.17 - version 2.2.2**   
 * Misc: introduced `wc_extra_sorting_options_fallback_order` filter
 * Misc: pass in `$orderby_value` to `wc_extra_sorting_options_fallback` and `wc_extra_sorting_options_fallback_order` filters to let you change them for particular orderby

**2015.07.27 - version 2.2.1**   
 * Misc: WooCommerce 2.4 compatibility

**2015.07.13 - version 2.2.0**   
 * Feature: added title fallback to use as secondary sorting parameter
 * Misc: introduced `wc_extra_sorting_options_fallback` filter
 * Misc: dropped WooCommerce 2.1 support since 2.2 added orderby **rand support

**2015.02.06 - version 2.1.1**   
 * Fix: bug with loading translations

**2015.02.03 - version 2.1.0**   
 * Misc: WooCommerce 2.3 compatibility

**2015.01.09 - version 2.0.1**   
 * Fix: Squished a bug affecting random sorting

**2015.01.05 - version 2.0.0**   
 * Misc: Refactored to simplify code and add upgrade routine
 * Feature: Added "Featured" sorting
 * Feature: Added "Availability" sorting
 * Tweak: Changed settings to multi-select instead of checkbox group
 * Tweak: Text domain is now `wc-extra-sorting-options` instead of `woocommerce-extra-product-sorting-options`

**2014.07.30 - version 1.2.0**   
 * Feature: Added "On Sale" sorting

**2014.07.29 - version 1.1.0**   
 * Feature: Added reverse alphabetical sorting option

**2014.07.28 - version 1.0.0**   
 * Initial Release
