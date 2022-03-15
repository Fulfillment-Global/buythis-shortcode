=== Buythis Shortcode ===
Contributors: fulfillmentglobal
Donate link: https://buythis.co.za/
Tags: buythis
Requires at least: 5.2
Tested up to: 5.9
Stable tag: 1.4
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Buythis Shortcode

== Third party service description ==

This plugin provides a shortcode to interface between Wordpress and [Buythis.co.za](https://buythis.co.za]).

This plugin relies on [Buythis.co.za](https://buythis.co.za]) as a third party service, in order for the plugin to work.

The reason for using the third party service, is to access JSON product data from the [Buythis.co.za](https://buythis.co.za]) store.

No personal data is collected during the process.

As can be seen in the plugin's source code, this plugin only uses information that is directly and knowingly provided by the user themselves, i.e. the `sku`, `value` and `affiliate` shortcode parameters.

No other data of any kind, whatsoever, is used by this plugin, other than the parameters provided by the user to the plugin shortcode.

The only data currently submitted to the third party service via HTTP is the `sku` parameter.

Links to the third party service:

[https://buythis.co.za](https://buythis.co.za)
[https://data.buythis.co.za](https://data.buythis.co.za)
[Data definition](https://stoplight.io/api/v1/projects/fulfillment/fulfillment-codes-database/nodes/Models/product.v1.json)

Third party services' terms of use and privacy policies:

[https://buythis.co.za/terms](https://buythis.co.za/terms)
[https://buythis.co.za/privacy](https://buythis.co.za/privacy)

== Description ==

Buythis.co.za is an online store that specializes in machinery sales.

== Usage ==

To use the Buythis Shortcode plugin:

`[buythis sku="sku" value="path" affiliate="affiliate_id"]`

The provided `affiliate_id` is used to generate revenue for a given affiliate, by using this plugin's `[buythis]` shortcode on their WordPress site.

A site-wide default Affiliate ID can be configured on the Buythis Shortcode settings page, that will be used as a fallback value for the `affiliate_id` attribute, should one not be provided. This makes it easy to change the affiliate ID for a site, and prevents the need to duplicate the `affiliate_id` in each usage of the shortcode.

Depending on the ***path*** specified in the `value` parameter, one or more API calls will be made to the following endpoints (sources), where ***[sku]*** is the provided `sku` parameter:

| Source  | Endpoint URL                                            | Example `value` parameter   |
|---------|---------------------------------------------------------|-----------------------------|
| data    | `https://data.buythis.co.za/product/[sku].json`         | `data.name.full`            |
| display | `https://data.buythis.co.za/product/[sku]/display.json` | `display.content`           |
| price   | `https://data.buythis.co.za/product/[sku]/price.json`   | `price.2022-01-01.sale`     |
| other   | Dynamic fixed data                                      | `other.affiliate`           |

The API response from a source is JSON-decoded, and the ***path*** specified by the `value` parameter is extracted from the JSON response.

Thus, to extract custom JSON data from a source's API response, use a custom ***path*** in the `value` parameter to specify (describe) the source API and the JSON data to extract from it, as shown in each example `value` parameter above.

== Shortcuts ==

The following shortcut `value` options are available:

| Shortcut        | Substituted path (`value` parameter)                                                                     |
|-----------------|----------------------------------------------------------------------------------------------------------|
| `content`       | `display.content`                                                                                        |
| `link`          | `https://buythis.co.za/(display.slug)#(other.affiliate)`                                                 |
| `link_name`     | `<a href="https://buythis.co.za/(display.slug)#(other.affiliate)">(data.name.full|data.name.simple)</a>` |
| `name`          | `data.name.full|data.name.simple`                                                                        |
| `price`         | `R(data.price.sale|data.price.regular)`                                                                  |
| `regular_price` | `R(data.price.regular|data.price.sale)`                                                                  |
| `sale_price`    | `R(data.price.sale|data.price.regular)`                                                                  |

== Syntax ==

In order of precedence:

1. `()` (round braces) is used to treat a ***path*** as a format string, by only evaluating ***sub-paths*** in the format string that are contained within round braces, and leaving text in the rest of the format string that are outside round braces, intact. Nested braces, i.e. braces inside braces, are not supported, i.e. a ***sub-path*** cannot contain a format string. Round braces can contain shortcuts as ***sub-paths***.
2. `|` (OR) is to coalesce the first non-null result of a ***sub-path*** out of a set of ***path*** expressions. A ***sub-path*** can be a shortcut, but cannot contain round braces.
3. `.` (period) is the JSON separator in a ***path***.

== Frequently Asked Questions ==

Is this plugin free to use?

Yes, this plugin is free to use. The source code is also available for you to examine what the plugin does and how it works.

== Screenshots ==

1. No screenshots at present.

== Changelog ==

= 1.2 =
* 15% VAT is added to price data received from sources, since the prices are VAT exclusive.

= 1.1 =
* "Affiliate code" changed to "Affiliate ID".
* Added more source endpoints for use in the "value" parameter.
* Added more shortcuts.
* Added format string (round brace) syntax.
* Added OR (pipe) syntax.

= 1.0 =
* Initial release.

== Upgrade Notice ==

No upgrade notice at present.
