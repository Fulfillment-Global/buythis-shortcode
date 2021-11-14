<?php
	/**
	* Buythis Shortcode
	*
	* @package           Buythis_Shortcode
	* @author            Fulfillment Global Corporation <wordpress@fulfillment.global>
	* @copyright         2021 Fulfillment Global Corporation
	* @license           GPL-2.0-or-later
	*
	* @wordpress-plugin
	* Plugin Name:       Buythis Shortcode
	* Plugin URI:        https://github.com/Fulfillment-Global/buythis-shortcode/
	* Description:       This plugin provides an interface between Wordpress and Buythis.co.za
	* Version:           1.0
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
			$value = trim( $atts['value'] );
			$affiliate = trim( $atts['affiliate'] );

			switch ( $value ) {
				case 'regular_price':
					$fields = array(
						'price.regular',
						'price.sale' 
					);
					$format = function( $number ) {
						return is_numeric( $number )
							? 'R ' . number_format( $number, 0, '.', ' ' )
							: null;
					};
					break;

				case 'sale_price':
					$fields = array(
						'price.sale',
						'price.regular'
					);
					$format = function( $number ) {
						return is_numeric( $number )
							? 'R ' . number_format( $number, 2, '.', ' ' )
							: null;
					};
					break;

				default:
					$fields = array( $value );
					$format = function( $value ) {
						return $value;
					};
			}

			$product = wp_remote_retrieve_body( wp_remote_get( "https://data.buythis.co.za/product/$sku.json" ) );

			if ( $product ) {
				$prodct = json_decode( $product );
				foreach ( $fields as $field ) {
					$result = $product;
					foreach ( explode( '.', $field ) as $key ) {
						if ( null === ( $result->$key ?? null ) ) {
							continue 2;
						}
						$result = $result->$key;
					}
					break;
				}
			} else {
				$result = null;
			}

			return $result ? ( $format( $result ) ?? __( 'Invalid value', 'buythis-shortcode' ) : __( 'SKU not found', 'buythis-shortcode' );
		}

		add_shortcode( 'buythis', 'buythis_shortcode' );
	}
?>
