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
    public static function get_modified_button_classes( $product ) {
        $purchase_attribute = $product->get_attribute( 'pa_purchase' );
        $values = array_map( 'trim', explode( ',', $purchase_attribute ) );
        $additional_classes = '';

        foreach ( $values as $value ) {
            if ( $value === 'quote' ) {
                $additional_classes .= ' quote-button';
            }
            if ( $value === 'purchase' ) {
                $additional_classes .= ' purchase-button';
            }
        }
        var_dump ( $additional_classes );

        return esc_attr( $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '' ) . $additional_classes;
    }

    /**
     * Generates add to cart buttons based on product attributes.
     *
     * @param WC_Product $product The product object.
     * @return string HTML for the add to cart buttons.
     */
    public static function generate_add_to_cart_buttons( $product ) {
        $button_classes =  self::get_modified_button_classes( $product );
        $output = '';

        // Split the string into an array for each class
        $classes_array = array_filter(explode(' ', $button_classes[0])); // Assuming button_classes returns one string
        var_dump ($classes_array);
        
        if (empty($classes_array)) {
            // If no specific classes are returned, use the default 'quote-button'
            $classes_array[] = 'quote-button';
        }        
        
        foreach ($classes_array as $class) {
            // Decide what text to use based on the class
            $button_text = $class === 'quote-button' ? 'Get Quote' : $product->add_to_cart_text();
            
            $output .= '<a href="' . esc_url($product->add_to_cart_url()) . '" rel="nofollow" data-product_id="' . esc_attr($product->get_id()) . '" data-product_sku="' . esc_attr($product->get_sku()) . '" class="button ' . esc_attr($class) . ' ajax_add_to_cart" data-quantity="1">';
            $output .= esc_html($button_text);
            $output .= '</a>';
        }

        return $output;
    }
}