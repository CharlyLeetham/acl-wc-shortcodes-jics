<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL-class-wc-widget-products.php';
require_once ACL_WC_SHORTCODES_DIR . 'src/frontend/ACL_WC_Shortcodes.php';

function acl_wc_shortcodes_init() {
    new ACL_WC_Widget_Products();
}

add_action('woocommerce_loaded', 'acl_wc_shortcodes_init');

add_filter( 'woocommerce_locate_template', 'acl_locate_template', 10, 3 );

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