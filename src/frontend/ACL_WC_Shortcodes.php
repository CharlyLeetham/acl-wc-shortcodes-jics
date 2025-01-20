<?php
class ACL_WC_Shortcodes {
    public function __construct() {
        
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

        $products = new WP_Query($args);
        
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
                            <?php if ($product->is_on_sale()) : ?>
                                <span class="acl-product-sale-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                            <?php else : ?>
                                <span class="acl-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                            <?php endif; ?>
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
    
    function acl_woocommerce_subcategory_thumbnail( $category ) {
        $small_thumbnail_size = apply_filters( 'subcategory_archive_thumbnail_size', 'woocommerce_thumbnail' );
        $dimensions           = wc_get_image_size( $small_thumbnail_size );
        $thumbnail_id         = get_term_meta( $category->term_id, 'thumbnail_id', true );
    
        if ( $thumbnail_id ) {
            $image        = wp_get_attachment_image( $thumbnail_id, $small_thumbnail_size );
            $image_src    = wp_get_attachment_image_src( $thumbnail_id, $small_thumbnail_size );
            $image_width  = $dimensions['width'];
            $image_height = $dimensions['height'];
        } else {
            $image        = wc_placeholder_img( $small_thumbnail_size );
            $image_src    = wc_placeholder_img_src( $small_thumbnail_size );
            $image_width  = $image_height = 1; // Prevent division by zero.
        }
    
        if ( $image ) {
            // Prevent esc_url from breaking spaces in urls for image embeds.
            // Ref: https://core.trac.wordpress.org/ticket/23605.
            $image = str_replace( ' ', '%20', $image );
    
            ?>
            <div class="acl-category-thumbnail">
                <a href="<?php echo esc_url( get_term_link( $category, 'product_cat' ) ); ?>">
                    <?php
                    echo wp_kses_post( apply_filters( 'woocommerce_subcategory_thumbnail', $image, $category ) );
                    ?>
                </a>
            </div>
            <?php
        }
    }    
}