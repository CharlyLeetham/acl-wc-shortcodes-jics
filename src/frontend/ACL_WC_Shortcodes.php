<?php

namespace ACLWcShortcodes\ACLWCShortcodes;
use ACLWcShortcodes\ACLWCRFQCart\ACL_WC_RFQ_cart;
use ACLWcShortcodes\Helpers\ACL_WC_Helpers;

class ACL_WC_Shortcodes {
    public function __construct() {
        // Constructor can be used for setting up hooks or other initialization tasks
    }

    public static function acl_products_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'on_sale' => 'false',
                'limit'   => 12,
                'columns' => 4,
            ),
            $atts,
            'products'
        );

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $atts['limit'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if ($atts['on_sale'] === 'true') {
            $product_ids_on_sale = wc_get_product_ids_on_sale();
            $args['post__in'] = $product_ids_on_sale;
        }

        $products = new \WP_Query($args);
        
        ob_start();

        if ($products->have_posts()) {
            echo '<div class="acl-products-shortcode columns-' . esc_attr($atts['columns']) . '">';
            while ($products->have_posts()) : $products->the_post();
                global $product;
                ?>
                <div class="acl-product-item">
                    <div class="acl-product-image">
                        <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
                            <?php echo $product->get_image(); ?>
                         </a>
                    </div>
                    <div class="acl-product-buy-now">
                        <?php
                            echo ACL_WC_Helpers::generate_add_to_cart_buttons($product);
                        ?>
                    </div>
                    <div class="acl-product-details">
                        <div class="clipped-content">
                            <h3 class="acl-product-title">
                                <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
                                    <?php echo wp_kses_post($product->get_name()); ?>
                                </a>
                            </h3>
                            <?php if ($product->is_on_sale()) { ?>
                                <span class="acl-product-sale-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                            <?php } else { ?>
                                <span class="acl-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php
            endwhile;
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>' . esc_html__('No products found.', 'acl-wc-shortcodes') . '</p>';
        }

        return ob_get_clean();
    } 
    
    public static function acl_mini_rfq_cart_shortcode() {
        // Since acl_mini_rfq_cart_widget is static in ACL_WC_RFQ_cart, we can call it without instantiation
        return ACL_WC_RFQ_cart::acl_mini_rfq_cart_widget();
    }    

    public static function acl_rfq_cart_shortcode( $atts ) {
        ob_start();
        ACL_WC_Helpers::acl_rfq_cart_content(); // This should be the function that builds your RFQ cart
        return ob_get_clean();
    }
}