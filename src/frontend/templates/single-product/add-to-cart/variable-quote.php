<?php
/**
 * Variable product add to quote
 *
 * Customized for Get Quote button
 */
defined('ABSPATH') || exit;

global $product;

$attribute_keys  = array_keys($attributes);
$variations_json = wp_json_encode($available_variations);
$variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

do_action('woocommerce_before_add_to_cart_form'); ?>

<form class="variations_form cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>" data-product_variations="<?php echo $variations_attr; ?>">
    <?php do_action('woocommerce_before_variations_form'); ?>

    <?php if (empty($available_variations) && false !== $available_variations) : ?>
        <p class="stock out-of-stock"><?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?></p>
    <?php else : ?>
        <table class="variations" role="presentation">
            <tbody>
                <?php foreach ($attributes as $attribute_name => $options) : ?>
                    <tr>
                        <th class="label"><label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>"><?php echo wc_attribute_label($attribute_name); ?></label></th>
                        <td class="value">
                            <?php
                            wc_dropdown_variation_attribute_options(
                                array(
                                    'options'   => $options,
                                    'attribute' => $attribute_name,
                                    'product'   => $product,
                                )
                            );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php
                            echo end($attribute_keys) === $attribute_name ? wp_kses_post(apply_filters('woocommerce_reset_variations_link', '<button class="reset_variations" aria-label="' . esc_html__('Clear options', 'woocommerce') . '">' . esc_html__('Clear', 'woocommerce') . '</button>')) : '';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
        <?php do_action('woocommerce_after_variations_table'); ?>

        <div class="single_variation_wrap">
            <?php do_action('woocommerce_before_single_variation'); ?>

            <div class="woocommerce-variation single_variation"></div>
            <div class="woocommerce-variation-add-to-cart variations_button">
                <a href="#" data-product-id="<?php echo esc_attr($product->get_id()); ?>" class="button quote-button">Get Quote</a>
                <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
                <input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
                <input type="hidden" name="variation_id" class="variation_id" value="0" />
            </div>

            <?php do_action('woocommerce_after_single_variation'); ?>
        </div>
    <?php endif; ?>

    <?php do_action('woocommerce_after_variations_form'); ?>
</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>