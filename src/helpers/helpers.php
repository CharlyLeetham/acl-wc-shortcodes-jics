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


	public static function acl_woocommerce_template_loop_category_link_open( $category ) {
		$category_term = get_term( $category, 'product_cat' );
		$category_name = ( ! $category_term || is_wp_error( $category_term ) ) ? '' : $category_term->name;
		/* translators: %s: Category name */
		echo '<div class="acl-category-thumbnail"><a aria-label="' . sprintf( esc_attr__( 'Visit product category %1$s', 'woocommerce' ), esc_attr( $category_name ) ) . '" href="' . esc_url( get_term_link( $category, 'product_cat' ) ) . '">';
	} 
    
    public static function acl_woocommerce_template_loop_product_link_open() {
        global $product;

        $link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );

        echo '<a href="' . esc_url( $link ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
    }

        /**
     * Custom function to display subcategory.
     *
     * @param WP_Term $category Category object.
     */
	public static function acl_woocommerce_subcategory_thumbnail( $category ) {
		$small_thumbnail_size = apply_filters( 'subcategory_archive_thumbnail_size', 'woocommerce_thumbnail' );
		$dimensions           = wc_get_image_size( $small_thumbnail_size );
		$thumbnail_id         = get_term_meta( $category->term_id, 'thumbnail_id', true );

		if ( $thumbnail_id ) {
			$image        = wp_get_attachment_image_src( $thumbnail_id, $small_thumbnail_size );
			$image        = $image[0];
			$image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $thumbnail_id, $small_thumbnail_size ) : false;
			$image_sizes  = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $thumbnail_id, $small_thumbnail_size ) : false;
		} else {
			$image        = wc_placeholder_img_src();
			$image_srcset = false;
			$image_sizes  = false;
		}

		if ( $image ) {
			// Prevent esc_url from breaking spaces in urls for image embeds.
			// Ref: https://core.trac.wordpress.org/ticket/23605.
			$image = str_replace( ' ', '%20', $image );

			// Add responsive image markup if available.
			if ( $image_srcset && $image_sizes ) {
				echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $category->name ) . '" width="' . esc_attr( $dimensions['width'] ) . '" height="' . esc_attr( $dimensions['height'] ) . '" srcset="' . esc_attr( $image_srcset ) . '" sizes="' . esc_attr( $image_sizes ) . '" /></div>';
			} else {
				echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $category->name ) . '" width="' . esc_attr( $dimensions['width'] ) . '" height="' . esc_attr( $dimensions['height'] ) . '" /></div>';
			}
		}
        echo '</a>';
	}

    	/**
	 * Show the subcategory title in the product loop.
	 *
	 * @param object $category Category object.
	 */
	public static function acl_woocommerce_template_loop_category_title( $category ) {
		?>
        <div class="acl-category-title">
            <h2 class="woocommerce-loop-category__title">
                <?php
                echo '<a aria-label="' . sprintf( esc_attr__( 'Visit product category %1$s', 'woocommerce' ), esc_attr( $category_name ) ) . '" href="' . esc_url( get_term_link( $category, 'product_cat' ) ) . '">';                
                echo esc_html( $category->name );

                if ( $category->count > 0 ) {
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo apply_filters( 'woocommerce_subcategory_count_html', ' <mark class="count">(' . esc_html( $category->count ) . ')</mark>', $category );
                }
                echo '</a>';
                ?>
            </h2>
        </div>
        <div class="acl-category-readmore thrv_text_element">
            <?php
            echo '<a aria-label="' . sprintf( esc_attr__( 'Visit product category %1$s', 'woocommerce' ), esc_attr( $category_name ) ) . '" href="' . esc_url( get_term_link( $category, 'product_cat' ) ) . '">';
            echo "View ". esc_html( $category->name ). " Catalog ->";
            echo '</a>';
            ?>
        </div>
		<?php
	}

    public static function acl_custom_product_buttons() {
        global $product;

        // Get the attribute 'Purchase'
        $purchase_attribute = $product->get_attribute( 'pa_purchase' ); // Assuming your attribute taxonomy is 'pa_purchase'

        echo '<div class="acl-single-product-custom-buttons" style="display: flex; flex-wrap: nowrap; justify-content: flex-start; align-items: center;">';

        // Show "Buy Now" button if purchase attribute contains 'purchase'
        if ( strpos( $purchase_attribute, 'purchase' ) !== false ) {
            echo '<div class="acl-single-product-button-wrapper">';
            woocommerce_template_single_add_to_cart();
            echo '</div>';
        }
        
        // Show "Get Quote" button if purchase attribute contains 'quote'
        if ( strpos( $purchase_attribute, 'quote' ) !== false ) {
            echo '<div class="acl-single-product-button-wrapper">';
            echo '<a href="' . esc_url( get_permalink( $product->get_id() ) . '?action=quote' ) . '" class="button quote-button">Get Quote</a>';
            echo '</div>';
        }
        
        echo '</div>';

        }
}