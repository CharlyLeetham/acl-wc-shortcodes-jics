<?php
/**
 * Email Template for Customer RFQ Email
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
$name = isset( $quote_details['_acl_first_name'][0] ) && isset( $quote_details['_acl_last_name'][0]) ? $quote_details['_acl_first_name'][0] . ' ' . $quote_details['_acl_last_name'][0] : '';
$phone = isset( $quote_details['_acl_phone'][0] ) ? $quote_details['_acl_phone'][0] : '';
$address1 = isset( $quote_details['_acl_address1'][0] ) ? $quote_details['_acl_address1'][0] : '';
$address2 = isset( $quote_details['_acl_address2'][0] ) ? $quote_details['_acl_address2'][0] : '';
$suburb = isset( $quote_details['_acl_suburb'][0] ) ? $quote_details['_acl_suburb'][0] : '';
$state = isset( $quote_details['_acl_state'][0] ) ? $quote_details['_acl_state'][0] : '';
$postcode = isset( $quote_details['_acl_postcode'][0] ) ? $quote_details['_acl_postcode'][0] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?> - <?php echo esc_html( $email_heading ); ?></title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            min-width: 100%!important;
        }
        img {
            height: auto;
            max-width: 100%;
        }
        .content {
            width: 100%;
            max-width: 600px;
        }
        .header {
            background: #f7f7f7;
            padding: 20px;
        }
        .main {
            background: #ffffff;
            padding: 20px;
        }
        .footer {
            background: #f7f7f7;
            padding: 20px;
        }
        .aligncenter {
            text-align: center;
        }
        .alignright {
            text-align: right;
        }
        .alignleft {
            text-align: left;
        }
    </style>
</head>
<body dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" style="margin: 0; padding: 0; width: 100%;">

<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
        <tr>
            <td align="center" valign="top" style="padding: 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td class="main" style="padding: 20px">
                            <p> <?php esc_html_e('Hi ', 'woocommerce'); ?> <?php echo esc_html($firstname); ?><?php esc_html_e(',', 'woocommerce'); ?></p>
                            <p> <?php esc_html_e('Thanks for requesting a quote from Jet Industrial Supplies.  The details of your request are below.', 'woocommerce'); ?></p>
                            <p><?php esc_html_e("You've requested a quote on the following items. We will put together the quote and be in touch within 5 business days.  If you have any queries or questions, please contact us on 08 9523 2909 or email us at sales@jetindustrialcom.au", 'woocommerce'); ?></p>
                        </td>
                    </tr>
                    <!-- Header -->
                    <tr>
                        <td class="header" style="padding: 20px;">
                            <h1><?php echo esc_html( $email_heading ); ?></h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td class="main" style="padding: 20px;">
                            <p><?php esc_html_e( 'A new quote request has been submitted:', 'woocommerce' ); ?></p>
                            <table>
                                <tr>
                                    <td><strong><?php esc_html_e( 'Name:', 'woocommerce' ); ?></strong></td>
                                    <td><?php echo esc_html( $name ); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php esc_html_e( 'Email:', 'woocommerce' ); ?></strong></td>
                                    <td><?php echo esc_html( $email ); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php esc_html_e( 'Phone:', 'woocommerce' ); ?></strong></td>
                                    <td><?php echo esc_html( $phone ); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php esc_html_e( 'Address:', 'woocommerce' ); ?></strong></td>
                                    <td>
                                        <?php echo esc_html( $address1 ); ?><br>
                                        <?php echo ! empty( $address2 ) ? esc_html( $address2 ) . '<br>' : ''; ?>
                                        <?php echo esc_html( trim( $suburb . ' ' . $state ) ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php esc_html_e( 'Post Code:', 'woocommerce' ); ?></strong></td>
                                    <td><?php echo esc_html( $postcode ); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php esc_html_e( 'Quote Items:', 'woocommerce' ); ?></strong></td>
                                    <td>
                                    <?php if ( ! empty( $quote_items ) ) { ?>
                                        <ul>
                                            <?php foreach ( $quote_items as $item ) : ?>
                                                <li><?php 
                                                    echo esc_html( $item['name'] ) . ' (' . esc_html( $item['quantity'] ) . ')'; 
                                                    if ( isset( $item['details'] ) && ! empty( $item['details'] ) ) {
                                                        echo ' - Details: ' . esc_html( $item['details'] );
                                                    }
                                                    ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php } else { ?>
                                        <p><?php echo esc_html__( 'No items specified.', 'woocommerce' ); ?></p>
                                    <?php } ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td class="footer" style="padding: 20px;">
                            <p><?php echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ); ?></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
