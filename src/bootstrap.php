<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL_WC_Shortcodes.php';

function acl_wc_shortcodes_init() {
    new ACL_WC_Shortcodes();
}

add_action('init', 'acl_wc_shortcodes_init');

add_action('wp_enqueue_scripts', 'acl_wc_shortcodes_scripts');
function acl_wc_shortcodes_scripts() {
    wp_enqueue_style('acl-wc-shortcodes-style', plugins_url('assets/css/acl-wc-shortcodes.css', __FILE__));
}

function acl_sc_remove() {
    remove_shortcode ( 'product' );
    add_shortcode ( 'product', 'acl_products_shortcode' );
}
add_action('wp_loaded', 'acl_sc_remove');