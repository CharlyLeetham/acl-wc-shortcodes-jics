<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

$quote_cart = WC()->session->get( 'quote_cart', array() );

if ( empty( $quote_cart ) ) {
    echo '<p class="cart-empty">' . esc_html_e( 'Your RFQ cart is currently empty.', 'woocommerce' ) . '</p>';
} else {
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
                <?php foreach ( $quote_cart as $item ) { ?>
                    <tr  class="woocommerce-cart-form__cart-item">
                        <td>
                        </td>
                        <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                            <?php echo esc_html( $item['name'] ); ?>
                        </td>
                        <?php //do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>
                        <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                            <?php echo esc_html( $item['quantity'] ); ?>
                        </td>                     
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- Add additional actions or buttons here if needed -->
    </form>
    <?php
}

do_action( 'woocommerce_after_cart' );



