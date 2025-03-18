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

    public function trigger( $quote_id ) {  
        if ( ! $quote_id ) {
            return;
        }
    
        // Check if the quote exists
        $this->object = get_post( $quote_id );
        if ( ! $this->object ) {
            return;
        }
    
        $this->placeholders['{quote_id}'] = $quote_id;
         // Force WooCommerce to send the email as HTML
        add_filter( 'woocommerce_mail_content_type', array( $this, 'set_email_content_type' ) );
    
        // Log email parameters before sending
        $subject = $this->get_subject();
        $content = $this->get_content_html();
        $headers = $this->get_headers();   
        $this->send(
            get_option( 'admin_email' ),
            $subject,
            $content,
            $headers,
            $this->get_attachments()
        );
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
    
        // Retrieve meta data for the quote
        $quote_meta = get_post_meta( $this->placeholders['{quote_id}'], '', true );
    
        // Get email and check if user was created
        $email = $quote_meta['_acl_email'][0] ?? '';
        $user = get_user_by( 'email', $email );
        $password_message = '';
    
        if ( $user && get_user_meta( $user->ID, '_acl_generated_password', true ) ) {
            // User was created during RFQ submission
            $password_reset_link = wp_lostpassword_url();
            $password_message = "
                <h3>We've created an account for you to track your quote.</h3>
                <p>Your username: <strong>{$email}</strong></p>
                <p>To set your password, visit this link: <a href='{$password_reset_link}'>Reset Password</a></p>
            ";
        }
    
        // Ensure quote_items is properly unserialized
        $quote_items = isset( $quote_meta['_acl_quote_items'][0] ) ? maybe_unserialize( $quote_meta['_acl_quote_items'][0] ) : array();
    
        wc_get_template(
            $this->template_html,
            array(
                'quote_id'       => $this->placeholders['{quote_id}'],
                'email_heading'  => $this->get_heading(),
                'sent_to_admin'  => true,
                'plain_text'     => false,
                'email'          => $this,
                'quote_details'  => !empty($quote_meta) ? $quote_meta : array(),
                'quote_items'    => $quote_items,
                'password_message' => $password_message // Include new user message
            ),
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