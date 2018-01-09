
=== Modify Product Page Layout for WooCommerce ===
Contributors: solarise_webdev
Donate link: http://solarisedesign.co.uk
Tags: woocommerce, product, reorder, layout
Requires at least: 3.0.1
Tested up to: 4.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin lets you rearrange the elements of the WooCommerce product page layout without having to modify code.

== Description ==

The product page layout in WooCommerce makes extensive use of the hooks & actions functionality of Wordpress. This is very useful from a coding point of view, as it provides a great deal of flexibility and customisation potential.

However, for quick edits or for anyone unfamiliar with how the underlying code works, modifying the layout of the product page could be a daunting task.

I created this plugin to allow a simpler drag-and-drop approach to allow the layout of the product page to be modified more easily, by non-technical users (e.g. designers wishing to alter the structure of the product page code)

And, even from a code-savvy point of view, it can be a little cumbersome to have to manually remove and re-add actions, setting specific priorities in order to arrange and re-arrange elements. This plugin gives a way to quickly 

Note that reordering the elements won't necessarily reflect terribly cleanly on the front-end. The adjustments will need to be combined with suitable modifications to CSS in order to be effective. But that's the main aim here, to allow modification of the underlying page structure to aid styling/CSS creation without needing to mess about with underlying PHP code.

### Technical bits and pieces

You don't really need to know about all the technical stuff in order to use this plugin, but for the sake of completeness, here we go...

The specific WooCommerce template which this plugin targets is located at /woocommerce/templates/content-single-product.php

This template is responsible for the `do_action` calls which render the individual product page elements (which can be found in /woocommerce/templates/single-product). Each of these template files can be easily modified to change the content of e.g. the "images" section, or the "price" section, but re-ordering them requires modification of the order/position of where each element is loaded within the `do_action` calls.

The product page contains three distinct `do_action` calls by default. These are:

 * woocommerce_before_single_product_summary
 * woocommerce_single_product_summary
 * woocommerce_after_single_product_summary

By default, 'before' contains the sale flash/icon and the images, 'after' contains the tabs and upsell/related products. Everything else is contained within the summary itself.

You could use these however you wish though, perhaps e.g. altering the HTML structure of content-single-product.php to create a three column layout, with a `do_action` in each.

It's perhaps not semantically correct, but offers a way to easily rearrange the product page without having to dig too deeply into code.

In this version:

 * Allows certain elements to be hidden (drag into the "Do not display" section)
 * All existing elements can be swapped between the three main 'action' calls

**Note:** usage of this plugin on a live site is entirely at the users own risk.

== Installation ==

1. Upload the `woocommerce-modify-product-page-layout` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make sure that WooCommerce is installed and activated
4. Configure the plugin via `WooCommerce > Settings > Products (tab) > Modify Layout

== Frequently Asked Questions ==

= Who is this plugin for? =

I created this plugin partially for my own use, to more easily configure the product page without having to manually adjust various `do_action` calls.

It may be of benefit for anyone working on a WooCommerce installation from a purely design-oriented point of view.

== Screenshots ==

1. The 'Modify Layout' section of the WooCommerce product tab

== Changelog ==

= 1.0 =
* Initial version created

== Upgrade Notice ==

