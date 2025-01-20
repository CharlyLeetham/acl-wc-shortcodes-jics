<?php

/**
 * Helper class for ACL WooCommerce Shortcodes.
*/

class ACL_WC_Helpers {
    /**
     * Helper functions for ACL WooCommerce Shortcodes.
     */

    /**
     * Modifies the add to cart button class based on product attributes.
     *
     * @param WC_Product $product The product object.
     * @return string The modified class attribute for the button.
     */
    public static function get_modified_button_classes($product) {
        $purchase_attribute = $product->get_attribute('pa_purchase');
        $values = array_map('trim', explode(',', $purchase_attribute));
        
        $button_classes = [];

        if (empty($values)) {
            // If no attribute values, default to 'quote'
            $button_classes[] = 'quote-button';
        } else {
            foreach ($values as $value) {
                if ($value === 'quote') {
                    $button_classes[] = 'quote-button';
                }
                if ($value === 'purchase') {
                    $button_classes[] = 'purchase-button';
                }
            }
        }

        return $button_classes;
    }

    /**
     * Generates add to cart buttons based on product attributes.
     *
     * @param WC_Product $product The product object.
     * @return string HTML for the add to cart buttons.
     */
    public static function generate_add_to_cart_buttons( $product ) {
        $button_classes = self::get_modified_button_classes( $product );
        $output = '';
        
        foreach ( $button_classes as $class ) {
            $button_class = esc_attr( $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button ' . $class : $class );
            
            if ( $class === 'purchase-button' ) {
                // Use a shopping cart icon for the purchase button
                $button_content = '<svg class="fa-svg" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="shopping-cart" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M528.12 301.319l47.273-208C578.806 78.301 567.391 64 551.99 64H159.208l-9.166-44.81C147.758 8.021 137.93 0 126.529 0H24C10.745 0 0 10.745 0 24v16c0 13.255 10.745 24 24 24h69.883l70.248 343.435C147.325 417.1 136 435.222 136 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-15.674-6.447-29.835-16.824-40h209.647C430.447 426.165 424 440.326 424 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-22.172-12.888-41.332-31.579-50.405l5.517-24.276c3.413-15.018-8.002-29.319-23.403-29.319H218.117l-6.545-32h293.145c11.206 0 20.92-7.754 23.403-18.681z"></path></svg>';
            } else {
                // Use text for other buttons
                $button_text = $class === 'quote-button' ? 'Get Quote' : $product->add_to_cart_text();
                $button_content = esc_html( $button_text );
            }
            
            $output .= '<div class="'.$button_class.'">';
            $output .= '<a href="' . esc_url( $product->add_to_cart_url()) . '" rel="nofollow" data-product_id="' . esc_attr($product->get_id() ) . '" data-product_sku="' . esc_attr( $product->get_sku() ) . '" class="button ' . $button_class . ' ajax_add_to_cart" data-quantity="1">';
            $output .= $button_content;
            $output .= '</a>';
            $output .= '</div>';
        }

        if (empty( $button_classes )) {
            // If no buttons were created, ensure a default 'quote' button is added
            $output .= '<div class="quote-button">';
            $output .= '<a href="' . esc_url( $product->add_to_cart_url() ) . '" rel="nofollow" data-product_id="' . esc_attr( $product->get_id() ) . '" data-product_sku="' . esc_attr( $product->get_sku() ) . '" class="button quote-button ajax_add_to_cart" data-quantity="1">Get Quote</a>';
            $output .= '</div>';
        }

        return $output;
    }

        /**
     * Custom function to display subcategory thumbnail.
     *
     * @param WP_Term $category Category object.
     */
    public static function acl_woocommerce_subcategory_thumbnail( $category ) {
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