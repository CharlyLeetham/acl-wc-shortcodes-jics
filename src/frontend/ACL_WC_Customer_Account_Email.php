<?php
namespace ACLWcShortcodes\ACLWCCustomerAccountEmail;
use ACLWcShortcodes\Helpers\ACL_WC_Helpers;


class ACL_WC_Customer_Account_Email extends \WC_Email {

    public function __construct() {
        $this->id          = 'acl_wc_customer_account_email';
        $this->title       = __( 'Your Quote Request Confirmation', 'woocommerce' );
        $this->description = __( 'This email is sent when a new account is created for an RFQ submission.', 'woocommerce' );

        $this->template_html  = 'emails/acl-customer-account-email.php';
        $this->template_plain = 'emails/plain/acl-customer-account-email.php';
        $this->template_base  = ACL_WC_SHORTCODES_PATH . 'src/templates/';

        add_action( 'acl_wc_send_customer_account_email', array( $this, 'trigger' ), 10, 1 );

        parent::__construct();
    }

    public function trigger( $user_id, $password_reset_url ) {
        $this->recipient = get_userdata($user_id)->user_email;

        if (! $this->recipient) {
            return;
        }

        $this->placeholders['{customer_email}'] = $this->recipient;
        $this->placeholders['{password_reset_url}'] = $password_reset_url;

        $this->send($this->recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'quote_details' => $this->email_data['quote_details'] ?? [],
                'quote_items' => $this->email_data['quote_items'] ?? [],
                'customer_name' => $this->email_data['customer_name'] ?? '',
                'address' => $this->email_data['address'] ?? '',
                'email_heading'  => $this->get_heading(),
                'sent_to_admin'  => false,
                'plain_text'     => false,
                'email'          => $this
            ),
            '',
            $this->template_base
        );
    }

    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'quote_details' => $this->email_data['quote_details'] ?? [],
                'quote_items' => $this->email_data['quote_items'] ?? [],
                'customer_name' => $this->email_data['customer_name'] ?? '',
                'address' => $this->email_data['address'] ?? '',
                'email_heading'  => $this->get_heading(),
                'sent_to_admin'  => false,
                'plain_text'     => true,
                'email'          => $this
            ),
            '',
            $this->template_base
        );
    }
}