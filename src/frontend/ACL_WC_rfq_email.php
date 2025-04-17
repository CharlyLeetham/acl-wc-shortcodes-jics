<?php
namespace ACLWcShortcodes\ACLWCRFQWCEMail;
use ACLWcShortcodes\Helpers\ACL_WC_Helpers;

class ACL_WC_RFQ_Email extends \WC_Email {

    public function __construct() {
        $this->id = 'acl_quote_email';
        $this->customer_email = false;
        $this->title = __('Shop Owner Quote Request', 'woocommerce');
        $this->description = __('This email is sent to the shop owner when a new quote request is submitted.', 'woocommerce');

        $this->email_type = 'html';
        $this->template_html = 'emails/acl-quote-request.php';
        $this->template_plain = 'emails/plain/acl-quote-request.php';
        $this->template_base = ACL_WC_SHORTCODES_PATH . 'src/templates/';
        $this->placeholders = ['{quote_id}' => ''];

        add_action('acl_quote_request_created', [$this, 'trigger'], 10, 2); // Changed to 2 args

        parent::__construct();
    }

    public function trigger($quote_id, $email_data = []) {
        $this->recipient = get_option('admin_email');

        if (!$this->recipient) {
            return;
        }

        $this->email_data = $email_data;
        $this->placeholders['{quote_id}'] = $quote_id;

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
        ob_start();

        $quote_details = $this->email_data['quote_details'] ?? [];
        $quote_items = $this->email_data['quote_items'] ?? [];
        $password_message = $this->email_data['password_message'] ?? '';
        $customer_name = $this->email_data['customer_name'] ?? '';
        $address = $this->email_data['address'] ?? '';

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

        $quote_details = $this->email_data['quote_details'] ?? [];
        $quote_items = $this->email_data['quote_items'] ?? [];
        $password_message = $this->email_data['password_message'] ?? '';
        $customer_name = $this->email_data['customer_name'] ?? '';
        $address = $this->email_data['address'] ?? '';

        wc_get_template(
            $this->template_plain,
            [
                'quote_id' => $this->placeholders['{quote_id}'],
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text' => true,
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

    public function init_form_fields() {
        $this->form_fields = [

            'enabled' => [
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable this email notification', 'woocommerce'),
                'default' => 'yes'
            ],

            'recipient' => [
                'title' => __('Recipient', 'woocommerce'),
                'type' => 'text',
                'description' => __('Enter the recipient email for this notification. Defaults to the customer’s email.', 'woocommerce'),
                'placeholder' => '',
                'default' => '',
            ],

            'subject' => [
                'title' => __('Subject', 'woocommerce'),
                'type' => 'text',
                'description' => sprintf(__('Defaults to <code>%s</code>', 'woocommerce'), $this->get_subject()),
                'placeholder' => 'Your Quote Request Confirmation',
                'default' => 'Your Quote Request Confirmation',
            ],

            'heading' => [
                'title' => __('Email Heading', 'woocommerce'),
                'type' => 'text',
                'description' => sprintf(__('Defaults to <code>%s</code>', 'woocommerce'), $this->get_heading()),
                'placeholder' => 'Your Quote Request Confirmation',
                'default' => 'Your Quote Request Confirmation',
            ],
        ];
    }

    public static function acl_force_html_email_setting() {
        $email_settings = get_option('woocommerce_acl_quote_email_settings', []);
        if (!isset($email_settings['email_type']) || $email_settings['email_type'] !== 'html') {
            $email_settings['email_type'] = 'html';
            update_option('woocommerce_acl_quote_email_settings', $email_settings);
        }
    }
}