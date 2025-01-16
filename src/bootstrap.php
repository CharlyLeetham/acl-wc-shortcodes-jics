<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Include your classes without explicitly loading WooCommerce
    require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL_WC_Shortcodes.php';
    require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL-class-wc-widget-products.php';

    function acl_wc_shortcodes_init() {
        new ACL_WC_Shortcodes();
    }

    // Use a later hook to ensure WooCommerce is fully loaded
    add_action('init', 'acl_wc_shortcodes_init', 20);

    // Template override
    add_filter( 'woocommerce_locate_template', 'acl_locate_template', 10, 3 );
} else {
    // Notice if WooCommerce is not active
    function acl_wc_shortcodes_admin_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e( 'ACL WooCommerce Shortcodes requires WooCommerce to be installed and activated.', 'acl-wc-shortcodes' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'acl_wc_shortcodes_admin_notice' );
}

// Template search function
function acl_locate_template( $template, $template_name, $template_path ) {
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

    return $template;
}