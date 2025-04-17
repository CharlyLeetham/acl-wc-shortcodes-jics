<?php
namespace ACLWcShortcodes\ACLWCRFQWCEMail;
use ACLWcShortcodes\Helpers\ACL_WC_Helpers;


/**
 * Class ACL_WC_RFQ_Email
 * Handles the functionality for email templates
 */
class ACL_WC_RFQ_Email extends \WC_Email {

    public function __construct() {
        // Email slug can be used in emails
        $this->id = 'acl_quote_email';
        $this->customer_email = true;
        $this->title = __( 'ACL Quote Request', 'woocommerce' );
        $this->description = __( 'This email is sent when a new quote request is submitted.', 'woocommerce' );

        // Force WooCommerce to recognize this as an HTML email
        $this->email_type = 'html';

        // Triggers for this email
        add_action( 'acl_quote_request_created', array( $this, 'trigger' ) );

        // Call parent constructor to set other defaults
        parent::__construct();

        // Other settings
        $this->template_html  = 'emails/acl-quote-request.php';
        $this->template_plain = 'emails/plain/acl-quote-request.php';
        $this->template_base  = ACL_WC_SHORTCODES_PATH . 'src/templates/';
        $this->placeholders   = array(
            '{quote_id}' => '',
        );
    }

    public function trigger($quote_id, $email_data = []) {
        // If no quote_id, use provided email_data
        if (!$quote_id && !empty($email_data)) {
            $this->object = null;
            $this->placeholders['{quote_id}'] = 0;
            $this->email_data = $email_data;
        } else {
            if (!$quote_id || !($this->object = get_post($quote_id))) {
                return;
            }
            $this->placeholders['{quote_id}'] = $quote_id;
            $this->email_data = [];
        }
    
        // Force HTML email
        add_filter('woocommerce_mail_content_type', [$this, 'set_email_content_type']);
    
        // Send email
        $this->send(
            get_option('admin_email'),
            $this->get_subject(),
            $this->get_content_html(),
            $this->get_headers(),
            $this->get_attachments()
        );
    
        // Remove filter
        remove_filter('woocommerce_mail_content_type', [$this, 'set_email_content_type']);
    }
    
    public function set_email_content_type() {
        return 'text/html';
    }

    public function get_headers() {
        return "Content-Type: text/html\r\n";
    }

    public function get_subject() {
        return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->get_option( 'subject', 'New Quote Request' ), $this->object );
    }

    public function get_content_html() {
        ob_start();
    
        // Use email_data if provided, else fetch from post meta
        if (!empty($this->email_data)) {
            $quote_details = $this->email_data['quote_details'] ?? [];
            $quote_items = $this->email_data['quote_items'] ?? [];
            $password_message = $this->email_data['password_message'] ?? '';
            $customer_name = $this->email_data['customer_name'] ?? '';
            $address = $this->email_data['address'] ?? '';
        } else {
            $quote_meta = get_post_meta($this->placeholders['{quote_id}'], '', true);
            $quote_details = !empty($quote_meta) ? $quote_meta : [];
            $quote_items = isset($quote_meta['_acl_quote_items'][0]) ? maybe_unserialize($quote_meta['_acl_quote_items'][0]) : [];
            
            $email = $quote_meta['_acl_email'][0] ?? '';
            $user = get_user_by('email', $email);
            $password_message = '';
            if ($user && get_user_meta($user->ID, '_acl_generated_password', true)) {
                $password_reset_link = wp_lostpassword_url();
                $password_message = "
                    <h3>We've created an account for you to track your quote.</h3>
                    <p>Your username: <strong>{$email}</strong></p>
                    <p>To set your password, visit this link: <a href='{$password_reset_link}'>Reset Password</a></p>
                ";
            }
            $customer_name = ($quote_meta['_acl_first_name'][0] ?? '') . ' ' . ($quote_meta['_acl_last_name'][0] ?? '');
            $address = trim(
                ($quote_meta['_acl_address_line1'][0] ?? '') . "\n" .
                ($quote_meta['_acl_address_line2'][0] ?? '') . "\n" .
                ($quote_meta['_acl_suburb'][0] ?? '') . ', ' .
                ($quote_meta['_acl_state'][0] ?? '') . ' ' .
                ($quote_meta['_acl_postcode'][0] ?? '')
            );
        }
    
        wc_get_template(
            $this->template_html,
            [
                'quote_id' => $this->placeholders['{quote_id}'],
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text' => false,
                'email' => $this,
                'quote_details' => $quote_details,
                'quote_items' => $quote_items,
                'password_message' => $password_message,
                'customer_name' => $customer_name,
                'address' => $address
            ],
            '',
            $this->template_base
        );
    
        return ob_get_clean();
    }
       
    public function get_content_plain() {
        ob_start();

        // Retrieve meta data for the quote
        $quote_meta = get_post_meta( $this->placeholders['{quote_id}'], '', true );

        if ( empty( $quote_meta ) ) {
            
        }
    
        // Ensure quote_items is properly unserialized
        $quote_items = isset( $quote_meta['_acl_quote_items'][0] ) ? maybe_unserialize( $quote_meta['_acl_quote_items'][0] ) : array();
        wc_get_template( $this->template_plain, array(
            'quote_id'       => $this->placeholders['{quote_id}'],
            'email_heading'  => $this->get_heading(),
            'sent_to_admin'  => true,
            'plain_text'     => true,
            'email'          => $this,
            'quote_details'  => !empty($quote_meta) ? $quote_meta : array(), // Prevent null issues
        ), '', $this->template_base );
        return ob_get_clean();
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'subject' => array(
                'title'       => __( 'Subject', 'woocommerce' ),
                'type'        => 'text',
                'description' => sprintf( __( 'Defaults to <code>%s</code>', 'woocommerce' ), $this->get_subject() ),
                'placeholder' => $this->get_subject(),
                'default'     => '',
            ),
        );
    }

    public static function acl_force_html_email_setting() {
        $email_settings = get_option( 'woocommerce_acl_quote_email_settings', array() );
    
        if ( ! isset($email_settings['email_type']) || $email_settings['email_type'] !== 'html' ) {
            $email_settings['email_type'] = 'html';
            update_option( 'woocommerce_acl_quote_email_settings', $email_settings );
        }
    }
}