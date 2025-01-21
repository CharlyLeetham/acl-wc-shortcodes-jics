<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL_WC_Shortcodes.php';
require_once ACL_WC_SHORTCODES_DIR . 'src/helpers/helpers.php';

function acl_wc_shortcodes_init() {
    new ACL_WC_Shortcodes();
}

add_action( 'init', 'acl_wc_shortcodes_init' );

add_action( 'wp_enqueue_scripts', 'acl_wc_shortcodes_scripts' );
function acl_wc_shortcodes_scripts() {
    $stylesheet_path = plugin_dir_path(__FILE__) . 'assets/css/acl-wc-shortcodes.css';
    $version = filemtime( $stylesheet_path );
    wp_enqueue_style( 'acl-wc-shortcodes-style', plugins_url( 'assets/css/acl-wc-shortcodes.css', __FILE__), array(), $version, 'all' );    
}

function acl_sc_remove() {
    remove_shortcode ( 'products' );
    add_shortcode('products', array('ACL_WC_Shortcodes', 'acl_products_shortcode'));
}    

add_action( 'wp_loaded', 'acl_sc_remove' );

// Remove the default function
remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
add_action( 'woocommerce_before_subcategory_title', array( 'ACL_WC_Helpers', 'acl_woocommerce_subcategory_thumbnail' ), 10);