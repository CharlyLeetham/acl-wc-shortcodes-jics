<?php
/**
 * Plugin Name: ACL WC Shortcodes JICS
 * Description: Custom WooCommerce Shortcodes and RFQ functionality
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

register_activation_hook( __FILE__, function( ) {
    flush_rewrite_rules( );
} );

// Check if WooCommerce is active
function acl_wc_shortcodes_check_woocommerce() {
    if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'This plugin requires WooCommerce to be installed and activated.', 'acl-wc-shortcodes-jics' ) );
    }
}
add_action( 'admin_init', 'acl_wc_shortcodes_check_woocommerce' );

try {
    // Define plugin path and URL
    if ( ! defined( 'ACL_WC_SHORTCODES_PATH' ) ) {
        define( 'ACL_WC_SHORTCODES_PATH', plugin_dir_path( __FILE__ ) );
    }
    if ( ! defined( 'ACL_WC_SHORTCODES_URL' ) ) {
        define( 'ACL_WC_SHORTCODES_URL', plugin_dir_url( __FILE__ ) );
    }

    // Require all included files
    require_once ACL_WC_SHORTCODES_PATH . 'src/frontend/ACL_WC_Shortcodes.php';
    require_once ACL_WC_SHORTCODES_PATH . 'src/helpers/helpers.php';
    require_once ACL_WC_SHORTCODES_PATH . 'src/frontend/ACL_WC_rfq_cart.php';
    require_once ACL_WC_SHORTCODES_PATH . 'src/frontend/ACL_WC_rfq_checkout.php';
    //require_once ACL_WC_SHORTCODES_PATH . 'src/frontend/ACL_WC_rfq_email.php';
    require_once ACL_WC_SHORTCODES_PATH . 'src/bootstrap.php'; 

    // Initialize the plugin using the static init method
    \ACLWcShortcodes\ACLWcShortcodes::init();

} catch ( Exception $e ) {
    if ( is_admin() ) {
        add_action( 'admin_notices', function() use ( $e ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'ACL WC Shortcodes JICS Error:', 'acl-wc-shortcodes-jics' ) . ' ' . esc_html( $e->getMessage() ) . '</p></div>';
        } );
    }
}
