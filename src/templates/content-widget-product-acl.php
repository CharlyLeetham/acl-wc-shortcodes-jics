<?php
/**
 * The template for displaying product widget entries in the ACL Products widget.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

global $product;

?>
<li>
    <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="acl-product-widget-image">
        <?php echo $product->get_image(); ?>
    </a>
    <div class="acl-product-widget-buy-now">
        <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="button product_type_<?php echo esc_attr( $product->get_type() ); ?>"><?php echo esc_html( $product->add_to_cart_text() ); ?></a>
    </div>
    <h3 class="acl-product-widget-title">
        <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
            <?php echo wp_kses_post( $product->get_name() ); ?>
        </a>
    </h3>
    <?php if ( $product->is_on_sale() ) : ?>
        <span class="acl-product-widget-sale-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
    <?php else : ?>
        <span class="acl-product-widget-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
    <?php endif; ?>
</li>