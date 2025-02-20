<?php
/**
 * Email Template for New Customer Account Created
 *
 * @package WooCommerce/Templates/Emails
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<p><?php esc_html_e('We’ve created an account for you, so you can track your quote.', 'woocommerce'); ?></p>
<p><strong><?php esc_html_e('Your username:', 'woocommerce'); ?></strong> <?php echo esc_html($customer_email); ?></p>
<p><?php esc_html_e('To set your password, visit this link:', 'woocommerce'); ?></p>
<p><a href="<?php echo esc_url($password_reset_url); ?>"><?php esc_html_e('Reset Password', 'woocommerce'); ?></a></p>

<?php do_action('woocommerce_email_footer', $email); ?>
