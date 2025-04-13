jQuery(document).ready(function($) {
    // Quote Submission Form
    $('.acl_quote_submission_form').on('submit', function(e) {
        e.preventDefault();
        console.log('Quote form submit event triggered');
        
        var formData = $(this).serialize();
        
        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: formData + '&action=acl_create_quote',
            success: function(response) {
                console.log('Quote submission response:', response);
                if (response.success) {
                    console.log('Quote submitted successfully');
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        alert('Quote submitted successfully, but no redirect URL provided.');
                    }
                } else {
                    console.error('Error submitting quote:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX submission error:', status, error);
                console.error('Error details:', xhr.responseText);
            }
        });
    });

    jQuery(document).ready(function($) {
        // Add to Quote Cart
        $('.quote-button').on('click', function(e) {
            e.preventDefault();
            //var productId = $(this).data('product-id');
            var productId = $(this).attr('data-product-id');
            console.log('Quote button clicked, Run #', $(this).data('clickCount') || 1, 'Element:', this.outerHTML);
            console.log('Product ID:', productId);
    
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
                        if (response.data.already_in_cart) {
                            alert(response.data.message); // Show message
                        } else {
                            console.log('Product added to quote cart:', response);
                            var cartElement = $('.acl-mini-rfq-cart a');
                            if (cartElement.length) {
                                var newCount = response.data.cart_count || (parseInt(cartElement.text().match(/\d+/)[0]) || 0) + 1;
                                var iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                              '<circle cx="9" cy="21" r="1"/>' +
                                              '<circle cx="20" cy="21" r="1"/>' +
                                              '<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>' +
                                              '</svg>';
                                cartElement.html(iconSvg + '<span class="rfq-cart-count">' + newCount + '</span>');
                            }
                        }
                    } else {
                        console.error('Error:', response.data);
                    }
                },
                error: function(error) {
                    console.error('Error adding product to quote cart:', error);
                }
            });
            console.log('Handler bound to .quote-button');
        });
    
        // Update data-product-id with variation_id
        $('.variations_form').on('found_variation.wc-variation-form', function(event, variation) {
            var variation_id = variation.variation_id;
            var $button = $(this).find('.quote-button');
            var original_product_id = $button.data('original-product-id') || $button.attr('data-product-id');
            $button.data('original-product-id', original_product_id);
            if (variation_id && variation_id !== '0') {
                $button.attr('data-product-id', variation_id);
            } else {
                $button.attr('data-product-id', original_product_id);
            }
        });
    
        // Store original product_id
        $('.quote-button').each(function() {
            $(this).data('original-product-id', $(this).data('product-id'));
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

    // Update Quantity on Blur
    $('.acl_qty_input').on('blur', function() {
        var productId = $(this).attr('name').match(/\d+/)[0];
        var qty = parseInt($(this).val(), 10);
        if (!isNaN(qty) && qty > 0) {
            console.log('Updating quantity - Product ID:', productId, 'New Quantity:', qty);
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
                    console.log('AJAX Response:', response);
                    if (response.success) {
                        console.log('Quantity updated for product ID:', productId, 'to:', qty, 'Cart Count:', response.data.cart_count);
                        console.log('Quantity updated for product ID:', productId, 'to:', qty);
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
        }
    });

    // Update Quantity on Keyup with Delay
    $('.acl_qty_input').on('keyup', function() {
        var $input = $(this);
        var productId = $input.attr('name').match(/\d+/)[0];
        var qty = parseInt($input.val(), 10);

        clearTimeout($input.data('timeout'));

        $input.data('timeout', setTimeout(function() {
            if (!isNaN(qty) && qty > 0) {
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
            }
        }, 2000));
    }); 
    
    $('#acl_update_cart').on('click', function(e) {
        e.preventDefault();
        console.log('Update Cart button clicked');

        var quantities = {};
        var details = {};
        $('.acl_qty_input').each(function() {
            var productId = $(this).attr('name').match(/\d+/)[0];
            quantities[productId] = $(this).val();
        });
        $('.product-details textarea').each(function() {
            var productId = $(this).attr('name').match(/\d+/)[0];
            details[productId] = $(this).val();
        });

        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: {
                action: 'acl_update_rfq_cart',
                quantities: quantities,
                details: details,
                security: acl_wc_shortcodes.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('RFQ cart updated:', response.data);
                    updateMiniCartDisplay(response.data.cart_count);
                    alert('RFQ cart updated successfully!');
                } else {
                    console.error('Error updating RFQ cart:', response.data);
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    });    

    // Update Mini Cart Function
    function updateMiniCart(productId, newQuantity) {
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

    function updateMiniCartDisplay(totalQty) {
        var cartElement = $('.acl-mini-rfq-cart a');
        if (cartElement.length) {
            var countSpan = cartElement.find('.rfq-cart-count');
            if (totalQty > 0) {
                // Update or create the quantity span
                if (countSpan.length) {
                    countSpan.text(totalQty);
                } else {
                    cartElement.append('<span class="rfq-cart-count">' + totalQty + '</span>');
                }
            } else {
                // Remove the span and set empty cart text
                if (countSpan.length) {
                    countSpan.remove();
                }
                cartElement.contents().filter(function() {
                    return this.nodeType === 3; // Text nodes only
                }).remove(); // Remove any existing text nodes
                cartElement.prepend('RFQ Cart: 0 items');
            }
        }
    }

    // Remove from Quote Cart
    $(document).on('click', '.acl_remove_from_quote_cart', function(e) {
        console.log('Remove clicked');
        e.preventDefault();
        var productId = $(this).data('product-id');
        var quantity = $(this).closest('tr').find('.acl_qty_input').val();

        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: {
                'action': 'acl_update_quantity_in_quote_cart',
                'product_id': productId,
                'quantity': 0,
                'security': acl_wc_shortcodes.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Product removed from quote cart:', productId);
                    $(e.target).closest('tr').remove();
                    var cartElement = $('.acl-mini-rfq-cart a');
                    if (cartElement.length) {
                        // Calculate total quantity from remaining items
                        var totalQty = 0;
                        $('.acl_qty_input').each(function() {
                            totalQty += parseInt($(this).val()) || 0;
                        });
                        updateMiniCartDisplay(totalQty);
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

    // Update Cart Button (for updating all quantities)
    $('form.woocommerce-cart-form:not(.acl_quote_submission_form)').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submit event triggered for cart update');
        var quantities = {};
        $('.acl_qty_input').each(function() {
            var productId = $(this).attr('name').match(/\d+/)[0];
            quantities[productId] = $(this).val();
        });
        console.log('Update quantities:', quantities);
        // Here you would implement an AJAX call to update all quantities at once if needed
    });

    // Submit Quote Form
    $('.acl_quote_submission_form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: formData + '&action=acl_create_quote',
            success: function(response) {
                if (response.success) {
                    if (response.login_form) {
                        $('.acl_quote_submission_form').replaceWith(response.login_form);
                    } else if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    });

    // Login Form Submission
    $(document).on('submit', '.woocommerce-form-login', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: acl_wc_shortcodes.ajax_url,
            data: formData + '&action=acl_process_login',
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    alert('Login failed: ' + response.message);
                }
            }
        });
    });    
});