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
	* Version:           1.4
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

	if ( ! defined( 'buythis_shortcode' ) ) {
		define( 'buythis_shortcode', 1 );

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
			if ( ! $affiliate_id ) {
				$options = get_option( 'buythis_shortcode_options' );
				$affiliate_id = $options['default_affiliate_id'];
			}

			$result = buythis_shortcode_parse( $sku, $path, $affiliate_id );

			return $result;
		}

		function buythis_shortcode_cache_api( $url ) {
			static $cache = array();

			if ( ! isset( $cache[ $url ] ) ) {
				$body = wp_remote_retrieve_body( wp_remote_get( $url ) );
				$parsed = json_decode( $body );
				if ( is_object ( $parsed ) ) {
					$cache[ $url ] = $parsed;
				}
			}

			return isset( $cache[ $url ] ) ? $cache[ $url ] : null;
		}

		function buythis_shortcode_data( $sku ) {
			static $cache = array();
			if ( ! isset( $cache[ $sku ] ) ) {
				$cache[ $sku ] = buythis_shortcode_cache_api( "https://data.buythis.co.za/product/$sku.json" );
				if ( isset( $cache[ $sku ]->price->regular ) ) {
					$cache[ $sku ]->price->regular = round($cache[ $sku ]->price->regular * 1.15, 2);
				}
				if ( isset( $cache[ $sku ]->price->sale ) ) {
					$cache[ $sku ]->price->sale = round($cache[ $sku ]->price->sale * 1.15, 2);
				}
			}
			return $cache[ $sku ];
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
			static $cache = array();
			if ( ! isset ( $cache[ $sku ] ) ) {
				$cache[ $sku ] = buythis_shortcode_cache_api( "https://data.buythis.co.za/product/$sku/price.json" );
				foreach ( $cache[ $sku ] as &$price ) {
					if ( isset( $price->regular ) ) {
						$price->regular = round($price->regular * 1.15, 2);
					}
					if ( isset( $price->sale ) ) {
						$price->sale = round($price->sale * 1.15, 2);
					}
				}
			}
			return $cache[ $sku ];
		}

		function buythis_shortcode_normalize( $sku, $path, $affiliate_id ) {
			$default_format = fn( $idempotent ) => $idempotent;
			$currency_format = fn( $number ) =>
				is_numeric( $number )
					? 'R ' . number_format( $number, 0, '.', ' ' )
					: null;
			$do_blocks_format = fn( $content ) =>
				do_blocks(
					str_replace(
						'buybuybuy',
						buythis_shortcode_parse( $sku, 'other.affiliate', $affiliate_id ),
						$content
					)
				);

			$format = $default_format;

			switch ( $path ) {
				case 'content':
					$fields = array(
						'display.content'
					);
					$format = $do_blocks_format;
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

		function buythis_shortcode_parse( $sku, $path, $affiliate_id ) {
			$path = trim($path);
			$subpaths = null;

			// Handle round braces
			if ( preg_match_all( '/\([^)]+\)/', $path, $subpaths, PREG_OFFSET_CAPTURE ) ) {
				$subpaths = $subpaths[0];
				$subresults = array();
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
			$data = null;
			$valid = false;

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

			$valid = buythis_shortcode_schema_validate( $sku, $path, $affiliate_id );

			if ( $result && $result !== $data && ! is_array( $result ) && ! is_object( $result ) ) {
				return $format( $result );
			} elseif ( $valid ) {
				return '';
			} else {
				return __('Invalid Value Setting', 'buythis-shortcode');
			}
		}

		function buythis_shortcode_schema() {
			// Download and cache data definition (i.e. schema)
			static $schema_array = null;
			if ( $schema_array === null ) {
				$schema_array = get_option('buythis_shortcode_schema');
			}
			if ( ! $schema_array || $schema_array['time'] < time() - 60 * 60 * 7 ) {
				$time = time();
				$schema = json_decode(
					wp_remote_retrieve_body(
						wp_remote_get(
							'https://stoplight.io/api/v1/projects/fulfillment/fulfillment-codes-database/nodes/Models/product.v1.json'
						)
					)
				);
				if ( is_object ( $schema ) ) {
					$schema_array = array(
						'schema' => $schema,
						'time' => $time
					);
					update_option( 'buythis_shortcode_schema', $schema_array );
				}
			}

			return $schema_array && isset( $schema_array['schema'] ) ? $schema_array['schema'] : null;
		}

		function buythis_shortcode_schema_validate( $sku, $path, $affiliate_id ) {
			// Obtain or generate schema for data source
			$split = explode( '.', $path );
			switch ( $split[0] ) {
				case 'data':
					$data = buythis_shortcode_data( $sku );
					$schema = buythis_shortcode_schema();
					break;

				case 'display':
					$data = buythis_shortcode_display( $sku );
					$schema = buythis_shortcode_schema();
					break;

				case 'other':
					$data = buythis_shortcode_other( $sku, $affiliate_id );
					$schema = json_decode( json_encode(
						[
							'properties' => [
								'affiliate' => [
									'type' => 'string'
								]
							]
						]
					) );
					break;

				case 'price':
					$data = buythis_shortcode_price( $sku );
					$schema = json_decode( json_encode( array(
						'properties' =>
							array_reduce(
								array_keys( get_object_vars( $data ) ),
								function( $carry, $date ) {
									$carry[$date] = array(
										'properties' => array(
											'regular' => array(
												'type' => 'number'
											),
											'sale' => array(
												'type' => 'number'
											)
										)
									);
									return $carry;
								},
								array()
							)
					) ) );
					break;

				default:
					return false;
			}

			// Validate path
			$valid = true;
			foreach ( array_slice( $split, 1 ) as $key ) {
				$valid = $valid && ( isset( $schema->properties->$key ) );
				if ( ! $valid ) {
					break;
				}
				$schema = $schema->properties->$key;
			}

			return $valid && ! isset( $schema->properties );
		}

		add_shortcode( 'buythis', 'buythis_shortcode' );

		require __DIR__ . '/settings.php';
	}
?>
