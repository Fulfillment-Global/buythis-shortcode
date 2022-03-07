<?php
	if ( ! defined( 'buythis_shortcode_settings' ) ) {
		define( 'buythis_shortcode_settings', 1 );

		function buythis_shortcode_add_settings_page() {
			add_options_page( 'Buythis Shortcode', 'Buythis Shortcode', 'manage_options', 'buythis_shortcode', 'buythis_shortcode_render_plugin_settings_page' );
		}

		function buythis_shortcode_register_settings() {
			register_setting( 'buythis_shortcode_options', 'buythis_shortcode_options', 'buythis_shortcode_options_validate' );
			add_settings_section( 'main_settings', 'Buythis Shortcode Settings', '', 'buythis_shortcode' );
			add_settings_field( 'buythis_shortcode_setting_default_affiliate_id', 'Default Affiliate', 'buythis_shortcode_setting_default_affiliate_id', 'buythis_shortcode', 'main_settings' );
		}

		function buythis_shortcode_render_plugin_settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			echo '<div class="wrap"><form action="options.php" method="post">';
			settings_fields( 'buythis_shortcode_options' );
			do_settings_sections( 'buythis_shortcode' );
			submit_button();
			echo '</form></div>';
		}

		function buythis_shortcode_options_validate( $input ) {
			$new_input = [
				'default_affiliate_id' => trim( $input['default_affiliate_id'] )
			];
			if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $new_input['default_affiliate_id'] ) ) {
				$new_input['default_affiliate_id'] = '';
			}

			return $new_input;
		}

		function buythis_shortcode_setting_default_affiliate_id() {
			$options = get_option( 'buythis_shortcode_options' );
			echo "<input id='buythis_shortcode_setting_default_affiliate_id' name='buythis_shortcode_options[default_affiliate_id]' type='text' value='" . esc_attr( $options['default_affiliate_id'] ) . "' />";
		}

		add_action( 'admin_init', 'buythis_shortcode_register_settings' );
		add_action( 'admin_menu', 'buythis_shortcode_add_settings_page' );
	}
?>
