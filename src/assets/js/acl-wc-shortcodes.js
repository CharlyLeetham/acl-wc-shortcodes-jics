jQuery(document).ready(function($) {
    $('.quote-button').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');

        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: {
                'action': 'acl_add_to_quote_cart',
                'product_id': productId,
                'security': acl_wc_shortcodes.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Product added to quote cart:', response);
                    // Update the mini cart display
                    var cartElement = $('.acl-mini-rfq-cart a');
                    if (cartElement.length) {
                        var currentCount = parseInt(cartElement.text().match(/\d+/)[0]) || 0;
                        cartElement.text('RFQ Cart: ' + (currentCount + 1) + ' item(s)');
                    }
                } else {
                    console.error('Error:', response.data);
                }
            },
            error: function(error) {
                console.error('Error adding product to quote cart:', error);
            }
        });
    });
});