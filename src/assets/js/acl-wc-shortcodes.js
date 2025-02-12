jQuery(document).ready(function($) {
 

    $(document).on('click', '.acl_remove_from_quote_cart', function(e) {
        console.log('Remove clicked - simplified');
        e.preventDefault();
        var productId = $(this).data('product-id');
        console.log('Product ID to remove:', productId);
    });
});