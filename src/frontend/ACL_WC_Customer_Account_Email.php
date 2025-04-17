<?php
namespace ACLWcShortcodes\ACLWCCustomerAccountEmail;
use ACLWcShortcodes\Helpers\ACL_WC_Helpers;


class ACL_WC_Customer_Account_Email extends \WC_Email {

    public function __construct() {
        $this->id          = 'acl_wc_customer_account_email';
        $this->title       = __( 'Your Quote Request Confirmation', 'woocommerce' );
        $this->description = __( 'This email is sent to the customer when a quote is received.', 'woocommerce' );

        $this->template_html  = 'emails/acl-customer-account-email.php';
        $this->template_plain = 'emails/plain/acl-customer-account-email.php';
        $this->template_base  = ACL_WC_SHORTCODES_PATH . 'src/templates/';

        add_action( 'acl_wc_send_customer_account_email', array( $this, 'trigger' ), 10, 1 );

        parent::__construct();
    }

    public function trigger($email_data) {
        $this->recipient = $email_data['quote_details']['_acl_email'][0] ?? '';
    
        if (!$this->recipient) {
            return;
        }
    
        $this->email_data = $email_data;
    
        $this->send(
            $this->recipient,
            $this->get_subject(),
            $this->get_content(),
            $this->get_headers(),
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
        return apply_filters('woocommerce_email_subject_' . $this->id, $this->get_option('subject', 'Your Quote Request Confirmation'), $this->object);
    }
    
    public function get_heading() {
        return apply_filters('woocommerce_email_heading_' . $this->id, $this->get_option('heading', 'Your Quote Request Confirmation'), $this->object);
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

    public function init_form_fields() {
        $this->form_fields = [

            'enabled' => [
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable this email notification', 'woocommerce'),
                'default' => 'yes'
            ],

            'subject' => [
                'title' => __('Subject', 'woocommerce'),
                'type' => 'text',
                'description' => sprintf(__('Defaults to <code>%s</code>', 'woocommerce'), $this->get_subject()),
                'placeholder' => 'New Quote Request',
                'default' => 'New Quote Request',
            ],
            'heading' => [
                'title' => __('Email Heading', 'woocommerce'),
                'type' => 'text',
                'description' => sprintf(__('Defaults to <code>%s</code>', 'woocommerce'), $this->get_heading()),
                'placeholder' => 'New Quote Request',
                'default' => 'New Quote Request',
            ],
        ];
    }  

    public static function acl_force_html_email_setting() {
        $email_settings = get_option('woocommerce_acl_wc_customer_account_email_settings', []);
        if (!isset($email_settings['email_type']) || $email_settings['email_type'] !== 'html') {
            $email_settings['email_type'] = 'html';
            update_option('woocommerce_acl_wc_customer_account_email_settings', $email_settings);
        }
    }

}