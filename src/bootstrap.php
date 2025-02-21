<?php

/**
 * Bootstrap the plugin by initializing all components.
 */

namespace ACLWcShortcodes;


class ACLWcShortcodes {
    public static function init() {

        add_action( 'init', array( __CLASS__, 'acl_wc_shortcodes_init' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'acl_wc_shortcodes_scripts' ), 1 );
        add_action( 'wp_loaded', array( __CLASS__, 'acl_sc_remove' ) );

        // Hook methods from ACL_WC_RFQ_cart assuming they are static
        add_action( 'woocommerce_init', array( 'ACLWcShortcodes\ACLWCRFQCart\ACL_WC_RFQ_cart', 'acl_start_quote_cart' ) );
        add_action( 'wp_ajax_acl_add_to_quote_cart', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_add_to_quote_cart_ajax' ) );
        add_action( 'wp_ajax_nopriv_acl_add_to_quote_cart', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_add_to_quote_cart_ajax' ) );
        add_action( 'wp_ajax_acl_remove_from_quote_cart', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_remove_from_quote_cart' ) );
        add_action( 'wp_ajax_acl_update_mini_cart', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_update_mini_cart' ) );
        add_action( 'wp_ajax_nopriv_acl_update_mini_cart', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_update_mini_cart' ) );
        add_action( 'wp_ajax_acl_update_quantity_in_quote_cart', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_update_quantity_in_quote_cart' ));
        add_action( 'wp_ajax_nopriv_acl_update_quantity_in_quote_cart', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_update_quantity_in_quote_cart' ));

        //Hook methods for Quote checkout and management
        add_action( 'wp_login', array( 'ACLWcShortcodes\ACLWCRFQCart\ACL_WC_RFQ_cart','acl_restore_rfq_login' ) , 10 , 2 );
        add_action( 'init', array( 'ACLWcShortcodes\ACLWCRFQCheckout\ACL_WC_RFQ_checkout','acl_register_quote_post_type' ) );
        add_action( 'acl_woocommerce_after_cart_table', array ( 'ACLWcShortcodes\ACLWCRFQCheckout\ACL_WC_RFQ_checkout', 'acl_display_quote_form' ) ); 
        add_action( 'wp_ajax_acl_create_quote', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_process_quote_submission' ) );
        add_action( 'wp_ajax_nopriv_acl_create_quote', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_process_quote_submission' ) );
        add_filter( 'wc_session_expiring', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_extend_session_lifetime' ) );
        add_filter( 'wc_session_expiration', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_extend_session_lifetime' ) );  
        add_action( 'wp_ajax_acl_process_login', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_process_login_submission' ) );
        add_action( 'wp_ajax_nopriv_acl_process_login', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_process_login_submission' ) );
        add_action( 'woocommerce_persistent_cart_update', array( 'ACLWcShortcodes\ACLWCRFQCart\ACL_WC_RFQ_cart', 'acl_save_rfq_cart_to_user_meta' ), 10, 2 );

        
        //Activate the Email Template and mailing program
        add_filter( 'woocommerce_init', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_ensure_email_system_ready' ) );
        add_filter( 'woocommerce_email_classes', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_register_custom_email' ) );
        add_action( 'admin_init', array( 'ACLWcShortcodes\ACLWCRFQWCEMail\ACL_WC_RFQ_Email', 'acl_force_html_email_setting' ) );

    }

    public static function acl_wc_shortcodes_init() {
        add_action( 'woocommerce_before_subcategory_title', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_woocommerce_subcategory_thumbnail' ), 10);
        add_action( 'woocommerce_before_subcategory', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_woocommerce_template_loop_category_link_open' ), 10 );
        add_action( 'woocommerce_before_shop_loop_item', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_woocommerce_template_loop_product_link_open' ), 10 );
        add_action( 'woocommerce_shop_loop_subcategory_title', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_woocommerce_template_loop_category_title' ), 10 );
        add_action( 'woocommerce_after_shop_loop_item_title', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_woocommerce_template_loop_product_title' ), 10 );
    }

    public static function acl_wc_shortcodes_scripts() {
        $stylesheet_path = ACL_WC_SHORTCODES_PATH . 'src/assets/css/acl-wc-shortcodes.css';
        $version_css = filemtime( $stylesheet_path );
        $script_path = ACL_WC_SHORTCODES_PATH . 'src/assets/js/acl-wc-shortcodes.js';
        $version_script = filemtime( $script_path );
        wp_enqueue_style( 'acl-wc-shortcodes-style', ACL_WC_SHORTCODES_URL . 'src/assets/css/acl-wc-shortcodes.css', array(), $version_css, 'all' );
        wp_enqueue_script( 'acl-wc-shortcodes-js', ACL_WC_SHORTCODES_URL . 'src/assets/js/acl-wc-shortcodes.js', array( 'jquery' ), $version_script, true );
        wp_localize_script( 'acl-wc-shortcodes-js', 'acl_wc_shortcodes', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'acl_add_to_quote_cart' ),
            'nonce'    => wp_create_nonce('acl_wc_shortcodes_nonce')
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
        add_shortcode( 'products', array( 'ACLWcShortcodes\ACLWCShortcodes\ACL_WC_Shortcodes', 'acl_products_shortcode' ) );
        add_action( 'woocommerce_single_product_summary', array( 'ACLWcShortcodes\Helpers\ACL_WC_Helpers', 'acl_custom_product_buttons' ), 30 );
        add_shortcode( 'acl_mini_rfq_cart', array( 'ACLWcShortcodes\ACLWCShortcodes\ACL_WC_Shortcodes', 'acl_mini_rfq_cart_shortcode' ) );
        add_shortcode( 'acl_rfq_cart', array( 'ACLWcShortcodes\ACLWCShortcodes\ACL_WC_Shortcodes', 'acl_rfq_cart_shortcode' ) );   
        add_shortcode( 'acl_test_send_email', array( 'ACLWcShortcodes\ACLWCShortcodes\ACL_WC_Shortcodes', 'acl_test_send_email_shortcode' ) );
    }

    public static function force_show_all_products( $visible, $product_id ) {
        return true;
    }
}