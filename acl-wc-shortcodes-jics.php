<?php
/*
Plugin Name: ACL New WooCommerce Shortcodes and Templates
Plugin URI: http://askcharlyleetham.com
Description: New Shortcodes and Templates for WooCommerce
Version: 1
Author: Charly Dwyer
Author URI: http://askcharlyleetham.com
License: GPL

Changelog
Version 1.0 - Original Version
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define('ACL_WC_SHORTCODES_DIR', plugin_dir_path( __FILE__ ));

require_once ACL_WC_SHORTCODES_DIR . 'src/bootstrap.php';