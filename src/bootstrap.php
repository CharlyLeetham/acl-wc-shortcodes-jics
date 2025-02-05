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

function override_woocommerce_product_query($query) {
    if (!is_admin() && $query->is_main_query() && isset($query->query_vars['wc_query']) && 'product_query' === $query->query_vars['wc_query']) {
        // Remove any stock status conditions from the query
        $query->set('meta_query', array());
        
        // Ensure no product is excluded based on stock status
        $query->set('post_status', 'publish');
        
        // Make sure all product types are included
        $query->set('post_type', 'product');
    }
}
add_action('pre_get_posts', 'override_woocommerce_product_query');

// Ensure variations are visible
add_filter('woocommerce_hide_invisible_variations', '__return_false');

// Show products without prices
add_filter('woocommerce_variation_is_visible', '__return_true');

// Override visibility check for products
function force_show_all_products($visible, $product_id) {
    return true;
}
add_filter('woocommerce_product_is_visible', 'force_show_all_products', 10, 2);


// Remove default add to cart button
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

// Add our custom buttons
add_action( 'woocommerce_after_shop_loop_item', array( 'ACL_WC_Helpers', 'acl_custom_product_buttons' ), 10 );

// For single product page, remove default add to cart and add custom buttons
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
//add_action( 'woocommerce_single_product_summary', array( 'ACL_WC_Helpers', 'acl_custom_product_buttons' ), 30 );