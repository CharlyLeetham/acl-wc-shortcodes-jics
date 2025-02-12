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
            var newVal = currentVal + 1;
            input.val(newVal).change();
            updateMiniCart(input.attr('name').match(/\d+/)[0], newVal);
        }
    });    

    // Decrement Quantity
    $('.acl_minus_qty').on('click', function() {
        var input = $(this).next('.acl_qty_input');
        var currentVal = parseInt(input.val());
        if (!isNaN(currentVal) && currentVal > 1) {
            var newVal = currentVal - 1;
            input.val(newVal).change();
            updateMiniCart(input.attr('name').match(/\d+/)[0], newVal);
        }
    });

    $('.acl_qty_input').on('blur', function() {
        var productId = $(this).attr('name').match(/\d+/)[0];
        var qty = parseInt($(this).val(), 10); // Ensure it's a number
        if (!isNaN(qty) && qty > 0) { // Validate that qty is a positive number
            $.ajax({
                type: 'POST',
                url: acl_wc_shortcodes.ajax_url,
                data: {
                    'action': 'acl_update_quantity_in_quote_cart',
                    'product_id': productId,
                    'quantity': qty,
                    'security': acl_wc_shortcodes.nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Quantity updated for product ID:', productId, 'to:', qty);
                        // Update mini cart here
                        updateMiniCartDisplay(response.data.cart_count);
                    } else {
                        console.error('Error updating quantity:', response.data);
                    }
                },
                error: function(error) {
                    console.error('Error updating quantity:', error);
                }
            });
        } else {
            console.log('Invalid quantity entered for product ID:', productId);
            // Optionally, reset to previous valid value or show an error message
        }
    });

    // Function to update mini cart
    function updateMiniCart(productId, newQuantity) {
        console.log (productId, newQuantity);
        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: {
                'action': 'acl_update_mini_cart',
                'product_id': productId,
                'quantity': newQuantity,
                'security': acl_wc_shortcodes.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Mini cart updated for product ID:', productId, 'to:', newQuantity);
                    updateMiniCartDisplay(response.data.cart_count);
                } else {
                    console.error('Error updating mini cart:', response.data);
                }
            },
            error: function(error) {
                console.error('Error updating mini cart:', error);
            }
        });
    }

    // Function to update mini cart display
    function updateMiniCartDisplay(count) {
        var cartElement = $('.acl-mini-rfq-cart a');
        if (cartElement.length) {
            cartElement.text('RFQ Cart: ' + count + ' item(s)');
        }
    }    


    // Update Quantity
    $('.acl_qty_input').on('change', function() {
        var productId = $(this).attr('name').match(/\d+/)[0];
        var qty = $(this).val();
        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: {
                'action': 'acl_update_quantity_in_quote_cart',
                'product_id': productId,
                'quantity': qty,
                'security': acl_wc_shortcodes.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Quantity updated for product ID:', productId, 'to:', qty);
                } else {
                    console.error('Error updating quantity:', response.data);
                }
            },
            error: function(error) {
                console.error('Error updating quantity:', error);
            }
        });
    });

    // Remove from Quote Cart
    $(document).on('click', '.acl_remove_from_quote_cart', function(e) {
        console.log('Remove clicked');
        e.preventDefault();
        var productId = $(this).data('product-id');
        var quantity = $(this).closest('tr').find('.acl_qty_input').val(); // Get the quantity of the item being removed
        
        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: {
                'action': 'acl_remove_from_quote_cart', // Ensure this matches the action in your PHP hook
                'product_id': productId,
                'quantity': quantity,
                'security': acl_wc_shortcodes.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Product removed from quote cart:', productId);
                    // Remove the row from the DOM
                    $(e.target).closest('tr').remove();
                    // Update mini cart if needed
                    var cartElement = $('.acl-mini-rfq-cart a');
                    if (cartElement.length) {
                        var newCount = response.data.cart_count || (parseInt(cartElement.text().match(/\d+/)[0]) || 0) - 1;
                        cartElement.text('RFQ Cart: ' + newCount + ' item(s)');
                    }
                } else {
                    console.error('Error removing product:', response.data);
                }
            },
            error: function(error) {
                console.error('Error removing product:', error);
            }
        });
    });

    // Update Cart Button
    $('form.woocommerce-cart-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submit event triggered');
        var quantities = {};
        $('.acl_qty_input').each(function() {
            var productId = $(this).attr('name').match(/\d+/)[0];
            quantities[productId] = $(this).val();
        });
        console.log('Update quantities:', quantities);
    });
});