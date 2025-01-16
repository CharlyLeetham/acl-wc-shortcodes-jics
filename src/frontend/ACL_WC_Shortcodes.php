<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class ACL_WC_Shortcodes {

    public function __construct() {
        add_action('wp_loaded', array($this, 'acl_test_remove'));
        add_shortcode('add_to_cart', array($this, 'acl_add_to_cart'));
        add_action('widgets_init', array($this, 'register_widgets'));  
        add_shortcode('products', array($this, 'acl_products_shortcode'));      
    }

    public function register_widgets() {
        register_widget('ACL_WC_Widget_Products');
    }    

    public function acl_add_to_cart($atts) {
        global $post;

        if (empty($atts)) {
            return '';
        }

        $atts = shortcode_atts(
            array(
                'id'         => '',
                'class'      => '',
                'quantity'   => '1',
                'sku'        => '',
                'style'      => 'border:4px dotted #ccc; padding: 12px;',
                'show_price' => 'true',
            ),
            $atts,
            'product_add_to_cart'
        );

        if (!empty($atts['id'])) {
            $product_data = get_post($atts['id']);
        } elseif (!empty($atts['sku'])) {
            $product_id   = wc_get_product_id_by_sku($atts['sku']);
            $product_data = get_post($product_id);
        } else {
            return '';
        }

        $product = is_object($product_data) && in_array($product_data->post_type, array('product', 'product_variation'), true) ? wc_setup_product_data($product_data) : false;

        if (!$product) {
            return '';
        }

        ob_start();

        echo '<p class="product woocommerce add_to_cart_inline ' . esc_attr($atts['class']) . '" style="' . (empty($atts['style']) ? '' : esc_attr($atts['style'])) . '">';

        if (wc_string_to_bool($atts['show_price'])) {
            echo $product->get_price_html();
            echo 'here';
        }

        $this->woocommerce_template_loop_add_to_cart(array('quantity' => $atts['quantity']));

        echo '</p>';

        wc_setup_product_data($post);

        return ob_get_clean();
    }

    public function woocommerce_template_loop_add_to_cart($args = array()) {
        global $product;
    
        if ($product) {
            $defaults = array(
                'quantity'   => 1,
                'class'      => implode(
                    ' ',
                    array_filter(
                        array(
                            'button',
                            wc_wp_theme_get_element_class_name('button'), // escaped in the template.
                            'product_type_' . $product->get_type(),
                            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                            $product->supports('ajax_add_to_cart') && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
                        )
                    )
                ),
                'attributes' => array(
                    'data-product_id'  => $product->get_id(),
                    'data-product_sku' => $product->get_sku(),
                    'aria-label'       => $product->add_to_cart_description(),
                    'aria-describedby' => $product->add_to_cart_aria_describedby(),
                    'rel'              => 'nofollow',
                ),
            );
    
            $args = apply_filters('woocommerce_loop_add_to_cart_args', wp_parse_args($args, $defaults), $product);
    
            if (!empty($args['attributes']['aria-describedby'])) {
                $args['attributes']['aria-describedby'] = wp_strip_all_tags($args['attributes']['aria-describedby']);
            }
    
            if (isset($args['attributes']['aria-label'])) {
                $args['attributes']['aria-label'] = wp_strip_all_tags($args['attributes']['aria-label']);
            }
    
            wc_get_template('loop/add-to-cart.php', $args);
        }
    }

    public function acl_test_remove() {
        remove_shortcode('add_to_cart');
    }

    public function acl_products_shortcode($atts) {
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

        $products = new WP_Query($args);
        
        ob_start();

        if ($products->have_posts()) {
            echo '<div class="acl-products-shortcode columns-' . esc_attr($atts['columns']) . '">';
            while ($products->have_posts()) : $products->the_post();
                global $product;
                ?>
                <div class="acl-product-item">
                    <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
                        <?php echo $product->get_image(); ?>
                    </a>
                    <div class="acl-product-buy-now">
                        <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="button product_type_<?php echo esc_attr($product->get_type()); ?>"><?php echo esc_html($product->add_to_cart_text()); ?></a>
                    </div>
                    <h3 class="acl-product-title">
                        <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
                            <?php echo wp_kses_post($product->get_name()); ?>
                        </a>
                    </h3>
                    <?php if ($product->is_on_sale()) : ?>
                        <span class="acl-product-sale-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                    <?php else : ?>
                        <span class="acl-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                    <?php endif; ?>
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
}