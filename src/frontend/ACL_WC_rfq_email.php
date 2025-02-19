<?php
namespace ACLWcShortcodes\ACLWCRFQWCEMail;
use ACLWcShortcodes\Helpers\ACL_WC_Helpers;
use WooCommerce;

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
        if ( $quote_id ) {
            $this->object = wc_get_product( $quote_id );
            $this->placeholders['{quote_id}'] = $quote_id;
            $this->send( get_option( 'admin_email' ), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }
    }

    public function get_subject() {
        return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->get_option( 'subject', 'New Quote Request' ), $this->object );
    }

    public function get_content_html() {
        ob_start();
        wc_get_template( $this->template_html, array(
            'quote_id'       => $this->placeholders['{quote_id}'],
            'email_heading'  => $this->get_heading(),
            'sent_to_admin'  => true,
            'plain_text'     => false,
            'email'          => $this,
            'quote_details'  => get_post_meta( $this->placeholders['{quote_id}'] )
        ), '', $this->template_base );
        return ob_get_clean();
    }    

    public function get_content_plain() {
        ob_start();
        wc_get_template( $this->template_plain, array(
            'quote_id'       => $this->placeholders['{quote_id}'],
            'email_heading'  => $this->get_heading(),
            'sent_to_admin'  => true,
            'plain_text'     => true,
            'email'          => $this,
            'quote_details'  => get_post_meta( $this->placeholders['{quote_id}'] )
        ), '', $this->template_base );
        return ob_get_clean();
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'subject' => array(
                'title'       => __( 'Subject', 'woocommerce' ),
                'type'        => 'text',
                'description' => sprintf( __( 'Defaults to <code>%s</code>', 'woocommerce' ), $this->get_default_subject() ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
        );
    }
}

return new ACL_WC_RFQ_Email();