<?php

function safe2pay_notifications_css() {
    $css = <<<EOT
    <style type="text/css">
        body{
            font-family: Calibri,Arial,sans-serif;
        }
    </style>
EOT;
    return $css;
}

function safe2pay_notifications_send_successful_purchase_email($addr, $subject, $purchase) {
    $ref = $purchase->payload->reference;
    if (substr($ref, -9, 1) == '-') {
        $ref = substr($ref, 0, -9);
    }
    if (function_exists("wc_sequential_order_numbers")) {
        $order_id = wc_sequential_order_numbers()->find_order_by_order_number($ref);
    } else {
        $order_id = $ref;
    }
    $order = wc_get_order($order_id);
    if ($order) {
        $order->update_status('processing');
        $time = (new DateTime($purchase->payload->transaction_date))->format('d/m/Y H:m');
        $amount = number_format($purchase->payload->decimal_amount, 2, '.', '');
        $order_url = '<a href="' . $order->get_edit_order_url() . '">' . $order->get_order_number() . '</a>';
        $body = "<html><head>" . safe2pay_notifications_css() . "</head><body>";
        $body .= <<<EOT
    <div>
        <div>
            <div>
                <h2>Payment Details</h2>
                <p>
Order: $order_url<br/>                    
Reference Number: $ref</br>
Date: $time</br>
                </p>
            </div>
        </div>
        <div>
            <div>
                <table border="1">
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Sub Total</th>
                    </tr>
EOT;
        foreach ($order->get_items() as $item_key => $item):
            $item_name = $item->get_name(); // Name of the product
            $quantity = $item->get_quantity();
            $line_total = number_format($item->get_total(), 2, '.', '');
            $body .= <<<EOT
                <tr>
    <td>$item_name</td>
    <td>$quantity</td>
    <td style="text-align: right;">\$$line_total</td>
    </tr>
EOT;
        endforeach;
        $body .= <<<EOT
    <tr>
    <td></td>
    <td><strong>Total</strong></td>
    <td style="text-align: right;"><strong>\${$amount}</strong></td>
    </tr>

    </table>
    </div>
    <div>
    <p><strong>LEGAL DISCLAIMER</strong><br/>Safe2Pay has made every attempt to ensure the accuracy and reliability of the information provided on this email. However, the information is provided "as is" without warranty of any kind. Safe2Pay does not accept any responsibility or liability for the accuracy, content, completeness, legality, or reliability of the information contained on this email. You are advised to compare the contents of this email to your merchant dashboard.
    </p>
    </div>
    </div>
    </div>
    </body>
    </html>
EOT;

        wp_mail($addr, $subject, $body, array('Content-Type: text/html; charset=UTF-8'));
    } else {
        // send unmatched payment notification email
    }
}

function safe2pay_notifications_send_failed_purchase_email($addr, $subject, $purchase) {
    $ref = $purchase->payload->reference;
    if (substr($ref, -9, 1) == '-') {
        $ref = substr($ref, 0, -9);
    }
    $message = $purchase->payload->response_code . " - " . $purchase->payload->message;
    $order = wc_get_order($ref);
    if ($order) {
        $time = (new DateTime($purchase->payload->transaction_date))->format('d/m/Y H:m');
        $amount = number_format($purchase->payload->decimal_amount, 2, '.', '');
        $order_url = '<a href="' . $order->get_edit_order_url() . '">' . $order->get_order_number() . '</a>';
        $body = "<html><head>" . safe2pay_notifications_css() . "</head><body>";
        $body .= <<<EOT
    <div>
        <div>
            <div>
                <h2 style="color: red;">Failed Payment Details</h2>
                <p>
Order: $order_url<br/>                    
Reference Number: $ref</br>
Date: $time</br>
                </p>
            </div>
        </div>
        <div>
            <div>
                <table border="1">
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Sub Total</th>
                    </tr>
EOT;
        foreach ($order->get_items() as $item_key => $item):
            $item_name = $item->get_name(); // Name of the product
            $quantity = $item->get_quantity();
            $line_total = number_format($item->get_total(), 2, '.', '');
            $body .= <<<EOT
                <tr>
    <td>$item_name</td>
    <td>$quantity</td>
    <td style="text-align: right;">\$$line_total</td>
    </tr>
EOT;
        endforeach;
        $body .= <<<EOT
    <tr>
    <td></td>
    <td><strong>Total</strong></td>
    <td style="text-align: right;"><strong>\${$amount}</strong></td>
    </tr>

    </table>
    </div>
    <div style="color: red;">
    <h2>Payment for this payment failed with the following message:</h2>
    <pre>$message</pre>
    </div>
    <div>
    <p><strong>LEGAL DISCLAIMER</strong><br/>Safe2Pay has made every attempt to ensure the accuracy and reliability of the information provided on this email. However, the information is provided "as is" without warranty of any kind. Safe2Pay does not accept any responsibility or liability for the accuracy, content, completeness, legality, or reliability of the information contained on this email. You are advised to compare the contents of this email to your merchant dashboard.
    </p>
    </div>
    </div>
    </div>
    </body>
    </html >
EOT;

        wp_mail($addr, $subject, $body, array('Content-Type: text/html; charset=UTF-8'));
    }
}
