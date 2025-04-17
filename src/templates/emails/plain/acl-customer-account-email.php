<?php
/**
 * Plain Text Email Template for New Customer Account Created
 *
 * @package WooCommerce/Templates/Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Use the quote details passed from the email class
$quote_items = isset( $quote_details['_acl_quote_items'][0] ) ? maybe_unserialize( $quote_details['_acl_quote_items'][0] ) : array();
$email = isset( $quote_details['_acl_email'][0] ) ? $quote_details['_acl_email'][0] : '';
$firstname = isset( $quote_details['_acl_first_name'][0] ) ? $quote_details['_acl_first_name'][0] : '';
$phone = isset( $quote_details['_acl_phone'][0] ) ? $quote_details['_acl_phone'][0] : '';
$address1 = isset( $quote_details['_acl_address1'][0] ) ? $quote_details['_acl_address1'][0] : '';
$address2 = isset( $quote_details['_acl_address2'][0] ) ? $quote_details['_acl_address2'][0] : '';
$suburb = isset( $quote_details['_acl_suburb'][0] ) ? $quote_details['_acl_suburb'][0] : '';
$state = isset( $quote_details['_acl_state'][0] ) ? $quote_details['_acl_state'][0] : '';
$postcode = isset( $quote_details['_acl_postcode'][0] ) ? $quote_details['_acl_postcode'][0] : '';

echo "Hi " . esc_html($firstname) . ",\n\n";
echo "Thanks for requesting a quote from Jet Industrial Supplies. The details of your request are below.\n\n";
echo "To start with, we’ve created an account for you, so you can track your quote.\n\n";
echo "You've requested a quote on the following items. We will put together the quote and be in touch within 5 business days. If you have any queries or questions, please contact us on 08 9523 2909 or email us at sales@jetindustrial.com.au.\n\n";

echo "Quote Request Details:\n";
echo "----------------------\n";
echo "Name: " . esc_html($firstname) . "\n";
echo "Email: " . esc_html($email) . "\n";
echo "Phone: " . esc_html($phone) . "\n";
echo "Address:\n";
echo esc_html($address1) . "\n";
if (!empty($address2)) {
    echo esc_html($address2) . "\n";
}
echo esc_html(trim($suburb . ' ' . $state)) . "\n";
echo "Post Code: " . esc_html($postcode) . "\n\n";

echo "Quote Items:\n";
if (!empty($quote_items)) {
    foreach ( $quote_items as $item ) {
        echo esc_html__( '• ', 'woocommerce' ) . esc_html( $item['name'] ) . ' (' . esc_html( $item['quantity'] ) . ')'; 
        if ( isset( $item['details'] ) && ! empty( $item['details'] ) ) {
            echo esc_html__( ' - Details: ', 'woocommerce' ) . esc_html( $item['details'] );
        }
        echo "\n";
    }
} else {
    echo "No items specified.\n";
}

echo "\n----------------------\n";
echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')) . "\n";
