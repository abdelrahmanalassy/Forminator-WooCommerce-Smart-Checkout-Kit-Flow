<?php
/**
 * Plugin Name: Instant Checkout via Forminator
 * Description: Automatically adds products to the WooCommerce cart on Forminator form submission and redirects users to checkout. Ideal for registration flows.
 * Version: 1.6
 * Author: Abdelrahman Ashraf
 * Author URI: https://www.linkedin.com/in/abdelrahman-ashraf-elassy/
 * Requires Plugins: Forminator, Woocommerce
 * Requires at least: 5.0
 * Tested up to: 6.5
 * Requires PHP: 7.4
 */

// -----------------------------------------------------------------------------
// Add GitHub link to the plugin in WP admin plugin list
// -----------------------------------------------------------------------------
add_filter('plugin_row_meta', 'forminator_checkout_plugin_meta_links', 10, 2);
function forminator_checkout_plugin_meta_links($links, $file) {
    if (strpos($file, 'forminator-kit-redirect.php') !== false) {
        $new_links = array(
            '<a href="https://github.com/abdelrahmanalassy/Instant-Checkout-via-Forminator" target="_blank">View on GitHub</a>'
        );
        $links = array_merge($links, $new_links);
    }
    return $links;
}

// -----------------------------------------------------------------------------
// Include additional PHP files that handle order syncing and role control
// -----------------------------------------------------------------------------
require_once plugin_dir_path(__FILE__) . 'watch-new-order.php';
require_once plugin_dir_path(__FILE__) . 'forminator-role-control.php';

// -----------------------------------------------------------------------------
// Enqueue the JS script that runs on the front-end (handles kit selection, etc.)
// -----------------------------------------------------------------------------
function seaperch_enqueue_redirect_script() {
    wp_enqueue_script(
        'redirect-final',
        plugin_dir_url(__FILE__) . 'redirect-final.js',
        array('jquery'),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'seaperch_enqueue_redirect_script');

// -----------------------------------------------------------------------------
// Redirect user to login page after successful order/payment (Thank You page)
// -----------------------------------------------------------------------------
add_action('template_redirect', function () {
    // Skip admin and AJAX calls
    if (is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax())) {
        return;
    }

    // Make sure we’re on the “Order Received” page
    if (!function_exists('is_order_received_page') || !is_order_received_page()) {
        return;
    }

    // Get the order ID from the URL
    $order_id = absint(get_query_var('order-received'));
    if (!$order_id) {
        return;
    }

    // Get the order object
    if (!function_exists('wc_get_order')) {
        return;
    }
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    // Optional: confirm that order key matches
    $order_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
    if ($order_key && $order->get_order_key() !== $order_key) {
        return;
    }

    // Only redirect after successful payment
    if ($order->has_status(array('processing', 'completed'))) {
        $target = 'https://your-website/thank-you/';

        // Avoid infinite redirect loops
        $current_url_path = isset($_SERVER['REQUEST_URI']) ? trailingslashit(home_url($_SERVER['REQUEST_URI'])) : '';
        if (trailingslashit($target) !== trailingslashit($current_url_path)) {
            wp_safe_redirect($target);
            exit;
        }
    }
});

// -----------------------------------------------------------------------------
// Optional: Force WooCommerce's return URL (after payment) to the login page
// -----------------------------------------------------------------------------
add_filter('woocommerce_get_return_url', function ($return_url, $order) {
    if ($order instanceof WC_Order && $order->has_status(array('processing','completed'))) {
        return 'https://your-website/thank-you/';
    }
    return $return_url;
}, 10, 2);


