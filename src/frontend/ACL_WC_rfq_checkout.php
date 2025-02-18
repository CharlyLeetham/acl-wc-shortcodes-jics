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
        <div class="rfq-form-layout">
            <div class="form-row">
                <label for="acl_first_name">First Name:</label>
                <input type="text" id="acl_first_name" name="acl_first_name" required>
                <label for="acl_last_name">Last Name:</label>
                <input type="text" id="acl_last_name" name="acl_last_name" required>
            </div>
            <div class="form-row">
                <label for="acl_email">Email Address:</label>
                <input type="email" id="acl_email" name="acl_email" required>
                <label for="acl_phone">Phone Number:</label>
                <input type="tel" id="acl_phone" name="acl_phone" required>
            </div>
            <div class="form-row">
                <label for="acl_address_line1">Shipping Address Line 1:</label>
                <input type="text" id="acl_address_line1" name="acl_address_line1" required>
            </div>
            <div class="form-row">
                <label for="acl_address_line2">Shipping Address Line 2:</label>
                <input type="text" id="acl_address_line2" name="acl_address_line2">
            </div>
            <div class="form-row">
                <label for="acl_suburb">Shipping Suburb:</label>
                <input type="text" id="acl_suburb" name="acl_suburb" required>
                <label for="acl_state">Shipping State:</label>
                <input type="text" id="acl_state" name="acl_state" required>
            </div>
            <div class="form-row">
                <label for="acl_postcode">Shipping Post Code:</label>
                <input type="text" id="acl_postcode" name="acl_postcode" required>
            </div>
        </div>
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
