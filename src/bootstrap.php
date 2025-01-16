<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Check if WooCommerce is active using a different method
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // We first check if WooCommerce is active by looking for its main plugin file in the active plugins list
    
    // Now we can safely include WooCommerce classes
    require_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php'; // This ensures WooCommerce is fully loaded
    
    // Check for WC_Widget class existence after loading WooCommerce
    if ( class_exists( 'WC_Widget' ) ) {
        error_log('WC_Widget class exists');
        
        require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL_WC_Shortcodes.php';
        require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL-class-wc-widget-products.php';

        function acl_wc_shortcodes_init() {
            new ACL_WC_Shortcodes();
        }

        add_action('woocommerce_init', 'acl_wc_shortcodes_init');

        // Template override
        add_filter( 'woocommerce_locate_template', 'acl_locate_template', 10, 3 );
    } else {
        error_log('WC_Widget class does NOT exist even after loading WooCommerce');
    }
} else {
    // WooCommerce not active
    function acl_wc_shortcodes_admin_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e( 'ACL WooCommerce Shortcodes requires WooCommerce to be installed and activated.', 'acl-wc-shortcodes' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'acl_wc_shortcodes_admin_notice' );
    error_log('WooCommerce is not active');
}

// Template search function
function acl_locate_template( $template, $template_name, $template_path ) {
    global $woocommerce;
    $_template = $template;
    if ( ! $template_path ) $template_path = $woocommerce->template_url;

    $plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/src/templates/';

    // Look within passed path within the theme - this is priority
    $template = locate_template(
        array(
            $template_path . $template_name,
            $template_name
        )
    );

    // If not found in theme, check in plugin directory
    if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
        $template = $plugin_path . $template_name;
    }

    // Return template file path
    return $template;
}