<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL_WC_Shortcodes.php';
require_once ACL_WC_SHORTCODES_DIR . 'src/helpers/helpers.php';

function acl_wc_shortcodes_init() {
    new ACL_WC_Shortcodes();
    add_action( 'woocommerce_before_subcategory_title', array( 'ACL_WC_Helpers', 'acl_woocommerce_subcategory_thumbnail' ), 10);
    add_action ( 'woocommerce_before_subcategory', array( 'ACL_WC_Helpers', 'acl_woocommerce_template_loop_category_link_open' ), 10 );
    add_action( 'woocommerce_shop_loop_subcategory_title', array( 'ACL_WC_Helpers', 'acl_woocommerce_template_loop_category_title' ), 10 );    
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
    remove_action ( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
    remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
    remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );    
    add_shortcode('products', array('ACL_WC_Shortcodes', 'acl_products_shortcode'));
}    
add_action( 'wp_loaded', 'acl_sc_remove' );


add_filter( 'woocommerce_variation_is_visible', function($visible, $variation ) {
    // Always return true to make the variation visible, regardless of whether it has a price set
    return true;
}, 10, 2);

add_filter( 'woocommerce_product_query_meta_query', 'acl_custom_woocommerce_product_query_meta_query', 10, 2 );
function acl_custom_woocommerce_product_query_meta_query($meta_query, $query) {
    if (!is_admin() && $query->is_main_query() && isset($query->query_vars['wc_query']) && 'product_query' === $query->query_vars['wc_query']) {
        $meta_query['relation'] = 'OR';
        $meta_query[] = array('key' => '_stock_status', 'compare' => 'EXISTS');
        $meta_query[] = array('key' => '_stock_status', 'compare' => 'NOT EXISTS');
    }
    return $meta_query;
}


