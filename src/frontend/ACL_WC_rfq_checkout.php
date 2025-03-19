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
        if (!is_user_logged_in()) {
            wc_print_notice('You must be logged in to submit a quote.', 'error');
            echo '<p><a href="' . esc_url(wp_login_url(get_permalink())) . '" class="button">Login</a></p>';
            return;
        }
        ?>
        <form class="acl_quote_submission_form" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post">
            <?php wp_nonce_field('acl_quote_submission', 'acl_quote_nonce'); ?>
            <input type="hidden" name="action" value="acl_create_quote">
            
            <div class="rfq-form-layout">
                <h3><?php esc_html_e('Submit Your Quote Request', 'woocommerce'); ?></h3>
                <div class="form-row">
                    <div class="field-group">
                        <label for="acl_first_name"><?php esc_html_e('First Name:', 'woocommerce'); ?> <span class="required">*</span></label>
                        <input type="text" id="acl_first_name" name="acl_first_name" required>
                    </div>
                    <div class="field-group">
                        <label for="acl_last_name"><?php esc_html_e('Last Name:', 'woocommerce'); ?> <span class="required">*</span></label>
                        <input type="text" id="acl_last_name" name="acl_last_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field-group">
                        <label for="acl_email"><?php esc_html_e('Email Address:', 'woocommerce'); ?> <span class="required">*</span></label>
                        <input type="email" id="acl_email" name="acl_email" required>
                    </div>
                    <div class="field-group">
                        <label for="acl_phone"><?php esc_html_e('Phone Number:', 'woocommerce'); ?></label>
                        <input type="tel" id="acl_phone" name="acl_phone">
                    </div>
                </div>
                <div class="form-row single-field">
                    <div class="field-group">
                        <label for="acl_address_line1"><?php esc_html_e('Shipping Address Line 1:', 'woocommerce'); ?></label>
                        <input type="text" id="acl_address_line1" name="acl_address_line1">
                    </div>
                </div>
                <div class="form-row single-field">
                    <div class="field-group">
                        <label for="acl_address_line2"><?php esc_html_e('Shipping Address Line 2:', 'woocommerce'); ?></label>
                        <input type="text" id="acl_address_line2" name="acl_address_line2">
                    </div>
                </div>
                <div class="form-row">
                    <div class="field-group">
                        <label for="acl_suburb"><?php esc_html_e('Shipping Suburb:', 'woocommerce'); ?> <span class="required">*</span></label>
                        <input type="text" id="acl_suburb" name="acl_suburb" required>
                    </div>
                    <div class="field-group">
                        <label for="acl_state"><?php esc_html_e('Shipping State:', 'woocommerce'); ?> <span class="required">*</span></label>
                        <input type="text" id="acl_state" name="acl_state" required>
                    </div>
                </div>
                <div class="form-row single-field">
                    <div class="field-group">
                        <label for="acl_postcode"><?php esc_html_e('Shipping Post Code:', 'woocommerce'); ?> <span class="required">*</span></label>
                        <input type="text" id="acl_postcode" name="acl_postcode" required>
                    </div>
                </div>
                <div class="form-row">
                    <button type="submit" class="button alt" name="acl_place_quote" value="Submit Quote"><?php esc_html_e('Submit Quote', 'woocommerce'); ?></button>
                </div>
            </div>
        </form>
        <?php
    }

    public static function acl_display_quote_form() {
        self::acl_quote_form();
    }  
}
