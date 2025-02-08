jQuery(document).ready(function($) {
    $('.quote-button').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id'); // Get the product ID from the data attribute

        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: {
                'action': 'acl_add_to_quote_cart',
                'product_id': productId,
                'security': acl_wc_shortcodes.nonce
            },
            success: function(response) {
                console.log('Product added to quote cart:', response);
                // Here you might want to update the UI or show a message to the user
                alert('Product added to your quote list!');
            },
            error: function(error) {
                console.error('Error adding product to quote cart:', error);
            }
        });
    });
});