<?php
namespace ACLWcShortcodes\ACLWCRFQCheckout;
use ACLWcShortcodes\Helpers\ACL_WC_Helpers;

/**
 * Class ACL_WC_RFQ_checkout
 * Handles the functionality for managing RFQ cacheckout and "quotes"  in WooCommerce.
 */
class ACL_WC_RFQ_checkout {

    public static function acl_register_quote_post_type() {
        $labels = array(
            'name'               => _x( 'Quotes', 'post type general name', 'your-text-domain' ),
            'singular_name'      => _x( 'Quote', 'post type singular name', 'your-text-domain' ),
            // ... more labels as needed
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'quote' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'custom-fields' ),
        );

        register_post_type( 'acl_quote', $args );
    }

    public static function acl_quote_form() {
        ?>
            <input type="hidden" name="action" value="acl_create_quote">
            <label for="acl_name">Name:</label><br>
            <input type="text" id="acl_name" name="acl_name" required><br>
            <label for="acl_address">Address:</label><br>
            <input type="text" id="acl_address" name="acl_address"><br>
            <label for="acl_phone">Phone Number:</label><br>
            <input type="tel" id="acl_phone" name="acl_phone" required><br>
            <label for="acl_email">Email Address:</label><br>
            <input type="email" id="acl_email" name="acl_email" required><br>
            <label for="acl_postcode">Shipping Post Code:</label><br>
            <input type="text" id="acl_postcode" name="acl_postcode" required><br>
        <?php
    }
    
    public static function acl_display_quote_form() {
        if ( ! is_user_logged_in() ) {
            wc_print_notice( 'You must be logged in to proceed.', 'error' );
            return;
        }
        self::acl_quote_form();
    }    
}
