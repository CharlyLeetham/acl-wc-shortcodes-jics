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
        error_log('acl_start_quote_cart called');
        if ( ! isset( WC()->session ) || ! WC()->session instanceof WC_Session ) {
            WC()->initialize_session();
        }

        WC()->session->set('quote_cart', array()); // Initialize quote_cart in session
        error_log('Session exists: ' . var_export(isset(WC()->session), true));
    }


    /**
     * Add a product to the quote cart.
     *
     * @param int $product_id The ID of the product to add to the quote cart.
     */
    public static function acl_add_to_quote_cart( $product_id ) {
        error_log( 'Adding product ID: ' . $product_id . ' to quote cart' );
        $product = wc_get_product( $product_id );
        if ( $product ) {
            //error_log('Product Found: ' . $product->get_name());
            $current_quote_cart = WC()->session->get('quote_cart', array());
            $current_quote_cart[] = array(
                'product_id' => $product_id,
                'quantity'   => 1,
                'name'       => $product->get_name(),
                'price'      => $product->get_price()
            );
            WC()->session->set('quote_cart', $current_quote_cart); // Set the updated cart
            WC()->session->save_data(); // Ensure session data is saved
            error_log( 'Quote Cart After Save: ' . var_export(WC()->session->get('quote_cart'), true) );
        } else {
            //error_log('Product Not Found for ID: ' . $product_id);
        }
        //error_log( 'After Adding - Quote Cart Content: ' . var_export( WC()->session->get('quote_cart'), true ) );
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
    public static function acl_mini_rfq_cart_widget( ) {
        if ( ! WC()->session instanceof WC_Session ) {
            WC()->initialize_session( );
        }
        WC()->session->_dirty = true; 
        WC()->session->save_data( );  
        $quote_cart = WC()->session->get( 'quote_cart', array( ) );
        //error_log( 'Quote Cart in Widget After Save: ' . var_export( $quote_cart, true ) );
    
        $session_data = WC()->session->_data;
        //error_log( 'Session Data: ' . var_export( $session_data, true ) );
        if ( isset( $session_data['quote_cart'] ) ) {
            $quote_cart = $session_data['quote_cart'];
        } else {
            $quote_cart = array( );
        }
        //error_log( 'Quote Cart from Session Data: ' . var_export( $quote_cart, true ) );        
        //error_log( 'Mini Cart - Quote Cart Content: ' . var_export( $quote_cart, true ) );
        
        $cart_url = home_url( '/rfq-cart' );
    
        if ( empty( $quote_cart ) ) {
            return '<div class="acl-mini-rfq-cart"><a href="' . esc_url( $cart_url ) . '">RFQ Cart: 0 items</a></div>';
        }
    
        $count = count( $quote_cart );
        return '<div class="acl-mini-rfq-cart"><a href="' . esc_url( $cart_url ) . '">RFQ Cart: ' . esc_html( $count ) . ' item(s)</a></div>';
    }

    public static function acl_rfq_cart_content( ) {
        error_log( 'RFQ Cart Content Function Called' );
        wc_get_template( 'cart/cart.php', null, '', ACL_WC_SHORTCODES_PATH . 'src/frontend/templates/woocommerce/' );
    }


}