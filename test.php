<?php
	# -----------------
	# Begin: Test stubs
	# -----------------

	function add_shortcode() {
	}

	function shortcode_atts_cache($atts = null) {
		static $_atts;
		if ($atts)
			$_atts = $atts;
		return $_atts;
	}

	function shortcode_atts($atts) {
		return shortcode_atts_cache();
	}

	function __() {
		return func_get_arg(0);
	}

	function wp_remote_retrieve_body() {
		return func_get_arg(0);
	}

	function wp_remote_get($url) {
		return file_get_contents($url);
	}

	function do_blocks($str) {
		return $str;
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

	function test($atts = null, $expected_result = null) {
		static $success = true;
		if ($atts) {
			shortcode_atts_cache($atts);
			$result = buythis_shortcode($atts);
			if ($result != $expected_result) {
				$success = false;
				echo 'test fail: ', "\n", print_r($atts, true), "\n", 'expected result: ', $expected_result, "\n", 'actual result: ', $result, "\n";
			}
		}
		return $success;
	}

	# -----------------
	# End: Test harness
	# -----------------

	# ----------------
	# Begin: Run tests
	# ----------------

	# Positive tests
	test(['sku' => 'V-800P', 'value' => 'link', 'affiliate' => 'cornerstone'], 'https://buythis.co.za/v-800p#cornerstone');
	test(['sku' => 'V-800P', 'value' => 'link_name', 'affiliate' => 'cornerstone'], '<a href="https://buythis.co.za/v-800p#cornerstone">AM.CO.ZA&reg; V-Series&trade; High-Pressure High-Speed USB Vinyl Cutter with 800mm Working Area</a>');
	test(['sku' => 'V-800P', 'value' => 'name', 'affiliate' => 'cornerstone'], 'AM.CO.ZA&reg; V-Series&trade; High-Pressure High-Speed USB Vinyl Cutter with 800mm Working Area');
	test(['sku' => 'V-800P', 'value' => 'price', 'affiliate' => 'cornerstone'], 'R 6 094');
	test(['sku' => 'V-800P', 'value' => 'price.2022-02-22.sale', 'affiliate' => 'cornerstone'], '6438.85');
	# test(['sku' => 'V-800P', 'value' => 'content', 'affiliate' => 'cornerstone']);

	# Negative tests
	test(['sku' => 'V-800P', 'value' => 'data.invalid.value', 'affiliate' => 'cornerstone'], 'Invalid value');
	test(['sku' => 'V-800P', 'value' => 'data.price.regular', 'affiliate' => 'cornerstone'], 'Invalid value');
	test(['sku' => 'V-800P', 'value' => 'invalid', 'affiliate' => 'cornerstone'], 'Invalid value');
	test(['sku' => 'V-800P', 'value' => 'price.sale', 'affiliate' => 'cornerstone'], 'Invalid value');

	if (test())
		echo 'all tests succeeded', "\n";

	# --------------
	# End: Run tests
	# --------------
?>
