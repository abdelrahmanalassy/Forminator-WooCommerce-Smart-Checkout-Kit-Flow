<?php
/**
 * Plugin Name: Instant Checkout via Forminator
 * Description: Automatically adds products to the WooCommerce cart on Forminator form submission and redirects users to checkout. Ideal for registration flows.
 * Version: 1.4
 * Author: Abdelrahman Ashraf
 * Author URI: https://www.linkedin.com/in/abdelrahman-ashraf-elassy/
 * Requires Plugins: Forminator, Woocommerce
 * Requires at least: 5.0
 * Tested up to: 6.5
 * Requires PHP: 7.4
 */

// Add GitHub link in plugin list
add_filter('plugin_row_meta', 'forminator_checkout_plugin_meta_links', 10, 2);
function forminator_checkout_plugin_meta_links($links, $file) {
    if (strpos($file, 'forminator-kit-redirect.php') !== false) {
        $new_links = array(
            '<a href="https://github.com/USERNAME/REPO" target="_blank">View on GitHub</a>'
        );
        $links = array_merge($links, $new_links);
    }
    return $links;
}

// Include file that watches new WooCommerce orders
require_once plugin_dir_path(__FILE__) . 'watch-new-order.php';

// Load redirect-final.js on frontend
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

