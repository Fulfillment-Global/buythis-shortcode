<?php
	# -----------------
	# Begin: Test stubs
	# -----------------

	function __() {
		return func_get_arg(0);
	}

	function add_action() {
	}

	function add_shortcode() {
	}

	function cache($key, $value = null) {
		static $cache = [];
		if ($value)
			$cache[$key] = $value;
		return $cache[$key] ?? null;
	}

	function do_blocks($str) {
		return $str;
	}

	function get_option($key) {
		return cache($key);
	}

	function shortcode_atts($atts) {
		return cache('atts');
	}

	function update_option($key, $value) {
		return cache($key, $value);
	}

	function wp_remote_get($url) {
		return file_get_contents($url);
	}

	function wp_remote_retrieve_body() {
		return func_get_arg(0);
	}

	# ---------------
	# End: Test stubs
	# ---------------

	# -----------------------------
	# Begin: Load code to be tested
	# -----------------------------

	require 'buythis-shortcode.php';

	# ---------------------------
	# End: Load code to be tested
	# ---------------------------

	# -------------------
	# Begin: Test harness
	# -------------------

	function success($new_success = null) {
		static $success = true;
		if ($new_success !== null)
			$success = $new_success;
		return $success;
	}

	function test_shortcode($atts = null, $expected_result = null) {
		if ($atts) {
			cache('atts', $atts);
			$result = buythis_shortcode($atts);
			if ($result != $expected_result) {
				success(false);
				echo 'test_shortcode() fail: ', "\n", print_r($atts, true), "\n", 'expected result: ', $expected_result, "\n", 'actual result: ', $result, "\n";
			}
		}
	}

	function test_validate($should_be_valid, $sku, $path, $affiliate_id) {
		$valid = buythis_shortcode_schema_validate($sku, $path, $affiliate_id);
		if ($valid !== $should_be_valid) {
			success(false);
			echo 'test_validate() fail: ', "\n", "sku: $sku", "\n", "path: $path", "\n", "affiliate_id: $affiliate_id", "\n";
		}
	}

	# -----------------
	# End: Test harness
	# -----------------

	# ----------------
	# Begin: Run tests
	# ----------------

	# Positive tests
	test_shortcode(['sku' => 'V-800P', 'value' => 'link', 'affiliate' => 'cornerstone'], 'https://buythis.co.za/v-800p#cornerstone');
	test_shortcode(['sku' => 'V-800P', 'value' => 'link_name', 'affiliate' => 'cornerstone'], '<a href="https://buythis.co.za/v-800p#cornerstone">AM.CO.ZA&reg; V-Series&trade; High-Pressure High-Speed USB Vinyl Cutter with 800mm Working Area</a>');
	test_shortcode(['sku' => 'V-800P', 'value' => 'name', 'affiliate' => 'cornerstone'], 'AM.CO.ZA&reg; V-Series&trade; High-Pressure High-Speed USB Vinyl Cutter with 800mm Working Area');
	test_shortcode(['sku' => 'V-800P', 'value' => 'price', 'affiliate' => 'cornerstone'], 'R 6 094');
	test_shortcode(['sku' => 'V-800P', 'value' => 'price.2022-02-22.sale', 'affiliate' => 'cornerstone'], '6438.85');
	test_shortcode(['sku' => 'V-800P', 'value' => 'other.affiliate', 'affiliate' => 'cornerstone'], 'cornerstone');
	test_validate(true, 'V-800P', 'price.2022-02-27.sale', 'cornerstone');
	test_validate(true, 'V-800P', 'price.2022-02-27.regular', 'cornerstone');

	# Negative tests
	test_shortcode(['sku' => 'V-800P', 'value' => 'data.invalid.value', 'affiliate' => 'cornerstone'], 'Invalid Value Setting');
	test_shortcode(['sku' => 'V-800P', 'value' => 'data.price.regular', 'affiliate' => 'cornerstone'], '');
	test_shortcode(['sku' => 'V-800P', 'value' => 'invalid', 'affiliate' => 'cornerstone'], 'Invalid Value Setting');
	test_shortcode(['sku' => 'V-800P', 'value' => 'price.sale', 'affiliate' => 'cornerstone'], 'Invalid Value Setting');
	test_shortcode(['sku' => 'V-800P', 'value' => 'price.2022-02-27.regular', 'affiliate' => 'cornerstone'], '');

	if (success())
		echo 'all tests succeeded', "\n";

	# --------------
	# End: Run tests
	# --------------
?>
