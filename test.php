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

	test(['sku' => 'a-chiller-1400', 'value' => 'name', 'affiliate' => 'xxx'], 'Generic AM-5200 1400W Refrigeration Industrial Water Chiller 5000Btu/h, Max.Pump Flow 10L/min and Lift 10 Metres');
	test(['sku' => 'a-chiller-1400', 'value' => 'link', 'affiliate' => 'xxx'], 'https://buythis.co.za/a-chiller-1400#xxx');
	test(['sku' => 'a-chiller-1400', 'value' => 'link_name', 'affiliate' => 'xxx'], '<a href="https://buythis.co.za/a-chiller-1400#xxx">Generic AM-5200 1400W Refrigeration Industrial Water Chiller 5000Btu/h, Max.Pump Flow 10L/min and Lift 10 Metres</a>');
	test(['sku' => 'a-chiller-1400', 'value' => 'price', 'affiliate' => 'xxx'], 'R 9 499');
	# test(['sku' => 'a-chiller-1400', 'value' => 'content', 'affiliate' => 'xxx']);

	if (test())
		echo 'all tests succeeded', "\n";

	# --------------
	# End: Run tests
	# --------------
?>
