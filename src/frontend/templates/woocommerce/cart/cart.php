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
    <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
        <?php do_action( 'woocommerce_before_cart_table' ); ?>
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
                            <a href="#" class="acl_remove_from_quote_cart" data-product-id="<?php echo esc_attr( $product_id ); ?>">
                                <?php echo esc_html_x( '×', 'remove item', 'woocommerce' ); ?>
                            </a>
                        </td>
                        <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                            <?php echo esc_html( $item['name'] ); ?>
                        </td>
                        <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                            <div class="acl_quantity">
                                <button type="button" class="acl_minus_qty" data-product-id="<?php echo esc_attr( $product_id ); ?>">-</button>
                                <input type="number" class="acl_qty_input" name="acl_qty[<?php echo esc_attr( $product_id ); ?>]" value="<?php echo esc_attr( $item['quantity'] ); ?>" min="1" />
                                <button type="button" class="acl_plus_qty" data-product-id="<?php echo esc_attr( $product_id ); ?>">+</button>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <input type="submit" class="button" name="acl_update_cart" value="<?php esc_attr_e( 'Update Cart', 'woocommerce' ); ?>" />
    </form>
    <?php
}

do_action( 'woocommerce_after_cart' );