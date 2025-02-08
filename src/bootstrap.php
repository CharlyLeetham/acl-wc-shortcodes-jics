<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL_WC_Shortcodes.php';
require_once ACL_WC_SHORTCODES_DIR . 'src/helpers/helpers.php';
require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL_WC_rfq_cart.php';

function acl_wc_shortcodes_init() {
    new ACL_WC_Shortcodes();
    add_action( 'woocommerce_before_subcategory_title', array( 'ACL_WC_Helpers', 'acl_woocommerce_subcategory_thumbnail' ), 10);
    add_action( 'woocommerce_before_subcategory', array( 'ACL_WC_Helpers', 'acl_woocommerce_template_loop_category_link_open' ), 10 );
    add_action( 'woocommerce_before_shop_loop_item', array( 'ACL_WC_Helpers', 'acl_woocommerce_template_loop_product_link_open' ), 10 );
    add_action( 'woocommerce_shop_loop_subcategory_title', array( 'ACL_WC_Helpers', 'acl_woocommerce_template_loop_category_title' ), 10 );
    add_action ( 'woocommerce_after_shop_loop_item_title', array( 'ACL_WC_Helpers', 'acl_woocommerce_template_loop_product_title' ), 10 );    
}

add_action( 'init', 'acl_wc_shortcodes_init' );

add_action( 'wp_enqueue_scripts', 'acl_wc_shortcodes_scripts' );

function acl_wc_shortcodes_scripts() {
    $stylesheet_path = plugin_dir_path(__FILE__) . 'assets/css/acl-wc-shortcodes.css';
    $version = filemtime( $stylesheet_path );
    wp_enqueue_style( 'acl-wc-shortcodes-style', plugins_url( 'assets/css/acl-wc-shortcodes.css', __FILE__), array(), $version, 'all' ); 
    wp_enqueue_script('acl-wc-shortcodes-js', plugins_url('assets/js/acl-wc-shortcodes.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('acl-wc-shortcodes-js', 'acl_wc_shortcodes', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('acl_add_to_quote_cart')
    ));       
}

function acl_sc_remove() {
    remove_shortcode ( 'products' );
    remove_action ( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
    remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
    remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
    remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );       
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
    remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );    
    add_shortcode('products', array('ACL_WC_Shortcodes', 'acl_products_shortcode'));    
    add_action( 'woocommerce_single_product_summary', array( 'ACL_WC_Helpers', 'acl_custom_product_buttons' ), 30 );
    add_shortcode( 'acl_mini_rfq_cart', array( 'ACL_WC_Shortcodes', 'acl_mini_rfq_cart_shortcode' ) );    
}    
add_action( 'wp_loaded', 'acl_sc_remove' );


// Override visibility check for products
function force_show_all_products($visible, $product_id) {
    return true;
}
add_filter('woocommerce_product_is_visible', 'force_show_all_products', 10, 2);


$rfq_cart = new ACL_WC_RFQ_cart();
// Optionally, hook into an action where this should initialize, like:
add_action( 'init', array( $rfq_cart, 'acl_start_quote_cart' ) ); 

add_action( 'wp_ajax_acl_add_to_quote_cart', array( 'ACL_WC_Helpers', 'acl_add_to_quote_cart_ajax' ) );
add_action( 'wp_ajax_nopriv_acl_add_to_quote_cart', array( 'ACL_WC_Helpers', 'acl_add_to_quote_cart_ajax' ) );
