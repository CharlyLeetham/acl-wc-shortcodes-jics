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
                $button_content = '<i class="fa fa-shopping-cart"></i>'; // Assuming Font Awesome for the icon, adjust as needed
            } else {
                // Use text for other buttons
                $button_text = $class === 'quote-button' ? 'Get Quote' : $product->add_to_cart_text();
                $button_content = esc_html( $button_text );
            }
            
            $output .= '<a href="' . esc_url( $product->add_to_cart_url()) . '" rel="nofollow" data-product_id="' . esc_attr($product->get_id() ) . '" data-product_sku="' . esc_attr( $product->get_sku() ) . '" class="button ' . $button_class . ' ajax_add_to_cart" data-quantity="1">';
            $output .= $button_content;
            $output .= '</a>';
        }

        if (empty( $button_classes )) {
            // If no buttons were created, ensure a default 'quote' button is added
            $output .= '<a href="' . esc_url( $product->add_to_cart_url() ) . '" rel="nofollow" data-product_id="' . esc_attr( $product->get_id() ) . '" data-product_sku="' . esc_attr( $product->get_sku() ) . '" class="button quote-button ajax_add_to_cart" data-quantity="1">Get Quote</a>';
        }

        return $output;
    }
}