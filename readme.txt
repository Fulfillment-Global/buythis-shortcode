=== Buythis.co.za Shortcode ===
Contributors: fulfillmentglobal
Donate link: https://buythis.co.za/
Tags: buythis
Requires at least: 5.2
Tested up to: 5.8.2
Stable tag: 1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Buythis.co.za Shortcode

== Third party service description ==

This plugin provides an interface between Wordpress and Buythis.co.za.

This plugin relies on Buythis.co.za as a third party service, under the circumstance of the plugin's normal

operation. The reason for using the third party service is to access JSON product data from the Buythis.co.za

store. No personal data is collected during the process.

As can be seen in the plugin's source code, this plugin only uses information that is directly

and knowingly provided by the user themselves, i.e. the "sku", "value" and "affiliate" shortcode

parameters. No other data of any kind, whatsoever, is used by this plugin, other than the parameters given

in the plugin shortcode. The only data submitted to the third party service via HTTP is the "sku" parameter.

Links to the third party service:

https://buythis.co.za
https://data.buythis.co.za

Third party services' terms of use and privacy policies:

https://buythis.co.za/terms
https://buythis.co.za/privacy

== Description ==

Buythis.co.za is an online store that specializes in machinery sales.

To use the Buythis.co.za Shortcode plugin:

[buythis sku="sku" value="path" affiliate="affiliate_code"]

An API call will be made to https://data.buythis.co.za/product/<sku>.json (where <sku> is the provided "sku"

attribute). The API response is JSON-decoded, and the "path" specified by the "value" attribute is extracted

from the API JSON response. The provided "affiliate_code" is used to generate revenue for the given affiliate,

by using this plugin's [buythis] shortcode on their WordPress site.

== Frequently Asked Questions ==

Is this plugin free to use?

Yes, this plugin is free to use. The source code is also available for you to examine what the plugin does and how it works.

== Screenshots ==

1. No screenshots at present.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

No upgrade notice at present.
