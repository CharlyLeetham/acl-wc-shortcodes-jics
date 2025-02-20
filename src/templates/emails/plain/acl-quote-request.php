<?php
/**
 * Plain Text Email Template for ACL Quote Request
 *
 * @package WooCommerce/Templates/Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
error_log( "ACL_WC_RFQ_Email: Plain text template: " );
// Use the quote details passed from the email class
$quote_items = isset( $quote_details['_acl_quote_items'][0] ) ? maybe_unserialize( $quote_details['_acl_quote_items'][0] ) : array();
error_log("quote_items: $quote_items");
$email = isset( $quote_details['_acl_email'][0] ) ? $quote_details['_acl_email'][0] : '';
$name = isset( $quote_details['_acl_first_name'][0] ) && isset( $quote_details['_acl_last_name'][0]) ? $quote_details['_acl_first_name'][0] . ' ' . $quote_details['_acl_last_name'][0] : '';
$phone = isset( $quote_details['_acl_phone'][0] ) ? $quote_details['_acl_phone'][0] : '';
$address1 = isset( $quote_details['_acl_address1'][0] ) ? $quote_details['_acl_address1'][0] : '';
$address2 = isset( $quote_details['_acl_address2'][0] ) ? $quote_details['_acl_address2'][0] : '';
$suburb = isset( $quote_details['_acl_suburb'][0] ) ? $quote_details['_acl_suburb'][0] : '';
$state = isset( $quote_details['_acl_state'][0] ) ? $quote_details['_acl_state'][0] : '';
$postcode = isset( $quote_details['_acl_postcode'][0] ) ? $quote_details['_acl_postcode'][0] : '';

echo esc_html( $email_heading ) . "\n\n";
echo esc_html__( 'A new quote request has been submitted:', 'woocommerce' ) . "\n\n";

echo esc_html__( 'Name:', 'woocommerce' ) . ' ' . esc_html( $name ) . "\n";
echo esc_html__( 'Email:', 'woocommerce' ) . ' ' . esc_html( $email ) . "\n";
echo esc_html__( 'Phone:', 'woocommerce' ) . ' ' . esc_html( $phone ) . "\n";
echo esc_html__( 'Address:', 'woocommerce' ) . "\n";
echo esc_html( $address1 ) . "\n";
echo esc_html__( 'Address:', 'woocommerce' ) . "\n";
echo esc_html( $address1 ) . "\n";
if ( ! empty( $address2 ) ) {
    echo esc_html( $address2 ) . "\n";
}
echo esc_html( trim( $suburb . ' ' . $state ) ) . "\n";
echo esc_html( $suburb . ' ' . $state ) . "\n";
echo esc_html__( 'Post Code:', 'woocommerce' ) . ' ' . esc_html( $postcode ) . "\n";
echo esc_html__( 'Quote Items:', 'woocommerce' ) . "\n";

if ( ! empty( $quote_items ) && is_array( $quote_items ) ) {
    foreach ( $quote_items as $item ) {
        echo esc_html__( '• ', 'woocommerce' ) . esc_html( $item['name'] ) . ' (' . esc_html( $item['quantity'] ) . ")\n";
    }
} else {
    echo esc_html__( 'No items specified.', 'woocommerce' ) . "\n";
}

echo "\n\n------------------------\n\n";
echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );