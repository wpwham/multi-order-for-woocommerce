=== Multi Order for WooCommerce ===
Contributors: wpwham
Tags: woocommerce, multiple, suborder, order, split, orders
Requires at least: 4.4
Tested up to: 5.3
Stable tag: 1.3.1
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Split your orders into suborders.

== Description ==

**Multi Order for WooCommerce** creates a sub-order for each item in a order.

**Free version**
The *free version* of this plugin only allows to:

* Create suborders for each different order item
* Setup the main order and the suborder status when orders are placed
* Display a new column on admin/frontend regarding Suborder IDs
* Display intuitive numbers to your suborders. E.g If your main order ID is 100, your suborders numbers will be 100-1, 100-2, and so on.

**[Premium Version](https://wpwham.com/products/multi-order-for-woocommerce/ "Multi Order for WooCommerce Pro")**
Besides free version features, the **premium version** of this plugin allows to:

* Setup if order item quantity is considered as suborder or not
* Deduct / Undeduct suborders from main order, i.e If you get your suborder paid, subtract its value from main order automatically
* Choose what order status will deduct/undeduct suborders from main order
* Sync orders. i.e Whenever you change a suborder item price or tax, the correspondent item on main order gets updated, and vice-versa
* Display a remaining column on both frontend and admin order screens, showing how much is left to pay
* Setup if emails will be sent to main order / suborders
* Organize orders with a new taxonomy called Payment Status. e.g Orders will be considered as Payed / Not paid / Partial

== Screenshots ==

1. Plugin's settings (WooCommerce > Settings > Multi Order)

== Frequently Asked Questions ==

= Where are the plugin's settings? =
Visit WooCommerce > Settings > Multi Order.

= Is there a Pro version? =
Yes, it's located [here](https://wpwham.com/products/multi-order-for-woocommerce/ "Multi Order for WooCommerce Pro")

= How can I contribute? Is there a GitHub repository? =
If you are interested in contributing - head over to the [Multi order for WooCommerce plugin GitHub Repository](https://github.com/grantalltodavid/multi-order-for-woocommerce) to find out how you can pitch in.

== Installation ==

1. Upload the entire 'multi-order-for-woocommerce' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Start by visiting plugin settings at WooCommerce > Settings > Multi Order.

== Screenshots ==

== Changelog ==

= 1.3.1 - 2019-12-16 =
* FIX: issue with suborder tax calculation when tax type is "inclusive"
* FIX: issues with manual order creation
* UPDATE: updated version number to be in sync with Pro version

= 1.1.3 - 2019-09-12 =
* UPDATE: updated .pot file for translations

= 1.1.2 - 2019-06-11 =
* Plugin author changed.

= 1.1.1 - 2019-05-29 =
* Improve `show_or_hide_admin_suborders_list_view()` function.
* Tested up to: 5.3.
* WC tested up to: 3.6.

= 1.1.0 - 2018-10-02 =
* Replace 'woocommerce_checkout_order_processed' by 'woocommerce_thankyou'
* Save sort id for new orders, regardless of suborders
* Stop sorting orders

= 1.0.10 - 2018-09-14 =
* Improve the way of setting the main order status

= 1.0.9 - 2018-06-29 =
* Fix function call (alg_multiorder_for_wc_pro to alg_multiorder_for_wc)
* Update WC tested up to

= 1.0.8 - 2018-06-26 =
* Recreate free version
* Add screenshot

= 1.0.7 - 2018-05-16 =
* Improve pre_get_posts hook functions

= 1.0.6 - 2018-02-21 =
* Fix "Automatic suborders creation" when new items are created inside an order

= 1.0.5 - 2018-01-24 =
* Replace "totals" label by "remaining" on parent orders

= 1.0.4 - 2017-12-18 =
* Fix WooCommerce reports

= 1.0.3 - 2017-11-22 =
* Hide multi order metabox on single item orders
* Tested up to WordPress version 4.9
* Tested up to WooCommerce version 3.2.5

= 1.0.2 - 2017-11-14 =
* Fix orders that get invisible
* Fix nested serialization of order item meta

= 1.0.1 - 2017-09-07 =
* Remove Payment status link

= 1.0.0 - 2017-06-19 =
* Initial Release.
