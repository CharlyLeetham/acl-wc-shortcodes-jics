public static function acl_add_to_quote_cart_ajax() {
    error_log('POST Data: ' . var_export($_POST, true));
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        error_log('Product ID to Add: ' . $product_id);
        \ACLWcShortcodes\ACLWCRFQCart\ACL_WC_RFQ_cart::acl_add_to_quote_cart($product_id);
        
        // Save session data explicitly after adding to cart
        WC()->session->save_data();
        
        error_log('After AJAX Addition - Quote Cart: ' . var_export(WC()->session->get('quote_cart'), true));
        wp_send_json_success('Product added to quote cart.');
    } else {
        error_log('Product ID not provided in AJAX call');
        wp_send_json_error('Product ID not provided.');
    }
}