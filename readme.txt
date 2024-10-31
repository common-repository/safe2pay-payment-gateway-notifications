=== Safe2Pay Gateway Notifications ===
Contributors: safe2pay
Tags: ecommerce, notifications, safe2pay, payment gateway, australia, subscriptions
Requires at least: 3.8
Tested up to: 5.3.1
Requires PHP: 5.6
Stable tag: trunk

This plug-in sends a notification whenever a payment is made through Safe2Pay payment gateway

== Description ==

This plug-in sends you an email when a payment goes through the Safe2Pay Payment Gateway. This includes notifications for successful and failed payments and refunds.

Visit [https://www.safe2pay.com.au](https://www.safe2pay.com.au "Safe2Pay Online Payment Gateway") for more details on using Safe2Pay.

== Installation ==

There are two methods to install the plugin:

**Copy the file to the WordPress plugins directory:**

1. Make a new directory in [SITE_ROOT]/wp-content/safe2pay-notifications
2. Copy the files safe2pay-notifications-emails.php and safe2pay-notifications.php to this new directory.
3. Activate the newly installed plug-in in the WordPress Plug-in Manager.

**Install the plug-in from WordPress:**

1. Search for the Safe2Pay Payment Gateway Notifications plug-in from the WordPress Plug-in Manager.
2. Install the plug-in.
3. Activate the newly installed plug-in.

== Configuration ==

1. Visit the Safe2Pay Notifications settings page.
2. Enter the email address you wish to receive the email Notifications (You can send notifications to multiple email addresses by separating them by a comma).
3. Select what notifications you wish to receive.
4. Save changes.

== Screenshots ==

1. Settings page where you can add/update the email address that receives payment notifications. As well as what notifications they receive.

== Changelog ==

= 1.23 =
* Bug fixes

= 1.22 =
* Bug fixes

= 1.21 =
* Bug fixes

= 1.2 =
* Hook URL now handled by the plugin itself instead of WooCommmerce plugin

= 1.1 =
* Order status is immediately updated for a successful payment notification

== Frequently Asked Questions ==

Can I add more then one email address to receive notifications?
Yes! The addresses just need to be separated be a comma.

== Support ==

If you have any issue with the Safe2Pay Gateway Notifications plug-in please contact us at support@safe2pay.com.au and we will be more then happy to help out.

