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
        error_log("ACL_WC_RFQ_Email: Trigger function called with Quote ID: " . $quote_id);
    
        if ( ! $quote_id ) {
            error_log("ACL_WC_RFQ_Email: No Quote ID provided.");
            return;
        }
    
        // Check if the quote exists
        $this->object = get_post( $quote_id );
        if ( ! $this->object ) {
            error_log("ACL_WC_RFQ_Email: Quote ID " . $quote_id . " does not exist.");
            return;
        }
    
        $this->placeholders['{quote_id}'] = $quote_id;
        error_log("ACL_WC_RFQ_Email: Preparing to send email to admin: " . get_option( 'admin_email' ));
    
        // Log email parameters before sending
        $subject = $this->get_subject();
        $content = $this->get_content();
        $headers = $this->get_headers();
    
        error_log("ACL_WC_RFQ_Email: Subject: " . $subject);
        error_log("ACL_WC_RFQ_Email: Headers: " . print_r($headers, true));
        error_log("ACL_WC_RFQ_Email: Content Length: " . strlen($content));
    
        $this->send(
            get_option( 'admin_email' ),
            $subject,
            $content,
            $headers,
            $this->get_attachments()
        );
    
        error_log("ACL_WC_RFQ_Email: Email send() function executed.");
    }
    

    public function get_headers() {
        return "Content-Type: text/html\r\n";
    }

    public function get_subject() {
        return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->get_option( 'subject', 'New Quote Request' ), $this->object );
    }

    public function get_content_html() {
        error_log( "ACL_WC_RFQ_Email: Generating HTML content for Quote ID: " . $this->placeholders['{quote_id}'] );
    
        ob_start();
        
        // Retrieve meta data for the quote
        $quote_meta = get_post_meta( $this->placeholders['{quote_id}'], '', true );
    
        if ( empty( $quote_meta ) ) {
            error_log("ACL_WC_RFQ_Email: No metadata found for Quote ID: " . $this->placeholders['{quote_id}']);
        }
    
        // Ensure quote_items is properly unserialized
        $quote_items = isset( $quote_meta['_acl_quote_items'][0] ) ? maybe_unserialize( $quote_meta['_acl_quote_items'][0] ) : array();
        error_log("quote_items: $quote_items");
        
        if ( empty( $quote_items ) ) {
            error_log("ACL_WC_RFQ_Email: No items found in quote!");
        }
    
        wc_get_template(
            $this->template_html,
            array(
                'quote_id'       => $this->placeholders['{quote_id}'],
                'email_heading'  => $this->get_heading(),
                'sent_to_admin'  => true,
                'plain_text'     => false,
                'email'          => $this,
                'quote_details'  => !empty($quote_meta) ? $quote_meta : array(),
                'quote_items'    => $quote_items, // Ensure quote items are passed to the template
            ),
            '',
            $this->template_base
        );
    
        $content = ob_get_clean(); // Store the generated content
    
        // Log if content is empty or successfully generated
        if ( empty( $content ) ) {
            error_log("ACL_WC_RFQ_Email: Generated email content is empty!");
        } else {
            error_log("ACL_WC_RFQ_Email: Email content generated successfully.");
        }
    
        return $content; // Return content AFTER logging
    }
    
    public function get_content_plain() {
        error_log( "ACL_WC_RFQ_Email: Generating Plain text content for Quote ID: " . $this->placeholders['{quote_id}'] );
        ob_start();

        // Retrieve meta data for the quote
        $quote_meta = get_post_meta( $this->placeholders['{quote_id}'], '', true );

        if ( empty( $quote_meta ) ) {
            error_log("ACL_WC_RFQ_Email: No metadata found for Quote ID: " . $this->placeholders['{quote_id}']);
        }
    
        // Ensure quote_items is properly unserialized
        $quote_items = isset( $quote_meta['_acl_quote_items'][0] ) ? maybe_unserialize( $quote_meta['_acl_quote_items'][0] ) : array();
        error_log("quote_items: $quote_items");

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
}