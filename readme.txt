=== Multi Order for WooCommerce ===
Contributors: algoritmika,karzin,anbinder
Tags: woocommerce,multiple,suborder,order,split,orders,algoritmika
Requires at least: 4.4
Tested up to: 5.0
Stable tag: 1.1.1
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Split your orders in suborders

== Description ==

**Multi Order for WooCommerce** creates a sub-order for each item in a order.

**Free version**
The *free version* of this plugin only allows to:

* Create suborders for each different order item
* Setup the main order and the suborder status when orders are placed
* Display a new column on admin/frontend regarding Suborder IDs
* Display intuitive numbers to your suborders. E.g If your main order ID is 100, your suborders numbers will be 100-1, 100-2, and so on.

**[Premium Version](https://wpcodefactory.com/item/multi-order-for-woocommerce/ "Multi Order for WooCommerce Pro")**
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
Yes, it's located [here](https://wpcodefactory.com/item/multi-order-for-woocommerce/ "Multi Order for WooCommerce Pro")

= How can I contribute? Is there a github repository? =
If you are interested in contributing - head over to the [Multi order for WooCommerce plugin GitHub Repository](https://github.com/algoritmika/multi-order-for-woocommerce) to find out how you can pitch in.

== Installation ==

1. Upload the entire 'multi-order-for-woocommerce' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Start by visiting plugin settings at WooCommerce > Settings > Multi Order.

== Screenshots ==

== Changelog ==

= 1.1.1 - 22/01/2019 =
* Improve show_or_hide_admin_suborders_list_view() function
* Tested up to: 5.0
* WC tested up to: 3.5

= 1.1.0 - 02/10/2018 =
* Replace 'woocommerce_checkout_order_processed' by 'woocommerce_thankyou'
* Save sort id for new orders, regardless of suborders
* Stop sorting orders

= 1.0.10 - 14/09/2018 =
* Improve the way of setting the main order status

= 1.0.9 - 29/06/2018 =
* Fix function call (alg_multiorder_for_wc_pro to alg_multiorder_for_wc)
* Update WC tested up to

= 1.0.8 - 26/06/2018 =
* Recreate free version
* Add screenshot

= 1.0.7 - 16/05/2018 =
* Improve pre_get_posts hook functions

= 1.0.6 - 21/02/2018 =
* Fix "Automatic suborders creation" when new items are created inside an order

= 1.0.5 - 24/01/2018 =
* Replace "totals" label by "remaining" on parent orders

= 1.0.4 - 18/12/2017 =
* Fix WooCommerce reports

= 1.0.3 - 22/11/2017 =
* Hide multi order metabox on single item orders
* Tested up to WordPress version 4.9
* Tested up to WooCommerce version 3.2.5

= 1.0.2 - 14/11/2017 =
* Fix orders that get invisible
* Fix nested serialization of order item meta

= 1.0.1 - 07/09/2017 =
* Remove Payment status link

= 1.0.0 - 19/06/2017 =
* Initial Release.

== Upgrade Notice ==

= 1.1.1 =
* Improve show_or_hide_admin_suborders_list_view() function
* Tested up to: 5.0
* WC tested up to: 3.5