<?php
/**
 * Send WooCommerce order data to Google Sheets using Google Apps Script
 * - On order creation (checkout)
 * - On order status change
 */
 
// Trigger when order is processed at checkout (even if unpaid)
add_action('woocommerce_checkout_order_processed', 'send_order_data_to_google_sheet', 20, 1);

function send_order_data_to_google_sheet($order_id) {
    // Get the order object
    $order = wc_get_order($order_id);
    if (!$order) return;

    // Extract basic order info
    $order_email  = $order->get_billing_email();
    $order_total  = $order->get_total();
    $order_status = $order->get_status();

    // URL of your Google Apps Script (acts as webhook)
    $webhook_url = 'https://script.google.com/macros/s/AKfycbwrhwYdsXsu70kH_4MNNbFm6W5CVC5SzjcF63ZAS308BEuvZ1JbFRz2i4LAuQe7Wf8Khw/exec';

    // Format the data to send
    $data = [
        'email'    => $order_email,
        'amount'   => $order_total,
        'status'   => $order_status,
        'order_id' => $order_id
    ];

    // Set up POST request with JSON body
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ]
    ];

    // Send data to Google Sheet
    $context  = stream_context_create($options);
    $result = file_get_contents($webhook_url, false, $context);

    // Log result to debug.log
    if ($result === FALSE) {
        error_log("Failed to send data to Google Sheet");
    } else {
        error_log("Data sent to Google Sheet successfully: " . $result);
    }
}

// Trigger when order status changes (e.g., from processing to completed)
add_action('woocommerce_order_status_changed', 'update_sheet_on_status_change', 10, 4);
function update_sheet_on_status_change($order_id, $old_status, $new_status, $order) {
    // Extract updated order details
    $email       = $order->get_billing_email();
    $amount      = $order->get_total();
    $orderNumber = $order->get_order_number();

    // Same Apps Script URL
    $sheet_url = 'https://script.google.com/macros/s/AKfycbwrhwYdsXsu70kH_4MNNbFm6W5CVC5SzjcF63ZAS308BEuvZ1JbFRz2i4LAuQe7Wf8Khw/exec';

    // Payload with new status
    $payload = array(
        'email'    => $email,
        'amount'   => $amount,
        'status'   => $new_status, // updated status
        'order_id' => $orderNumber
    );

    // Send the data via wp_remote_post (recommended over file_get_contents for WP)
    wp_remote_post($sheet_url, array(
        'method'  => 'POST',
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => json_encode($payload),
    ));
}
