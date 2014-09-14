=== WooCommerce Extra Product Sorting Options ===
Contributors: beka.rice, skyverge, tamarazuk
Tags: woocommerce, sorting, product sorting, orderby
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@skyverge.com&item_name=Donation+for+WooCommerce+Extra+Product+Sorting
Requires at least: 3.8
Tested up to: 4.0
Requires WooCommerce at least: 2.1
Tested WooCommerce up to: 2.2
Stable Tag: 2.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Rename the default product sorting option and add up to 6 new sorting options including alphabetical and on-sale sorting.

== Description ==

WooCommerce Extra Product Sorting Options provides options that extend the default WooCommerce orderby options on the shop page. You can optionally set a new name for the default sorting (helpful if you've used this to create a custom sorting order), and can enable up to **6 new sorting options**: alphabetical, reverse alphabetical, on sale, featured, availability, and random product sorting.

> **Requires: WooCommerce 2.1+**, Compatible with WooCommerce 2.2

= Features =
Includes options to:

 - rename default product sorting (i.e., change to "Our Sorting")
 - enable alphabetical product sorting
 - enable reverse alphabetical sorting
 - enable featured-first sorting
 - enable on sale sorting (thanks to [Bryce Adams](http://bryceadams.com/order-products-sale-woocommerce/) for the idea)
 - enable sorting by inventory / availability
 - enable randomized product sorting

= Rename Default Sorting =
You can customize your product sorting order on your shop pages - [here's a handy tutorial](http://www.sellwithwp.com/create-woocommerce-custom-product-sorting/) to do so. However, many shop admins like to then rename this from "Default Sorting" to something more descriptive, such as "Our Sorting" or "Our Selection". You can optionally enter a new name for this sorting order if desired.

= Adding Sorting Options =
When you create a customized sorting order, you lose the ability to sort products alphabetically. This plugin gives you the ability to add new sorting options to list products by title A to Z or in reverse order (Z to A).

You can add the option to sort items by sale status - there's a sorting option to show "On Sale" items first in the shop catalog. You can also show featured items first in your catalog.

Want to show items with the highest stock first? You can enable sorting by availability, which will enable sorting from high stock to low stock (See FAQ for more details).

Finally, you can add a "randomized" sorting option just for fun - any time this sorting is selected, the product order will be randomized when the shop page is viewed.

= Looking to remove sorting options? =
We have a compatible plugin that will let you remove core WooCommerce sorting options, such as the default sorting method. You can check out the [WooCommerce Remove Product Sorting](wordpress.org/plugins/woocommerce-remove-product-sorting/) plugin page for more details.

= More Details =
 - See the [product page](http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/) for full details.
 - View more of SkyVerge's [free WooCommerce extensions](http://profiles.wordpress.org/skyverge/)
 - View all [SkyVerge WooCommerce extensions](http://www.skyverge.com/shop/)
 - View the FAQ for some tips.

== Installation ==

1. Be sure you're running WooCommerce 2.1+ in your shop.
2. Upload the entire `woocommerce-extra-product-sorting-options` folder to the `/wp-content/plugins/` directory, or upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **WooCommerce &gt; Settings &gt; Products**. The new settings are added after "Default Product Sorting". If you enable more sorting options, you can set these as new defaults as well.
5. View [documentation on the product page](http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/) for more help if needed.

== Frequently Asked Questions ==
= Do I need to rename the default sorting? =
Nope. You can use this plugin to simply add alphabetical, reverse alphabetical, and randomized sorting to your shop pages. Any of the settings are entirely optional.

= How do I set my new sorting option as the default? =
When you check to enable these options, save your Product settings. You'll now be able to select your new options as a default under the "Default Product Sorting" list.

= Can I change the sorting label in the shop dropdown? =
Yep! You can use the [Say What plugin](https://wordpress.org/plugins/say-what/) to change the text - for example, you could change the label that says "Sort by name: A to Z" to "Sort alphabetically". See the screenshots for an example. 

The text domain to use is `woocommerce`.

= Why doesn't sorting by availability work? =
Don't worry, it does :). It's possible to sort by stock, but this will work for parent products OR on a per-variation base for variable products. While total stock may be 100 for a product, it may be sorted lower because the value used is the highest variation stock (which may be, for example, 85).

If you don't manage your stock, you should **disable** this option - it will simply work as an alphabetical sort if all products are just "In Stock" without inventory managed.

= This is handy! Can I contribute? =
Yes you can! Join in on our [GitHub repository](https://github.com/bekarice/woocommerce-extra-product-sorting-options/) and submit a pull request :)

== Screenshots ==
1. Plugin Settings under **WooCommerce &gt; Settings &gt; Products**
2. Some new sorting options on the shop page
3. Change sorting label (in shop dropdown) with the [Say What plugin](https://wordpress.org/plugins/say-what/)

== Changelog ==
= 2014.09.15 - version 2.0.0 =
 * Misc: Refactored to simplify code
 * Feature: Added "Featured" sorting
 * Feature: Added "Availability" sorting
 * Tweak: Changed settings to multi-select instead of checkbox group

= 2014.07.30 - version 1.2.0 =
 * Feature: Added "On Sale" sorting (thanks [Bryce Adams](http://bryceadams.com/order-products-sale-woocommerce/) for the idea)
 
= 2014.07.29 - version 1.1.0 =
 * Feature: Added reverse alphabetical sorting option

= 2014.07.28 - version 1.0.0 =
 * Initial Release