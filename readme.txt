=== WooCommerce Extra Product Sorting Options ===
Contributors: skyverge, beka.rice, tamarazuk
Tags: woocommerce, sorting, product sorting, orderby
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@skyverge.com&item_name=Donation+for+WooCommerce+Extra+Product+Sorting
Requires at least: 4.1
Tested up to: 4.8.1
WC Requires at least: 2.4
WC Tested up to: 3.1.1
Stable Tag: 2.6.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Rename the default product sorting option and add up to 5 new sorting options including alphabetical and on-sale sorting.

== Description ==

WooCommerce Extra Product Sorting Options provides options that extend the default WooCommerce orderby options on the shop page. You can optionally set a new name for the default sorting (helpful if you've used this to create a custom sorting order), and can enable up to **5 new sorting options**: alphabetical, reverse alphabetical, on sale, review count, and availability product sorting.

> **Requires: WooCommerce 2.4+ and WordPress 4.1+**

= Features =
Includes options to:

 - rename default product sorting (i.e., change to "Our Sorting")
 - enable alphabetical product sorting
 - enable reverse alphabetical sorting
 - enable on sale sorting (**note**: works only for simple products)
 - enable sorting by inventory / availability
 - enable sorting by review count (most reviews to least)

= Rename Default Sorting =
You can customize your product sorting order on your shop pages - [here's a handy tutorial](http://www.sellwithwp.com/create-woocommerce-custom-product-sorting/) to do so. However, many shop admins like to then rename this from "Default Sorting" to something more descriptive, such as "Our Sorting" or "Our Selection". You can optionally enter a new name for this sorting order if desired.

= Adding Sorting Options =
When you create a customized sorting order, you lose the ability to sort products alphabetically. This plugin gives you the ability to add new sorting options to list products by title A to Z or in reverse order (Z to A).

Want to show items with the highest stock first? You can enable sorting by availability, which will enable sorting from high stock to low stock (See FAQ for more details). You can also sort by the number of product reviews.

You can add the option to sort items by sale status - there's a sorting option to show "On Sale" items first in the shop catalog. Please note that only simple products can be sorted by sale status, and variable products will display mixed with non-sale products.

= Looking to remove sorting options? =
We have a compatible plugin that will let you remove core WooCommerce sorting options, such as the default sorting method. You can check out the [WooCommerce Remove Product Sorting](http://www.skyverge.com/product/woocommerce-remove-product-sorting/) plugin page for more details.

= More Details =
 - See the [product page](http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/) for full details.
 - View more of SkyVerge's [free WooCommerce extensions](http://profiles.wordpress.org/skyverge/)
 - View all [SkyVerge WooCommerce extensions](http://www.skyverge.com/shop/)
 - View the FAQ for some tips.

== Installation ==

1. Be sure you're running WooCommerce 2.4+ and WordPress 4.1+ in your shop.
2. Upload the entire `woocommerce-extra-product-sorting-options` folder to the `/wp-content/plugins/` directory, or upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **WooCommerce &gt; Settings &gt; Products &gt; Display**. The new settings are added after "Default Product Sorting". If you enable more sorting options, you can set these as new defaults as well.
5. View [documentation on the product page](http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/) for more help if needed.

== Frequently Asked Questions ==

= Do I need to rename the default sorting? =
Nope. You can use this plugin to simply add new sorting options to your shop pages. Any of the settings are entirely optional.

= How do I set my new sorting option as the default? =
When you check to enable these options, save your Product settings. You'll now be able to select your new options as a default under the "Default Product Sorting" list.

= Can I change the sorting label in the shop dropdown? =
Yep! You can use the [Say What plugin](https://wordpress.org/plugins/say-what/) to change the text - for example, you could change the label that says "Sort by name: A to Z" to "Sort alphabetically". See the screenshots for an example.

The text domain to use is `woocommerce-extra-product-sorting-options`.

= Why doesn't sorting by availability work? =
Don't worry, it does :) It's possible to sort by stock, but this will work for parent products rather than using the stock available at the variation level. You can set this under Product Data &gt; Inventory by enabling "Manage stock". Set the available stock for _all_ variations, and this will be used to sort the item. You can still manage stock at the variation level.

If you don't manage your stock, you should **disable** this option - it will simply work as an alphabetical sort if all products are just "In Stock" without inventory managed.

= Why can't on-sale sorting work for variable products? =
Simple products and variable products use two different "keys" to indicate if they're on sale. As a result, we can't order products using two different meta keys, so we've used the key that indicates a simple product's sale price in this plugin.

We don't anticipate changing this in the foreseeable future, as we've spent a couple hours trying to get the custom search query to work, but WooCommerce core adds search parameters that conflict with it, and we haven't found a suitable work-around.

= This is handy! Can I contribute? =
Yes you can! Join in on our [GitHub repository](https://github.com/skyverge/woocommerce-extra-product-sorting-options/) and submit a pull request :)

== Screenshots ==
1. Plugin Settings under **WooCommerce &gt; Settings &gt; Products**
2. Some new sorting options on the shop page
3. Change sorting label (in shop dropdown) with the [Say What plugin](https://wordpress.org/plugins/say-what/)

== Other Notes ==

= Translations =

**The plugin text domain is**: `woocommerce-extra-product-sorting-options`

= Help! I upgraded to WooCommerce 3.0 and "featured" sorting disappeared. =

Unfortunately this was removed because the way products are designated as "featured" has changed in WooCommerce 3.0+. Because the way this is stored changed behind the scenes, it's no longer possible to sort products using this value to show featured items first. We're sad to say this means that our plugin cannot offer this option for stores running WooCommerce 3.0+ since the data used to sort this way no longer exists.

= This plugin used to have random sorting, I need it back! =

We removed randomized product sorting some time ago because it wasn't 100% functional for many shops (as it required all products to be on one page), and thus is better served as custom code snippet instead of being part of this plugin.

**Why didn't it work?** WordPress will get a new random set of products for each page in your shop, so random sorting only works at 100% when you have a small number of products and they're all displayed on one page. In order to "remember" which products have already been displayed, you'd need [some custom code](http://wordpress.stackexchange.com/questions/31647/is-it-possible-to-paginate-posts-correctly-that-are-random-ordered) to store these products in a session, which is not something we ever planned to support as it isn't possible with the structure of this plugin.

Since this feature wasn't at 100%, we have removed it and turned it into a code snippet. If you need to re-add randomized sorting, please [use this code snippet](https://gist.github.com/bekarice/bac8b67064001ebc3bc2475424d99f87), ensuring that you [know how to add code to your site](http://skyverge.com/blog/add-custom-code-to-wordpress/).

== Changelog ==

= 2017.08.22 - version 2.6.1
 * Fix: PHP warning when WooCommerce is outdated

= 2017.03.23 - version 2.6.0 =
 * Feature: Sort products by review count
 * Misc: Removes 'featured first' sorting in shops running WooCommerce 3.0+ since featured meta is no longer available for products ([see notes](http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/other_notes/) for further details)
 * Misc: Added support for WooCommerce 3.0
 * Misc: Removed support for WooCommerce 2.3.x

= 2016.07.28 - version 2.5.0 =
 * Misc: removed 'randomized' sorting due to issues with larger catalogs ([see notes](http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/other_notes/) for further details)

= 2016.05.31 - version 2.4.0 =
 * Misc: added support for WooCommerce 2.6
 * Misc: removed support for WooCommerce 2.2

= 2016.01.18 - version 2.3.0 =
 * Misc: updated textdomain to `woocommerce-extra-product-sorting-options` - **please update translations**!
 * Misc: WooCommerce 2.5 compatibility

= 2015.09.07 - version 2.2.3 =
 * Fix: properly use `orderby` attributes when passed in via shortcode

= 2015.08.17 - version 2.2.2 =
 * Misc: introduced `wc_extra_sorting_options_fallback_order` filter
 * Misc: pass in `$orderby_value` to `wc_extra_sorting_options_fallback` and `wc_extra_sorting_options_fallback_order` filters to let you change them for particular orderby

= 2015.07.27 - version 2.2.1 =
 * Misc: WooCommerce 2.4 compatibility

= 2015.07.13 - version 2.2.0 =
 * Feature: added title fallback to use as secondary sorting parameter
 * Misc: introduced `wc_extra_sorting_options_fallback` filter
 * Misc: dropped WooCommerce 2.1 support since 2.2 added orderby = rand support

= 2015.02.06 - version 2.1.1 =
 * Fix: bug with loading translations

= 2015.02.03 - version 2.1.0 =
 * Misc: WooCommerce 2.3 compatibility

= 2015.01.09 - version 2.0.1 =
 * Fix: Squished a bug affecting random sorting

= 2015.01.05 - version 2.0.0 =
 * Misc: Refactored to simplify code and add upgrade routine
 * Feature: Added "Featured" sorting
 * Feature: Added "Availability" sorting
 * Tweak: Changed settings to multi-select instead of checkbox group
 * Tweak: Text domain is now `wc-extra-sorting-options` instead of `woocommerce-extra-product-sorting-options`

= 2014.07.30 - version 1.2.0 =
 * Feature: Added "On Sale" sorting (thanks [Bryce Adams](http://bryceadams.com/order-products-sale-woocommerce/) for the idea)

= 2014.07.29 - version 1.1.0 =
 * Feature: Added reverse alphabetical sorting option

= 2014.07.28 - version 1.0.0 =
 * Initial Release
