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
                    <tr  class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
                    <td class="product-remove">
							<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										/* translators: %s is the product name */
										esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
										esc_attr( $product_id ),
										esc_attr( $item->get_sku() )
									),
									$cart_item_key
								);
							?>
						</td>
                        <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                            <?php echo esc_html( $item['name'] ); ?>
                        </td>
                        <?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>
                        <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                            <?php echo esc_html( $item['quantity'] ); ?>
                        </td>

						<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
						<?php
						/*if ( $item->is_sold_individually() ) {
							$min_quantity = 1;
							$max_quantity = 1;
						} else {
							$min_quantity = 0;
							$max_quantity = $item->get_max_purchase_quantity();
						} */

						$product_quantity = woocommerce_quantity_input(
							array(
								'input_name'   => "cart[{$cart_item_key}][qty]",
								'input_value'  => $cart_item['quantity'],
								'max_value'    => $max_quantity,
								'min_value'    => $min_quantity,
								'product_name' => $product_name,
							),
							$item,
							false
						);

						echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
						?>
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



