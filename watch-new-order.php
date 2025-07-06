<?php
// Send data after order is initially processed (even if unpaid)
add_action('woocommerce_checkout_order_processed', 'send_order_data_to_google_sheet', 20, 1);

function send_order_data_to_google_sheet($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    $email       = $order->get_billing_email();
    $amount      = $order->get_total();
    $status      = $order->get_status();
    $orderNumber = $order->get_order_number();

    // Webhook URL to Google Sheets script
    $sheet_url = 'Your Web URL';

    // Prepare payload
    $payload = array(
        'email'    => $email,
        'amount'   => $amount,
        'status'   => $status,
        'order_id' => $orderNumber
    );

    // Send to Google Sheet
    wp_remote_post($sheet_url, array(
        'method'  => 'POST',
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => json_encode($payload),
    ));
}

// Also send data when order status is changed (manually or automatically)
add_action('woocommerce_order_status_changed', 'update_sheet_on_status_change', 10, 4);
function update_sheet_on_status_change($order_id, $old_status, $new_status, $order) {
    $email       = $order->get_billing_email();
    $amount      = $order->get_total();
    $orderNumber = $order->get_order_number();

    $sheet_url = 'Your Web URL';

    $payload = array(
        'email'    => $email,
        'amount'   => $amount,
        'status'   => $new_status, // updated status
        'order_id' => $orderNumber
    );

    wp_remote_post($sheet_url, array(
        'method'  => 'POST',
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => json_encode($payload),
    ));
}
