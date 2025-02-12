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
                        var newCount = response.data.cart_count || (parseInt(cartElement.text().match(/\d+/)[0]) || 0) + 1;
                        cartElement.text('RFQ Cart: ' + newCount + ' item(s)');
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

    // Increment Quantity
    $('.acl_plus_qty').on('click', function() {
        var input = $(this).prev('.acl_qty_input');
        var currentVal = parseInt(input.val());
        if (!isNaN(currentVal)) {
            input.val(currentVal + 1).change();
        }
    });

    // Decrement Quantity
    $('.acl_minus_qty').on('click', function() {
        var input = $(this).next('.acl_qty_input');
        var currentVal = parseInt(input.val());
        if (!isNaN(currentVal) && currentVal > 1) {
            input.val(currentVal - 1).change();
        }
    });

    // Update Quantity (you'll need AJAX here to update the session)
    $('.acl_qty_input').on('change', function() {
        var productId = $(this).attr('name').match(/\d+/)[0];
        var qty = $(this).val();
        // AJAX call to update quantity in session goes here
        console.log('Update quantity for product ID:', productId, 'to:', qty);
    });

    // Remove from Quote Cart
    $('.acl_remove_from_quote_cart').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        // AJAX call to remove item from cart goes here
        console.log('Remove product ID:', productId);
    });

    // Update Cart Button
    $('form.woocommerce-cart-form').on('submit', function(e) {
        e.preventDefault();
        // Collect all quantities and send via AJAX to update the cart
        var quantities = {};
        $('.acl_qty_input').each(function() {
            var productId = $(this).attr('name').match(/\d+/)[0];
            quantities[productId] = $(this).val();
        });
        // AJAX call to update cart quantities goes here
        console.log('Update quantities:', quantities);
    });    
});
