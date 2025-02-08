<?php

/**
 * Bootstrap the plugin by initializing all components.
 */

namespace ACLWcShortcodes;
use ACLWcShortcodes\Helpers\ACL_WC_Helpers;
use ACLWcShortcodes\ACLWCRFQCart\ACL_WC_RFQ_cart;

class ACLWcShortcodes {
    public static function init() {
        error_log('Class exists: ' . (class_exists('ACL_WC_RFQ_cart') ? 'Yes' : 'No'));
        error_log('Method exists: ' . (method_exists('ACL_WC_RFQ_cart', 'acl_start_quote_cart') ? 'Yes' : 'No'));
        add_action( 'init', array( __CLASS__, 'acl_wc_shortcodes_init' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'acl_wc_shortcodes_scripts' ) );
        add_action( 'wp_loaded', array( __CLASS__, 'acl_sc_remove' ) );

        // Hook methods from ACL_WC_RFQ_cart assuming they are static
        add_action( 'init', array( 'ACL_WC_RFQ_cart', 'acl_start_quote_cart' ) );

        add_action( 'wp_ajax_acl_add_to_quote_cart', array( 'ACL_WC_Helpers', 'acl_add_to_quote_cart_ajax' ) );
        add_action( 'wp_ajax_nopriv_acl_add_to_quote_cart', array( 'ACL_WC_Helpers', 'acl_add_to_quote_cart_ajax' ) );
    }

    public static function acl_wc_shortcodes_init() {
        // Add any additional initialization logic here if needed
    }

    public static function acl_wc_shortcodes_scripts() {
        $stylesheet_path = ACL_WC_SHORTCODES_PATH . 'assets/css/acl-wc-shortcodes.css';
        $version = filemtime( $stylesheet_path );
        wp_enqueue_style( 'acl-wc-shortcodes-style', ACL_WC_SHORTCODES_URL . 'assets/css/acl-wc-shortcodes.css', array(), $version, 'all' );
        wp_enqueue_script( 'acl-wc-shortcodes-js', ACL_WC_SHORTCODES_URL . 'assets/js/acl-wc-shortcodes.js', array( 'jquery' ), '1.0', true );
        wp_localize_script( 'acl-wc-shortcodes-js', 'acl_wc_shortcodes', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'acl_add_to_quote_cart' )
        ) );
    }

    public static function acl_sc_remove() {
        remove_shortcode( 'products' );
        remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
        remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
        remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
        remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
        remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
        add_shortcode( 'products', array( 'ACL_WC_Shortcodes', 'acl_products_shortcode' ) );
        add_action( 'woocommerce_single_product_summary', array( 'ACL_WC_Helpers', 'acl_custom_product_buttons' ), 30 );
        add_shortcode( 'acl_mini_rfq_cart', array( 'ACL_WC_Shortcodes', 'acl_mini_rfq_cart_shortcode' ) );
    }

    public static function force_show_all_products( $visible, $product_id ) {
        return true;
    }
}