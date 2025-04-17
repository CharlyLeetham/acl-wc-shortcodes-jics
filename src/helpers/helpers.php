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
            
            $button_url = ($class === 'quote-button') ? '#' : esc_url($product->add_to_cart_url());
            $div_class = $class === 'purchase-button' ? 'add_to_cart_button purchase-button' : 'add_to_cart_button';

            $output .= '<div class="' . esc_attr($div_class) . '">';
            $output .= '<a href="' . $button_url . '" rel="nofollow" data-product-id="' . esc_attr($product->get_id()) . '" data-product_sku="' . esc_attr($product->get_sku()) . '" class="button ' . $button_class . ' ajax_add_to_cart" data-quantity="1">';
            $output .= $button_content;
            $output .= '</a>';
            $output .= '</div>';
        }

        if (empty($button_classes)) {
            // If no buttons were created, ensure a default 'quote' button is added
            $output .= '<div class="add_to_cart_button">';
            $output .= '<a href="#" rel="nofollow" data-product-id="' . esc_attr($product->get_id()) . '" data-product_sku="' . esc_attr($product->get_sku()) . '" class="button quote-button ajax_add_to_cart add_to_cart_button" data-quantity="1">Get Quote</a>';
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

        $debug = false;
        if (!$debug) {

            // Show "Buy Now" button if purchase attribute contains 'purchase'
            if ( strpos( $purchase_attribute, 'purchase' ) !== false ) {
                echo '<div class="acl-single-product-button-wrapper">';
                woocommerce_template_single_add_to_cart();
                echo '</div>';
            }
            
            // Show "Get Quote" button if purchase attribute contains 'quote'
            if ( strpos( $purchase_attribute, 'quote' ) !== false || !$purchase_attribute ) {
                echo '<div class="acl-single-product-button-wrapper">';
                $product = wc_get_product($product->get_id());
                if ($product->is_type('variable')) {
                    wc_get_template(
                        'single-product/add-to-cart/variable-quote.php',
                        array(
                            'available_variations' => $product->get_available_variations(),
                            'attributes'           => $product->get_variation_attributes(),
                            'selected_attributes'  => $product->get_default_attributes(),
                        ),
                        '',
                        ACL_WC_SHORTCODES_PATH . 'src/frontend/templates/'
                    );
                } else {
                    echo '<a href="#" data-product-id="' . esc_attr($product->get_id()) . '" class="button quote-button">Get Quote</a>';
                }

                echo '</div>';
            }
        }
        
        echo '</div>';
    }

    public static function acl_add_to_quote_cart_ajax( ) {
        if ( !WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
        $session_id = WC()->session->get_customer_id();

        $product_id = isset( $_POST['product_id']) ? intval($_POST['product_id'] ) : 0;
        if ( $product_id ) {
            $quote_cart = WC()->session->get( 'quote_cart' );
            $total_quantity = array_reduce($quote_cart, function($carry, $item) {
                return $carry + (isset($item['quantity']) ? intval($item['quantity']) : 0);
            }, 0);

            if ( isset( $quote_cart[$product_id] ) ) {
                wp_send_json_success( array(
                    'message' => 'This product is already added for quote.',
                    'cart_count' => $total_quantity,
                    'already_in_cart' => true
                ) );
            } else {
                ACL_WC_RFQ_cart::acl_add_to_quote_cart( $product_id );
                $quote_cart = WC()->session->get( 'quote_cart' );
                $total_quantity = array_reduce($quote_cart, function($carry, $item) {
                    return $carry + (isset($item['quantity']) ? intval($item['quantity']) : 0);
                }, 0);
                wp_send_json_success(array(
                    'message' => 'Product added for quote',
                    'cart_count' => $total_quantity,
                    'already_in_cart' => false
                ));
            }
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
        check_ajax_referer('acl_wc_shortcodes_nonce', 'security');
        
        // Ensure session is active
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }
        $session_id = WC()->session->get_customer_id();
    
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
        $quote_cart = WC()->session->get('quote_cart', array());
    
        if ($product_id && $quantity > 0) {
            foreach ($quote_cart as &$item) {
                if ($item['product_id'] === $product_id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
            WC()->session->set('quote_cart', $quote_cart);
            WC()->session->save_data();
        }
    
        // For logged-in users, only load from meta if session cart is empty
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $blog_id = get_current_blog_id();
            $meta_key = '_acl_persistent_rfq_cart_' . $blog_id;
    
            $saved_rfq_cart = get_user_meta($user_id, $meta_key, true);
            if (!empty($saved_rfq_cart) && empty($quote_cart)) { // Only override if session is empty
                $quote_cart = maybe_unserialize($saved_rfq_cart);
                WC()->session->set('quote_cart', $quote_cart);
                WC()->session->save_data();
            }
        }
    
        // Sync session to database for guests
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}woocommerce_sessions SET session_value = %s WHERE session_key = %s",
                maybe_serialize(array_merge(WC()->session->get_session_data(), ['quote_cart' => $quote_cart])),
                $session_id
            )
        );
    
        // Persist RFQ cart in user meta for logged-in users
        if (is_user_logged_in() && apply_filters('woocommerce_persistent_cart_enabled', true)) {
            if (!empty($quote_cart)) {
                update_user_meta($user_id, $meta_key, maybe_serialize($quote_cart));
            } else {
                delete_user_meta($user_id, $meta_key);
            }
        }
    
        $count = array_reduce($quote_cart, function($carry, $item) {
            return $carry + $item['quantity'];
        }, 0);
    
        wp_send_json_success(array('cart_count' => $count));
    }
    
    public static function acl_update_quantity_in_quote_cart() {
        check_ajax_referer('acl_wc_shortcodes_nonce', 'security');
      
        // Ensure session is active
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }
        $session_id = WC()->session->get_customer_id();
    
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
        if ($product_id) {
            $quote_cart = WC()->session->get('quote_cart', array());
    
            if ($quantity > 0) {
                // Update quantity
                $found = false;
                foreach ($quote_cart as &$item) {
                    if ($item['product_id'] === $product_id) {
                        $item['quantity'] = $quantity;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // If product isn’t in cart, add it (optional, adjust based on your needs)
                    $quote_cart[] = array('product_id' => $product_id, 'quantity' => $quantity);
                }
            } else {
                // Remove item
                $quote_cart = array_filter($quote_cart, function($item) use ($product_id) {
                    return $item['product_id'] !== $product_id;
                });
            }

            WC()->session->set('quote_cart', $quote_cart);
            WC()->session->save_data();
    
            // Sync to database for guests
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}woocommerce_sessions SET session_value = %s WHERE session_key = %s",
                    maybe_serialize(array_merge(WC()->session->get_session_data(), ['quote_cart' => $quote_cart])),
                    $session_id
                )
            );
    
            // Sync to user meta for logged-in users
            if (is_user_logged_in() && apply_filters('woocommerce_persistent_cart_enabled', true)) {
                $user_id = get_current_user_id();
                $blog_id = get_current_blog_id();
                $meta_key = '_acl_persistent_rfq_cart_' . $blog_id;
                if (!empty($quote_cart)) {
                    update_user_meta($user_id, $meta_key, maybe_serialize($quote_cart));
                } else {
                    delete_user_meta($user_id, $meta_key);
                }
            }
    
            $count = array_reduce($quote_cart, function($carry, $item) {
                return $carry + $item['quantity'];
            }, 0);
    
            wp_send_json_success(array('cart_count' => $count));
        } else {
            wp_send_json_error('Invalid product ID or quantity');
        }
    }

    public static function acl_update_rfq_cart() {
        check_ajax_referer('acl_wc_shortcodes_nonce', 'security');

        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }
        $session_id = WC()->session->get_customer_id();

        $quantities = isset($_POST['quantities']) ? (array) $_POST['quantities'] : array();
        $details = isset($_POST['details']) ? (array) $_POST['details'] : array();
        $quote_cart = WC()->session->get('quote_cart', array());

        // Update quantities and details in the RFQ cart
        foreach ($quantities as $product_id => $quantity) {
            $product_id = intval($product_id);
            $quantity = intval($quantity);
            if ($quantity > 0 && isset($quote_cart[$product_id])) {
                $quote_cart[$product_id]['quantity'] = $quantity;
                if (isset($details[$product_id]) && !empty($details[$product_id])) {
                    $quote_cart[$product_id]['product-deets'] = sanitize_text_field($details[$product_id]);
                }
            } elseif ($quantity <= 0 && isset($quote_cart[$product_id])) {
                unset($quote_cart[$product_id]); // Remove item if quantity is 0 or less
            }
        }

        // Save updated cart to session
        WC()->session->set('quote_cart', $quote_cart);
        WC()->session->save_data();

        // Sync to database for guests
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}woocommerce_sessions SET session_value = %s WHERE session_key = %s",
                maybe_serialize(array_merge(WC()->session->get_session_data(), ['quote_cart' => $quote_cart])),
                $session_id
            )
        );

        // Sync to user meta for logged-in users
        if (is_user_logged_in() && apply_filters('woocommerce_persistent_cart_enabled', true)) {
            $user_id = get_current_user_id();
            $blog_id = get_current_blog_id();
            $meta_key = '_acl_persistent_rfq_cart_' . $blog_id;
            if (!empty($quote_cart)) {
                update_user_meta($user_id, $meta_key, maybe_serialize($quote_cart));
            } else {
                delete_user_meta($user_id, $meta_key);
            }
        }

        // Calculate total quantity for mini-cart
        $cart_count = array_reduce($quote_cart, function($carry, $item) {
            return $carry + (isset($item['quantity']) ? intval($item['quantity']) : 0);
        }, 0);

        wp_send_json_success(array('cart_count' => $cart_count));
    }    
    
    public static function acl_process_quote_submission() {
        if (isset($_POST['action']) && $_POST['action'] === 'acl_create_quote') {
            // Verify nonce
            if (!check_ajax_referer('acl_quote_submission', 'acl_quote_nonce', false)) {
                wp_send_json_error(['message' => __('Security check failed.', 'woocommerce')]);
                exit;
            }
    
            // Validate required fields
            $email = sanitize_email($_POST['acl_email'] ?? '');
            $firstname = sanitize_text_field($_POST['acl_first_name'] ?? '');
            $lastname = sanitize_text_field($_POST['acl_last_name'] ?? '');
            $suburb = sanitize_text_field($_POST['acl_suburb'] ?? '');
            $state = sanitize_text_field($_POST['acl_state'] ?? '');
            $postcode = sanitize_text_field($_POST['acl_postcode'] ?? '');
            $address_line1 = sanitize_text_field($_POST['acl_address_line1'] ?? '');
            $address_line2 = sanitize_text_field($_POST['acl_address_line2'] ?? '');
            $phone = sanitize_text_field($_POST['acl_phone'] ?? '');
    
            if (empty($email) || empty($firstname) || empty($lastname) || empty($suburb) || empty($state) || empty($postcode)) {
                wp_send_json_error(['message' => __('Please fill in all required fields.', 'woocommerce')]);
                exit;
            }
    
            // Prepare email data
            $customer_name = $firstname . ' ' . $lastname;
            $address = trim($address_line1 . "\n" . $address_line2 . "\n" . $suburb . ', ' . $state . ' ' . $postcode);
            $quote_items = [];
    
            $quote_cart = WC()->session->get('quote_cart', []);
            foreach ($quote_cart as $item) {
                $product_id = $item['product_id'] ?? 0;
                $quantity = $item['quantity'] ?? 1;
                $product = wc_get_product($product_id);
                if ($product) {
                    $quote_items[] = [
                        'name' => $product->get_name(),
                        'sku' => $product->get_sku() ?: 'N/A',
                        'quantity' => $quantity
                    ];
                }
            }
    
            if (empty($quote_items)) {
                $quote_items[] = ['name' => 'No items in quote', 'sku' => '', 'quantity' => 0];
            }
    
            // Email data for templates
            $email_data = [
                'quote_id' => 0, // No quote post, but included for compatibility
                'email_heading' => __('New Quote Request', 'woocommerce'),
                'sent_to_admin' => true,
                'plain_text' => false,
                'email' => null,
                'quote_details' => [
                    '_acl_first_name' => [$firstname],
                    '_acl_last_name' => [$lastname],
                    '_acl_email' => [$email],
                    '_acl_phone' => [$phone],
                    '_acl_address_line1' => [$address_line1],
                    '_acl_address_line2' => [$address_line2],
                    '_acl_suburb' => [$suburb],
                    '_acl_state' => [$state],
                    '_acl_postcode' => [$postcode],
                    '_acl_quote_items' => [serialize($quote_items)] // Added serialized items
                ],
                'quote_items' => $quote_items,
                'password_message' => '', // No user creation
                'customer_name' => $customer_name,
                'address' => $address
            ];
   
            // Email to shop owner (using ACL_WC_RFQ_Email)
            do_action('acl_quote_request_created', 0, $email_data);

            // Email to customer (using ACL_WC_Customer_Account_Email)
            do_action('acl_wc_send_customer_account_email', $email_data);
    
            // Clear RFQ cart
        // Clear main cart if synced
            WC()->session->set('cart', []);
            WC()->cart->empty_cart();
            \wc_clear_cart_transients();

            wp_send_json_success(['redirect' => wc_get_page_permalink('shop')]);
            exit;
        }
    }
    
    
    
    public static function acl_register_custom_email( $emails ) {
        require_once ACL_WC_SHORTCODES_PATH . 'src/frontend/ACL_WC_rfq_email.php';
        require_once ACL_WC_SHORTCODES_PATH . 'src/frontend/ACL_WC_Customer_Account_Email.php';
    
        if ( class_exists( 'ACLWcShortcodes\ACLWCRFQWCEMail\ACL_WC_RFQ_Email' ) ) {
            $emails['ACL_WC_RFQ_Email'] = new \ACLWcShortcodes\ACLWCRFQWCEMail\ACL_WC_RFQ_Email();
        }
    
        if ( class_exists( 'ACLWcShortcodes\ACLWCCustomerAccountEmail\ACL_WC_Customer_Account_Email' ) ) {
            $emails['ACL_WC_Customer_Account_Email'] = new \ACLWcShortcodes\ACLWCCustomerAccountEmail\ACL_WC_Customer_Account_Email();
        }
    
        return $emails;
    }

    public static function acl_ensure_email_system_ready() {
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

    public static function acl_custom_add_to_cart_text( $default_text, \WC_Product $product ) {
        // Check if the product has a price (non-empty and not zero)
        $has_price = ! empty( $product->get_price() ) && $product->get_price() > 0;

        if ( $has_price ) {
            if ( $product->is_type( 'simple' ) ) {
                return 'Add to Cart'; // Simple product with a price
            } elseif ( $product->is_type( 'variable' ) ) {
                return 'View Product'; // Variable product with a price
            }
        }

        // Default for no price or other product types
        return 'View Product';
    }

    public static function acl_wc_capitalise_product_title( $title, $post_id ) {
        // Convert to lowercase and capitalize each word

        if ( get_post_type( $post_id ) === 'product' ) {
            return ucwords( strtolower( $title ) );
        }
        return $title;        
    } 
    
    public static function acl_wc_capitalise_cat_title( $term, $taxonomy ) {
        // Convert to lowercase and capitalize each word

        if ( $taxonomy === 'product_cat' ) {
            $term->name = ucwords( strtolower( $term->name ) );
        }
        return $term;
    }

    public static function acl_make_variations_available( $variation_data, $product, $variation ) {
        $variation_data['is_purchasable'] = true;
        $variation_data['is_in_stock'] = true;
        $variation_data['variation_is_active'] = true;
        $variation_data['variation_is_visible'] = true;
        return $variation_data;
    }    
}