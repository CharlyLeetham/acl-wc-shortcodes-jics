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
        error_log ('HERE$$$$');
        if ( ! isset( WC()->session ) || ! WC()->session instanceof WC_Session ) {
            WC()->initialize_session();
        }
        
        $session_id = is_user_logged_in() ? get_current_user_id() : WC()->session->get_customer_id();
        
        // Ensure session exists and retrieves correctly
        $quote_cart = WC()->session->get( 'quote_cart', array() );
    
        // If quote_cart is somehow not an array, reset it
        if ( ! is_array( $quote_cart ) ) {
            $quote_cart = array();
        }
    
        // Store the corrected cart back into session
        WC()->session->set( 'quote_cart', $quote_cart );
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
    
            // ✅ **Log for debugging**
            error_log( 'RFQ Cart: Session data after update: ' . print_r( WC()->session->get( 'quote_cart' ), true ) );
    
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
                    error_log( 'RFQ Cart: Saving updated cart to user meta for user: ' . $user_id );
                    update_user_meta( $user_id, $meta_key, maybe_serialize( $quote_cart ) );
                } else {
                    error_log( 'RFQ Cart: RFQ cart is empty, deleting from user meta for user: ' . $user_id );
                    delete_user_meta( $user_id, $meta_key );
                }
            }
        }
    }

    public static function acl_delayed_rfq_cart_restore() {

        if ( ! is_user_logged_in() ) {
            return; // Ensure only logged-in users get their RFQ cart restored
        }
    
        // Delay execution until WooCommerce session is available
        add_action( 'woocommerce_init', array( 'ACLWcShortcodes\ACLWCRFQCart\ACL_WC_RFQ_cart', 'acl_restore_rfq_login' ), 20 );
    }

    public static function acl_restore_rfq_login() {

        error_log( 'Did this run' );
        if ( ! is_user_logged_in() ) {
            return; // Only restore for logged-in users.
        }
    
        $user_id  = get_current_user_id();
        $blog_id  = get_current_blog_id();
        $meta_key = '_acl_persistent_rfq_cart_' . $blog_id;
    
        error_log( 'RFQ Cart: Checking for saved RFQ cart for user: ' . $user_id );
    
        // Ensure WooCommerce session is initialized
        if ( ! isset( WC()->session ) || ! WC()->session instanceof WC_Session ) {
            error_log( 'RFQ Cart: WooCommerce session not available, attempting to initialize. 1' );
            WC()->initialize_session();
        }
   
        if ( ! isset( WC()->session ) || ! WC()->session instanceof WC_Session ) {
            error_log( 'RFQ Cart: WooCommerce session still unavailable after initialization. 2' );
            return;
        }
    
        // Check if we should merge a saved RFQ cart
        $saved_rfq_cart = get_user_meta( $user_id, '_acl_persistent_rfq_cart_' . $blog_id, true );

        if ( ! empty( $saved_rfq_cart ) ) {
            WC()->session->set( 'quote_cart', maybe_unserialize( $saved_rfq_cart ) );
            error_log( 'RFQ Cart: Successfully restored for user: ' . $user_id );
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
            $order->add_product( wc_get_product( $item['product_id'] ), $item['quantity'] );
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
    
        $count = count( $quote_cart );
        return '<div class="acl-mini-rfq-cart"><a href="' . esc_url( $cart_url ) . '">RFQ Cart: ' . esc_html( $count ) . ' item(s)</a></div>';
    }

    public static function acl_rfq_cart_content( ) {
        wc_get_template( 'cart/cart.php', null, '', ACL_WC_SHORTCODES_PATH . 'src/frontend/templates/woocommerce/' );
    }


}