<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

We’ve created an account for you, so you can track your quote.

Your username: <?php echo esc_html($customer_email); ?>

To set your password, visit this link:
<?php echo esc_url($password_reset_url); ?>

<?php do_action('woocommerce_email_footer', $email); ?>
