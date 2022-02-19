<?php
	/**
	* Buythis Shortcode
	*
	* @package           Buythis_Shortcode
	* @author            Fulfillment Global Corporation <wordpress@fulfillment.global>
	* @copyright         2021-2022 Fulfillment Global Corporation
	* @license           GPL-2.0-or-later
	*
	* @wordpress-plugin
	* Plugin Name:       Buythis Shortcode
	* Plugin URI:        https://github.com/Fulfillment-Global/buythis-shortcode/
	* Description:       This plugin provides an interface between Wordpress and Buythis.co.za
	* Version:           1.1
	* Requires at least: 5.2
	* Requires PHP:      7.2
	* Author:            Fulfillment Global Corporation
	* Author URI:        https://fulfillment.global/
	* Text Domain:       buythis-shortcode
	* License:           GPL v2 or later
	* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
	* Update URI:        https://github.com/Fulfillment-Global/buythis-shortcode/
	* GitHub Plugin URI: https://github.com/Fulfillment-Global/buythis-shortcode/
	* GitHub Branch:     master
	*/

	if ( !function_exists( 'buythis_shortcode' ) ) {
		function buythis_shortcode( $atts ) {
			$atts = shortcode_atts(
				array(
					'sku'       => '',
					'value'     => '',
					'affiliate' => ''
				),
				$atts,
				'buythis'
			);

			$sku = preg_replace( '/\W+/', '-', strtolower( $atts['sku'] ) );
			$path = trim( $atts['value'] );
			$affiliate_id = trim( $atts['affiliate'] );

			$result = buythis_shortcode_parse( $sku, $path, $affiliate_id );

			return $result ?? __( 'SKU not found', 'buythis-shortcode' );
		}

		function buythis_shortcode_cache_api( $url ) {
			static $cache = [];

			if ( !isset( $cache[ $url ] ) ) {
				$body = wp_remote_retrieve_body( wp_remote_get( $url ) );
				$parsed = json_decode( $body );
				if ( is_object ( $parsed ) ) {
					$cache[ $url ] = $parsed;
				}
			}

			return isset( $cache[ $url ] ) ? $cache[ $url ] : null;
		}

		function buythis_shortcode_data( $sku ) {
			return buythis_shortcode_cache_api( "https://data.buythis.co.za/product/$sku.json" );
		}

		function buythis_shortcode_display( $sku ) {
			return buythis_shortcode_cache_api( "https://data.buythis.co.za/product/$sku/display.json" );
		}

		function buythis_shortcode_other( $sku, $affiliate_id ) {
			return (object) [
				'affiliate' => $affiliate_id ?? ''
			];
		}

		function buythis_shortcode_price( $sku ) {
			return buythis_shortcode_cache_api( "https://data.buythis.co.za/product/$sku/price.json" );
		}

		function buythis_shortcode_normalize( $sku, $path, $affiliate_id ) {
			$default_format = function( $idempotent ) {
				return $idempotent;
			};
			$currency_format = function ($number) {
				return is_numeric( $number )
					? 'R ' . number_format( $number, 0, '.', ' ' )
					: null;
			};
			$parse_blocks_format = function( $content ) use ( $sku, $affiliate_id ) {
				return do_blocks(
					str_replace(
						'buybuybuy',
						buythis_shortcode_parse( $sku, 'other.affiliate', $affiliate_id ),
						$content
					)
				);
			};

			$format = $default_format;

			switch ( $path ) {
				case 'content':
					$fields = array(
						'display.content'
					);
					$format = $parse_blocks_format;
					break;

				case 'link':
					return 'https://buythis.co.za/(display.slug)#(other.affiliate)';

				case 'link_name':
					return '<a href="https://buythis.co.za/(display.slug)#(other.affiliate)">(data.name.full|data.name.simple)</a>';

				case 'name':
					$fields = array(
						'data.name.full',
						'data.name.simple'
					);
					break;

				case 'price':
				case 'sale_price':
					$fields = array(
						'data.price.sale',
						'data.price.regular'
					);
					$format = $currency_format;
					break;

				case 'regular_price':
					$fields = array(
						'data.price.regular',
						'data.price.sale' 
					);
					$format = $currency_format;
					break;

				default:
					$fields = array( $path );
			}

			return array( $fields, $format );
		}

		function buythis_shortcode_parse( $sku, $path, $affiliate_id = null ) {
			$path = trim($path);
			$subpaths = null;

			// Handle round braces
			if ( preg_match_all( '/\([^)]+\)/', $path, $subpaths, PREG_OFFSET_CAPTURE ) ) {
				$subpaths = $subpaths[0];
				$subresults = [];
				foreach ( $subpaths as $subpath ) {
					$subresults[] = buythis_shortcode_parse( $sku, substr( $subpath[0], 1, strlen($subpath[0]) - 2 ), $affiliate_id );
				}
				$result = '';
				$prev_end_index = 0;
				foreach ( $subpaths as $i => $subpath ) {
					$result .= substr( $path, $prev_end_index, $subpath[1] - $prev_end_index ) . $subresults[$i];
					$prev_end_index = $subpath[1] + strlen($subpath[0]);
				}
				$result .= substr ( $path, $prev_end_index );
				return $result;
			}

			// Handle OR
			elseif ( $path && strstr( $path, '|' ) !== false ) {
				$fields = explode( '|', $path );
				$format = function( $idempotent ) {
					return $idempotent;
				};
			}

			// Handle standard path
			else {
				$normalized = buythis_shortcode_normalize( $sku, $path, $affiliate_id );

				if ( is_string( $normalized ) ) {
					return buythis_shortcode_parse( $sku, $normalized, $affiliate_id );
				}
				else {
					list ( $fields, $format ) = $normalized;
				}
			}

			$result = null;

			foreach ( $fields as $field ) {
				// Parse source from path
				if ( 'data.' === substr( $field, 0, strlen( 'data.' ) ) ) {
					$data = buythis_shortcode_data( $sku );
				}
				elseif ( 'display.' === substr( $field, 0, strlen( 'display.' ) ) ) {
					$data = buythis_shortcode_display( $sku );
				}
				elseif ( 'other.' === substr( $field, 0, strlen( 'other.' ) ) ) {
					$data = buythis_shortcode_other( $sku, $affiliate_id );
				}
				elseif ( 'price.' === substr( $field, 0, strlen( 'price.' ) ) ) {
					$data = buythis_shortcode_price( $sku );
				}
				else {
					continue;
				}

				// Evaluate period-separated path expression
				$result = $data;
				foreach ( array_slice( explode( '.', $field ), 1 ) as $key ) {
					if ( null === ( $result->$key ?? null ) ) {
						continue 2;
					}
					$result = $result->$key;
				}
				break;
			}

			$result = $format( $result ) ?? __( 'Invalid value', 'buythis-shortcode' );
			return $result;
		}

		add_shortcode( 'buythis', 'buythis_shortcode' );
	}
?>
