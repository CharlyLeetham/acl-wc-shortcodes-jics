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
        $email_type = $this->get_option('email_type', 'html');
        switch ($email_type) {
            case 'plain':
                return 'text/plain';
            case 'multipart':
                return 'multipart/mixed';
            case 'html':
            default:
                return 'text/html';
        }
    }
    
    public function get_headers() {
        $email_type = $this->get_option('email_type', 'html');
        $header = 'Content-Type: ' . $this->set_email_content_type() . "\r\n";
        if ($email_type === 'multipart') {
            $header .= "MIME-Version: 1.0\r\n";
        }
        return $header;
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
                'description' => sprintf(__('Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce'), esc_attr(get_option('admin_email'))),
                'placeholder' => get_option('admin_email'),
                'default' => get_option('admin_email'),
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

            'email_type' => [
                'title' => __('Email type', 'woocommerce'),
                'type' => 'select',
                'description' => __('Choose which format of email to send.', 'woocommerce'),
                'default' => 'html',
                'class' => 'email_type wc-enhanced-select',
                'options' => [
                    'plain' => __('Plain text', 'woocommerce'),
                    'html' => __('HTML', 'woocommerce'),
                    'multipart' => __('Multipart', 'woocommerce'),
                ],
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