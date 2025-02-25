<?php

/**
 * Helper class for ACL WooCommerce Shortcodes.
 */

namespace ACLWcShortcodes\Helpers;
use ACLWcShortcodes\ACLWCRFQCart\ACL_WC_RFQ_cart;
use ACLWcShortcodes\ACLWCRFQWCEMail\ACL_WC_RFQ_Email;

class ACL_WC_Helpers {
    /**
     * Helper functions for ACL WooCommerce Shortcodes.
     */

    /**
     * Modifies the add to cart button class based on product attributes.
     *
     * @param WC_Product $product The product object.
     * @return array The modified class attributes for the buttons.
     */
    public static function get_modified_button_classes($product) {
        $purchase_attribute = $product->get_attribute('pa_purchase');
        $values = array_map('trim', explode(',', $purchase_attribute));
        
        $button_classes = [];

        if (empty($values)) {
            // If no attribute values, default to 'quote'
            $button_classes[] = 'quote-button';
        } else {
            foreach ($values as $value) {
                if ($value === 'quote') {
                    $button_classes[] = 'quote-button';
                }
                if ($value === 'purchase') {
                    $button_classes[] = 'purchase-button';
                }
            }
        }

        return $button_classes;
    }

    /**
     * Generates add to cart buttons based on product attributes.
     *
     * @param WC_Product $product The product object.
     * @return string HTML for the add to cart buttons.
     */
    public static function generate_add_to_cart_buttons($product) {
        $button_classes = self::get_modified_button_classes($product);
        $output = '';
        
        foreach ($button_classes as $class) {
            $button_class = esc_attr($product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button ' . $class : $class);
            
            if ($class === 'purchase-button') {
                // Use a shopping cart icon for the purchase button
                $button_content = '<svg class="fa-svg" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="shopping-cart" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M528.12 301.319l47.273-208C578.806 78.301 567.391 64 551.99 64H159.208l-9.166-44.81C147.758 8.021 137.93 0 126.529 0H24C10.745 0 0 10.745 0 24v16c0 13.255 10.745 24 24 24h69.883l70.248 343.435C147.325 417.1 136 435.222 136 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-15.674-6.447-29.835-16.824-40h209.647C430.447 426.165 424 440.326 424 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-22.172-12.888-41.332-31.579-50.405l5.517-24.276c3.413-15.018-8.002-29.319-23.403-29.319H218.117l-6.545-32h293.145c11.206 0 20.92-7.754 23.403-18.681z"></path></svg>';
            } else {
                // Use text for other buttons
                $button_text = $class === 'quote-button' ? 'Get Quote' : $product->add_to_cart_text();
                $button_content = esc_html($button_text);
            }
            
            $output .= '<div class="' . $button_class . '">';
            $output .= '<a href="' . esc_url($product->add_to_cart_url()) . '" rel="nofollow" data-product_id="' . esc_attr($product->get_id()) . '" data-product_sku="' . esc_attr($product->get_sku()) . '" class="button ' . $button_class . ' ajax_add_to_cart" data-quantity="1">';
            $output .= $button_content;
            $output .= '</a>';
            $output .= '</div>';
        }

        if (empty($button_classes)) {
            // If no buttons were created, ensure a default 'quote' button is added
            $output .= '<div class="quote-button">';
            $output .= '<a href="' . esc_url($product->add_to_cart_url()) . '" rel="nofollow" data-product_id="' . esc_attr($product->get_id()) . '" data-product_sku="' . esc_attr($product->get_sku()) . '" class="button quote-button ajax_add_to_cart" data-quantity="1">Get Quote</a>';
            $output .= '</div>';
        }

        return $output;
    }

    public static function acl_woocommerce_template_loop_category_link_open($category) {
        $category_term = get_term($category, 'product_cat');
        $category_name = (! $category_term || is_wp_error($category_term)) ? '' : $category_term->name;
        /* translators: %s: Category name */
        echo '<div class="acl-category-thumbnail"><a aria-label="' . sprintf(esc_attr__('Visit product category %1$s', 'woocommerce'), esc_attr($category_name)) . '" href="' . esc_url(get_term_link($category, 'product_cat')) . '">';
    }

    public static function acl_woocommerce_template_loop_product_link_open() {
        global $product;

        $link = apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);

        echo '<div class="acl-category-thumbnail"><a href="' . esc_url($link) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
    }

    /**
     * Custom function to display subcategory.
     *
     * @param WP_Term $category Category object.
     */
    public static function acl_woocommerce_subcategory_thumbnail($category) {
        $small_thumbnail_size = apply_filters('subcategory_archive_thumbnail_size', 'woocommerce_thumbnail');
        $dimensions           = wc_get_image_size($small_thumbnail_size);
        $thumbnail_id         = get_term_meta($category->term_id, 'thumbnail_id', true);

        if ($thumbnail_id) {
            $image        = wp_get_attachment_image_src($thumbnail_id, $small_thumbnail_size);
            $image        = $image[0];
            $image_srcset = function_exists('wp_get_attachment_image_srcset') ? wp_get_attachment_image_srcset($thumbnail_id, $small_thumbnail_size) : false;
            $image_sizes  = function_exists('wp_get_attachment_image_sizes') ? wp_get_attachment_image_sizes($thumbnail_id, $small_thumbnail_size) : false;
        } else {
            $image        = wc_placeholder_img_src();
            $image_srcset = false;
            $image_sizes  = false;
        }

        if ($image) {
            // Prevent esc_url from breaking spaces in urls for image embeds.
            // Ref: https://core.trac.wordpress.org/ticket/23605.
            $image = str_replace(' ', '%20', $image);

            // Add responsive image markup if available.
            if ($image_srcset && $image_sizes) {
                echo '<img src="' . esc_url($image) . '" alt="' . esc_attr($category->name) . '" width="' . esc_attr($dimensions['width']) . '" height="' . esc_attr($dimensions['height']) . '" srcset="' . esc_attr($image_srcset) . '" sizes="' . esc_attr($image_sizes) . '" /></div>';
            } else {
                echo '<img src="' . esc_url($image) . '" alt="' . esc_attr($category->name) . '" width="' . esc_attr($dimensions['width']) . '" height="' . esc_attr($dimensions['height']) . '" /></div>';
            }
        }
        echo '</a>';
    }

    /**
     * Show the subcategory title in the product loop.
     *
     * @param object $category Category object.
     */
    public static function acl_woocommerce_template_loop_category_title($category) {
        ?>
        <div class="acl-category-title">
            <h2 class="woocommerce-loop-category__title">
                <?php
                echo '<a aria-label="' . sprintf(esc_attr__('Visit product category %1$s', 'woocommerce'), esc_attr($category->name)) . '" href="' . esc_url(get_term_link($category, 'product_cat')) . '">';
                echo esc_html($category->name);

                if ($category->count > 0) {
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo apply_filters('woocommerce_subcategory_count_html', ' <mark class="count">(' . esc_html($category->count) . ')</mark>', $category);
                }
                echo '</a>';
                ?>
            </h2>
        </div>
        <div class="acl-category-readmore thrv_text_element">
            <?php
            echo '<a aria-label="' . sprintf(esc_attr__('Visit product category %1$s', 'woocommerce'), esc_attr($category->name)) . '" href="' . esc_url(get_term_link($category, 'product_cat')) . '">';
            echo "View " . esc_html($category->name) . " Catalog ->";
            echo '</a>';
            ?>
        </div>
        <?php
    }

    public static function acl_woocommerce_template_loop_product_title() {
        echo '</a></div><h2 class="' . esc_attr(apply_filters('woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title')) . '">' . get_the_title() . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public static function acl_custom_product_buttons() {
        global $product;

        // Get the attribute 'Purchase'
        $purchase_attribute = $product->get_attribute('pa_purchase'); // Assuming your attribute taxonomy is 'pa_purchase'

        echo '<div class="acl-single-product-custom-buttons" style="display: flex; flex-wrap: nowrap; justify-content: flex-start; align-items: center;">';

        // Show "Buy Now" button if purchase attribute contains 'purchase'
        if (strpos($purchase_attribute, 'purchase') !== false) {
            echo '<div class="acl-single-product-button-wrapper">';
            woocommerce_template_single_add_to_cart();
            echo '</div>';
        }
        
        // Show "Get Quote" button if purchase attribute contains 'quote'
        if (strpos($purchase_attribute, 'quote') !== false) {
            echo '<div class="acl-single-product-button-wrapper">';
            echo '<a href="#" data-product-id="' . esc_attr($product->get_id()) . '" class="button quote-button">Get Quote</a>';
            echo '</div>';
        }
        
        echo '</div>';
    }

    public static function acl_add_to_quote_cart_ajax( ) {
        error_log( 'Add to Quote ajax' );
        if ( !WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
        $session_id = WC()->session->get_customer_id();
        error_log( 'AJAX Request - Session ID: ' . $session_id );

        $product_id = isset( $_POST['product_id']) ? intval($_POST['product_id'] ) : 0;
        if ( $product_id ) {
            ACL_WC_RFQ_cart::acl_add_to_quote_cart( $product_id );
            $quote_cart = WC()->session->get( 'quote_cart' );
            wp_send_json_success( array( 'message' => 'Product added', 'cart_count' => count( $quote_cart ) ) );
        } else {
            wp_send_json_error( 'Invalid product ID' );
        }
    }

    public static function acl_remove_from_quote_cart() {
        check_ajax_referer( 'acl_wc_shortcodes_nonce', 'security' );
        
        $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        $quantity = isset ( $_POST['quantity'] ) ? intval( $_POST['quantity'] )  : 1; // Default to 1 if not provided
    
        if ($product_id) {
            $quote_cart = WC()->session->get( 'quote_cart', array() );
            $quote_cart = array_filter( $quote_cart, function( $item )  use ( $product_id ) {
                return $item['product_id'] !== $product_id;
            });
            
            WC()->session->set( 'quote_cart', $quote_cart );
            WC()->session->save_data();
            
            $count = count( $quote_cart );
            wp_send_json_success( array( 'message' => 'Product removed', 'cart_count' => $count)  );
        } else {
            wp_send_json_error( 'Invalid product ID' );
        }
    }
    
    public static function acl_update_mini_cart() {
        check_ajax_referer( 'acl_wc_shortcodes_nonce', 'security' );
    
        $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        $quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 0;
    
        if ( $product_id && $quantity > 0 ) {
            $quote_cart = WC()->session->get( 'quote_cart', array() );
            foreach ( $quote_cart as &$item ) {
                if ( $item['product_id'] === $product_id ) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
            WC()->session->set( 'quote_cart', $quote_cart );
            WC()->session->save_data();
            
            $count = array_reduce( $quote_cart, function( $carry, $item ) {
                return $carry + $item['quantity'];
            }, 0 );
    
            wp_send_json_success( array( 'cart_count' => $count ) );
        } else {
            wp_send_json_error( 'Invalid product ID or quantity' );
        }
    }  
    
    public static function acl_update_quantity_in_quote_cart() {
        //error_log ("entering update in quote cart");
        check_ajax_referer('acl_wc_shortcodes_nonce', 'security');
    
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
        if ($product_id && $quantity > 0) {
            $quote_cart = WC()->session->get('quote_cart', array());
            foreach ($quote_cart as &$item) {
                if ($item['product_id'] === $product_id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
            WC()->session->set('quote_cart', $quote_cart);
            WC()->session->save_data();
            
            $count = array_reduce($quote_cart, function($carry, $item) {
                return $carry + $item['quantity'];
            }, 0);
    
            wp_send_json_success(array('cart_count' => $count));
        } else {
            wp_send_json_error('Invalid product ID or quantity');
        }
    } 
    
    public static function acl_process_quote_submission() {
        if ( isset( $_POST['action'] ) && $_POST['action'] == 'acl_create_quote' ) {
            $email = sanitize_email( $_POST['acl_email'] );
            $firstname = sanitize_text_field( $_POST['acl_first_name'] );
            $lastname = sanitize_text_field( $_POST['acl_last_name'] );
    
            // Check if user exists
            $user = get_user_by( 'email', $email );
    
            if (!$user) {
                // Create account if email doesn't exist
                $username = sanitize_user( current( explode( '@', $email ) ) );
                $password = wp_generate_password();
                $user_id = wp_create_user( $username, $password, $email );
    
                if (is_wp_error($user_id)) {
                    wp_send_json_error(array('message' => 'Error creating account.'));
                    exit;
                }
    
                // Store generated password in user meta (DO NOT email it)
                update_user_meta($user_id, '_acl_temp_password', $password);
    
                // Auto-login new user
                wp_set_auth_cookie( $user_id, true );
                wp_set_current_user( $user_id );
    
            } else {
                // User exists → Show login form and do NOT create a quote yet
                ob_start();
                ?>
                <div class="woocommerce">
                    <h2><?php esc_html_e( 'Login Required', 'woocommerce' ); ?></h2>
                    <p><?php esc_html_e( 'An account already exists with this email. Please log in to continue.', 'woocommerce' ); ?></p>
    
                    <form method="post" class="woocommerce-form woocommerce-form-login login">
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" required>
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                            <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required>
                        </p>
                        <p class="form-row">
                            <input type="hidden" name="redirect" value="<?php echo esc_url( home_url( '/rfq-cart' ) ); ?>">
                            <button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
                                <?php esc_html_e( 'Log in', 'woocommerce' ); ?>
                            </button>
                        </p>
                    </form>
                </div>
                <?php
                $login_form = ob_get_clean();
    
                wp_send_json_success(array(
                    'login_form' => $login_form
                ));
                exit;
            }
    
            // If user was created or is logged in, proceed with the quote submission
            $quote_id = wp_insert_post( array(
                'post_type'   => 'acl_quote',
                'post_title'  => sprintf( 'Quote for %s', $firstname ),
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            ));
    
            if ($quote_id) {
                update_post_meta($quote_id, '_acl_first_name', $firstname);
                update_post_meta($quote_id, '_acl_last_name', $lastname);
                update_post_meta($quote_id, '_acl_email', $email);
    
                $quote_cart = WC()->session->get( 'quote_cart', array() );
                update_post_meta( $quote_id, '_acl_quote_items', $quote_cart );
    
                // Clear RFQ cart
                WC()->session->set( 'quote_cart', array() );
    
                wp_send_json_success(array('redirect' => wc_get_page_permalink( 'shop' )));
                exit;
            }
        }
    }
    
    
    
    public static function acl_register_custom_email( $emails ) {
        //error_log('🚀 woocommerce_email_classes filter executed.');
    
        require_once ACL_WC_SHORTCODES_PATH . 'src/frontend/ACL_WC_rfq_email.php';
        require_once ACL_WC_SHORTCODES_PATH . 'src/frontend/ACL_WC_Customer_Account_Email.php';
    
        if ( class_exists( 'ACLWcShortcodes\ACLWCRFQWCEMail\ACL_WC_RFQ_Email' ) ) {
            //error_log( "ACL_WC_RFQ_Email class exists!" );
            $emails['ACL_WC_RFQ_Email'] = new \ACLWcShortcodes\ACLWCRFQWCEMail\ACL_WC_RFQ_Email();
        }
    
        if ( class_exists( 'ACLWcShortcodes\ACLWCCustomerAccountEmail\ACL_WC_Customer_Account_Email' ) ) {
            //error_log( "ACL_WC_Customer_Account_Email class exists!" );
            $emails['ACL_WC_Customer_Account_Email'] = new \ACLWcShortcodes\ACLWCCustomerAccountEmail\ACL_WC_Customer_Account_Email();
        } else {
            //error_log( "ACL_WC_Customer_Account_Email class NOT FOUND!" );
        }
    
        return $emails;
    }

    public static function acl_ensure_email_system_ready() {
        //error_log( "WooCommerce has initialized, ensuring email system is ready." );
        WC()->mailer()->get_emails(); // This ensures `woocommerce_email_classes` gets applied
    }

    public static function acl_extend_session_lifetime( $time ) {
        return WEEK_IN_SECONDS * 4; // 4 weeks
    }

    public static function acl_process_login_submission() {
        if ( isset($_POST['action'] ) && $_POST['action'] == 'acl_process_login' ) {
            $username = sanitize_text_field( $_POST['username'] );
            $password = sanitize_text_field( $_POST['password'] );
    
            $user = wp_signon( array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => true
            ) );
    
            if ( is_wp_error( $user ) ) {
                wp_send_json_error( array( 'message' => 'Login failed. Please check your credentials.' ) );
            } else {
                wp_set_current_user( $user->ID );
                wp_set_auth_cookie( $user->ID, true );
    
                // After login, submit the quote automatically
                $quote_id = wp_insert_post( array(
                    'post_type'   => 'acl_quote',
                    'post_title'  => sprintf( 'Quote for %s', $user->display_name ),
                    'post_status' => 'publish',
                    'post_author' => $user->ID,
                ) );
    
                if ( $quote_id)  {
                    update_post_meta( $quote_id, '_acl_email', $user->user_email );
                    $quote_cart = WC()->session->get( 'quote_cart', array() );
                    update_post_meta( $quote_id, '_acl_quote_items', $quote_cart );
                    WC()->session->set( 'quote_cart', array() );
    
                    wp_send_json_success( array( 'redirect' => wc_get_page_permalink( 'shop' ) ) );
                } else {
                    wp_send_json_error( array( 'message' => 'Error submitting quote after login.' ) );
                }
            }
            exit;
        }
    }
    
    public static function acl_restore_rfq_cart_via_ajax() {
        error_log( '🔥 AJAX RFQ Cart Restore Triggered' );

        if ( !WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
        $session_id = WC()->session->get_customer_id();
        error_log( 'AJAX Request - Session ID: ' . $session_id );
            
        if ( !WC()->session->has_session() ) {
            error_log( '🚨 No WooCommerce session, forcing initialization...' );
            WC()->session->set_customer_session_cookie( true );
        }
    
        if ( !isset( WC()->session ) || !WC()->session instanceof WC_Session ) {
            error_log( '❌ WooCommerce session still unavailable after AJAX trigger.' );
            wp_send_json_error( array( 'message' => 'Session unavailable' ) );
            return;
        }
    
        $quote_cart = WC()->session->get( 'quote_cart', array() );
        if ( !is_array( $quote_cart ) ) {
            $quote_cart = array();
        }
    
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $blog_id = get_current_blog_id();
            $meta_key = '_acl_persistent_rfq_cart_' . $blog_id;
    
            error_log( '🔎 Checking saved RFQ cart for user: ' . $user_id );
    
            $saved_rfq_cart = get_user_meta( $user_id, $meta_key, true );
            if ( !empty( $saved_rfq_cart ) ) {
                $quote_cart = maybe_unserialize( $saved_rfq_cart );
                WC()->session->set( 'quote_cart', $quote_cart );
                error_log( '✅ RFQ Cart Restored for user: ' . $user_id );
            }
        }
    
        WC()->session->set( 'quote_cart', $quote_cart );
        error_log ( '💾 Quote cart saved in session via AJAX: ' . print_r( $quote_cart, true ) );
    
        wp_send_json_success( array( 'message' => 'Cart restored', 'cart' => $quote_cart ) );
    }
    

    

}