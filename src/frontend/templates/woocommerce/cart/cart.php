<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

$quote_cart = WC()->session->get( 'quote_cart', array() );

if ( empty( $quote_cart ) ) {
    echo '<p class="cart-empty">' . esc_html_e( 'Your RFQ cart is currently empty.', 'woocommerce' ) . '</p>';
} else {
    // Group items by product ID to have one line per product
    $grouped_cart = array();
    foreach ( $quote_cart as $item ) {
        $product_id = $item['product_id'];
        if ( isset( $grouped_cart[$product_id] ) ) {
            $grouped_cart[$product_id]['quantity'] += $item['quantity'];
        } else {
            $grouped_cart[$product_id] = $item;
        }
    }
    ?>
    <div class="woocommerce">
        <form class="woocommerce-cart-form acl_quote_submission_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <?php wp_nonce_field( 'acl_quote_submission', 'acl_quote_nonce' ); ?>
            <input type="hidden" name="action" value="acl_create_quote">
            
            <?php do_action( 'acl_woocommerce_before_cart_table' ); ?>
            <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
                <thead>
                    <tr>
                        <th class="product-remove"><span class="screen-reader-text"><?php esc_html_e( 'Remove item', 'woocommerce' ); ?></span></th>
                        <th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
                        <th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php do_action( 'woocommerce_before_cart_contents' ); ?>
                    <?php foreach ( $grouped_cart as $product_id => $item ) { ?>
                        <tr class="woocommerce-cart-form__cart-item">
                            <td class="product-remove">
                                <a href="#" class="acl_remove_from_quote_cart remove" data-product-id="<?php echo esc_attr( $product_id ); ?>">
                                    <?php echo esc_html_x( '×', 'remove item', 'woocommerce' ); ?>
                                </a>
                            </td>
                            <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                                <?php echo esc_html( $item['name'] ); ?>
                            </td>
                            <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                                <div class="acl_quantity quantity">
                                    <button type="button" class="acl_minus_qty tve-woo-quantity-button" data-product-id="<?php echo esc_attr( $product_id ); ?>">-</button>
                                    <input type="number" class="acl_qty_input input-text qty text" name="acl_qty[<?php echo esc_attr( $product_id ); ?>]" value="<?php echo esc_attr( $item['quantity'] ); ?>" min="1" />
                                    <button type="button" class="acl_plus_qty tve-woo-quantity-button" data-product-id="<?php echo esc_attr( $product_id ); ?>">+</button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php do_action( 'acl_woocommerce_after_cart_table' ); ?>
            <input type="submit" class="button" name="acl_update_cart" value="<?php esc_attr_e( 'Update Cart', 'woocommerce' ); ?>" />
            <button type="submit" class="button alt" name="acl_place_quote" value="Submit Quote"><?php esc_html_e( 'Submit Quote', 'woocommerce' ); ?></button>
        </form>
    </div>
    <?php
}

do_action( 'woocommerce_after_cart' );