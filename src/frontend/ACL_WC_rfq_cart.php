<?php
namespace ACLWcShortcodes\ACLWCRFQCart;
use ACLWcShortcodes\Helpers\ACL_WC_Helpers;

/**
 * Class ACL_WC_RFQ_cart
 * Handles the functionality for managing RFQ carts in WooCommerce.
 */
class ACL_WC_RFQ_cart {

    /**
     * Initialize the quote cart in the session.
     */
    public static function acl_start_quote_cart() {
  
        // Ensure WooCommerce session is initialized
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }
    
        // Retrieve session ID (Customer ID for guests, User ID for logged-in users)
        $session_id = WC()->session->get_customer_id();
    
        // Retrieve or initialize the RFQ cart
        $quote_cart = WC()->session->get('quote_cart', array());
        if (!is_array($quote_cart)) {
            $quote_cart = array();
        }
    
        // Restore RFQ Cart for logged-in users
        if (is_user_logged_in()) {
            $user_id  = get_current_user_id();
            $blog_id  = get_current_blog_id();
            $meta_key = '_acl_persistent_rfq_cart_' . $blog_id;
        
            // Check if user has a saved RFQ cart
            $saved_rfq_cart = get_user_meta($user_id, $meta_key, true);
            if (!empty($saved_rfq_cart)) {
                $quote_cart = maybe_unserialize($saved_rfq_cart);
            }
        }
    
        // Store the corrected cart back into session
        WC()->session->set('quote_cart', $quote_cart);
        WC()->session->save_data();
    
        // Ensure session updates for guest users
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}woocommerce_sessions SET session_value = %s WHERE session_key = %s",
                maybe_serialize(array_merge(WC()->session->get_session_data(), ['quote_cart' => $quote_cart])),
                $session_id
            )
        );
    
        // Persist RFQ cart in user meta for logged-in users
        if (is_user_logged_in() && apply_filters('woocommerce_persistent_cart_enabled', true)) {
            if (!empty($quote_cart)) {
                update_user_meta($user_id, $meta_key, maybe_serialize($quote_cart));
            } else {
                delete_user_meta($user_id, $meta_key);
            }
        }
    }



    /**
     * Add a product to the quote cart.
     *
     * @param int $product_id The ID of the product to add to the quote cart.
     */
    public static function acl_add_to_quote_cart( $product_id ) {
        if ( ! WC()->session instanceof WC_Session ) {
            WC()->initialize_session();
        }
    
        $session_id = is_user_logged_in() ? get_current_user_id() : WC()->session->get_customer_id();
        $product = wc_get_product( $product_id );
    
        if ( $product ) {
            $quote_cart = WC()->session->get( 'quote_cart', array() );
    
            // Add product to RFQ cart
            $quote_cart[ $product_id ] = array(
                'product_id' => $product_id,
                'quantity'   => 1,
                'name'       => $product->get_name(),
                'price'      => $product->get_price()
            );
    
            // ✅ **Always store RFQ cart in WooCommerce session**
            WC()->session->set( 'quote_cart', $quote_cart );
            WC()->session->save_data();
    
    
            // ✅ **Always update WooCommerce sessions table directly (for guest users)**
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}woocommerce_sessions SET session_value = %s WHERE session_key = %s",
                    maybe_serialize( array_merge( WC()->session->get_session_data(), [ 'quote_cart' => $quote_cart ] ) ),
                    $session_id
                )
            );
    
            // ✅ **Only update User Meta if Persistent Cart is enabled**
            if ( is_user_logged_in() && apply_filters( 'woocommerce_persistent_cart_enabled', true ) ) {
                $user_id  = get_current_user_id();
                $blog_id  = get_current_blog_id();
                $meta_key = '_acl_persistent_rfq_cart_' . $blog_id;
    
                if ( ! empty( $quote_cart ) ) {
                    update_user_meta( $user_id, $meta_key, maybe_serialize( $quote_cart ) );
                } else {
                    delete_user_meta( $user_id, $meta_key );
                }
            }
        }
    }
 

    /**
     * Send a quote request email to the shop owner.
     *
     * @param array $customer_data Customer details.
     * @param array $quote_cart    Array of products in the quote cart.
     */
    public static function acl_send_quote_email( $customer_data, $quote_cart ) {
        $to = get_option( 'admin_email' ); // This retrieves the admin email from WordPress settings, which should be the shop owner's email if set correctly in WooCommerce.
        $subject = 'New Quote Request';
        $message = "Customer: " . $customer_data['name'] . "\nEmail: " . $customer_data['email'] . "\n\nProducts:\n";
        foreach ( $quote_cart as $item ) {
            $message .= $item['name'] . ' - Quantity: ' . $item['quantity'] . ' - Price: ' . $item['price'] . "\n";
        }
        wp_mail( $to, $subject, $message );
    }

    /**
     * Convert the quote cart to a WooCommerce order.
     *
     * @param array $quote_cart Array of products in the quote cart.
     * @param array $customer_data Customer details.
     * @return WC_Order The created WooCommerce order object.
     */
    public static function acl_convert_quote_to_order( $quote_cart, $customer_data ) {
        // Create an order object
        $order = wc_create_order();

        // Add products to the order
        foreach ( $quote_cart as $item ) {
            $product = wc_get_product( $item['product_id'] );
            $order_item = $order->add_product( $product, $item['quantity'] );
    
            // Check if product details were provided
            if ( isset( $item['product-deets'] ) && !empty( $item['product-deets'] ) ) {
                wc_add_order_item_meta( $order_item, 'Size & Specs', sanitize_text_field( $item['product-deets'] ) );
            }
        }

        // Set customer details
        $order->set_address( $customer_data, 'billing' );
        $order->set_address( $customer_data, 'shipping' );

        // Save the order
        $order->calculate_totals();
        $order->save();

        return $order;
    }

    /**
     * Generate the mini RFQ cart widget.
     *
     * @return string HTML for the mini RFQ cart widget.
     */
    public static function acl_mini_rfq_cart_widget() {
        if ( !WC()->session instanceof WC_Session ) {
            WC()->initialize_session();
        }
        $session_id = WC()->session->get_customer_id();      
        
        // Get quote cart directly from session since that's where it's stored
        $quote_cart = WC()->session->get('quote_cart', array());
        $cart_url = home_url( '/rfq-cart' );
    
        if ( empty( $quote_cart ) ) {
            return '<div class="acl-mini-rfq-cart"><a href="' . esc_url( $cart_url ) . '">RFQ Cart: 0 items</a></div>';
        }
    
        // Sum the 'quantity' field across all items
        $total_quantity = array_reduce($quote_cart, function($carry, $item) {
            return $carry + (isset($item['quantity']) ? intval($item['quantity']) : 0);
        }, 0);

        // Define the icon (using Font Awesome as an example)
        $icon_html = '<i class="fas fa-shopping-cart"></i>';

        // If there are items, show the count
        $item_count_html = $total_quantity > 0 ? '<span class="rfq-cart-count">' . esc_html( $total_quantity ) . '</span>' : '';

        return '<div class="acl-mini-rfq-cart"><a href="' . esc_url( $cart_url ) . '" title="Request Quote">' . $icon_html . $item_count_html . '</a></div>';        
    }

    public static function acl_rfq_cart_content( ) {
        wc_get_template( 'cart/cart.php', null, '', ACL_WC_SHORTCODES_PATH . 'src/frontend/templates/woocommerce/' );
    }


}