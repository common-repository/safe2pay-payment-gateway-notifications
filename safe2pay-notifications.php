<?php
/*
  Plugin Name: Safe2Pay Payment Gateway Notifications
  Plugin URI: http://www.safe2pay.com.au/
  Description: Sends notifications for payment gateway events
  Version: 1.23
  Author: Iman Biglari, DataQuest PTY Ltd.
  Author URI: http://www.dataquest.com.au/
 */

if (!defined("ABSPATH")) {
    exit;
}
require_once(ABSPATH . "wp-includes/pluggable.php");
include_once("safe2pay-notifications-emails.php");

define("SAFE2PAY_NOTIFICATIONS_HOOK_URL", "/safe2payhook");
define("SAFE2PAY_NOTIFICATIONS_SETTING_GROUP", "safe2pay-notifications-settings-group");
define("SAFE2PAY_NOTIFICATIONS_NOTIFY_EMAIL", "safe2pay_notifications_notify_email");
define("SAFE2PAY_NOTIFICATIONS_SETTINGS", "safe2pay_notifications_settings");
define("SAFE2PAY_NOTIFICATION_EVENT_SUCCESSFUL_PURCHASE", "safe2pay_notifications_event_successful_purchase");
define("SAFE2PAY_NOTIFICATION_EVENT_FAILED_PURCHASE", "safe2pay_notification_event_failed_purchase");
define("SAFE2PAY_NOTIFICATION_USER_NAME", "safe2pay_notifications_username");
define("SAFE2PAY_NOTIFICATION_PASSWORD", "safe2pay_notifications_password");

function safe2pay_notifications_filter_input_fix($type, $variable_name, $filter = FILTER_DEFAULT, $options = NULL) {
    $checkTypes = [
        INPUT_GET,
        INPUT_POST,
        INPUT_COOKIE 
    ];

    if ($options === NULL) {
        $options = FILTER_NULL_ON_FAILURE;
    }
        
    if (in_array($type, $checkTypes) || filter_has_var($type, $variable_name)) {
        return sanitize_text_field(filter_input($type, $variable_name, $filter, $options));
    } else if ($type == INPUT_SERVER && isset($_SERVER[$variable_name])) {
        return sanitize_text_field(filter_var($_SERVER[$variable_name], $filter, $options));
    } else if ($type == INPUT_ENV && isset($_ENV[$variable_name])) {
        return sanitize_text_field(filter_var($_ENV[$variable_name], $filter, $options));
    } else {
        return NULL;
    }
}

function safe2pay_notifications_url_handler() {
    if ((safe2pay_notifications_filter_input_fix(INPUT_SERVER, "REQUEST_URI") === SAFE2PAY_NOTIFICATIONS_HOOK_URL) && (safe2pay_notifications_filter_input_fix(INPUT_SERVER, 'REQUEST_METHOD') === 'POST')) {
        $body = file_get_contents('php://input');
        $payload = json_decode($body);
        do_action("safe2pay_notifications_hook", $payload);
        die();
    } else {
        
    }
}

function safe2pay_notifications_hook($payload) {
    $email = get_option(SAFE2PAY_NOTIFICATIONS_NOTIFY_EMAIL);
    if (empty($email)) {
        return;
    }
    $username = get_option(SAFE2PAY_NOTIFICATION_USER_NAME);
    if (!empty($username)) {
        $password = get_option(SAFE2PAY_NOTIFICATION_PASSWORD);
        if (empty($password)) {
            return;
        }
        $user = sanitize_user($_SERVER['PHP_AUTH_USER']);
        $pwd = $_SERVER['PHP_AUTH_PW'];

        if (($user != $username) || ($pwd != $password)) {
            header('WWW-Authenticate: Basic realm="Safe2Pay Gateway"');
            header('HTTP/1.0 401 Unauthorized');
            die;
        }
    }

    switch ($payload->event) {
        case "purchase:success":
            if (get_option(SAFE2PAY_NOTIFICATION_EVENT_SUCCESSFUL_PURCHASE) == "1") {
                safe2pay_notifications_send_successful_purchase_email($email, "Successful Payment Received", $payload);
            }
            break;
        case "purchase:failed":
            if (get_option(SAFE2PAY_NOTIFICATION_EVENT_FAILED_PURCHASE) == "1") {
                safe2pay_notifications_send_failed_purchase_email($email, "Failed Payment Notification", $payload);
            }
            break;
        case "refund:success":
            // send refund successful email
            break;
        case "refund:failed":
            // send refund failed email
            break;
        default:
            ;
    }
    return;
}

function safe2pay_notifications_register_settings() {
    //register our settings
    register_setting(SAFE2PAY_NOTIFICATIONS_SETTING_GROUP, SAFE2PAY_NOTIFICATIONS_NOTIFY_EMAIL);
    register_setting(SAFE2PAY_NOTIFICATIONS_SETTING_GROUP, SAFE2PAY_NOTIFICATION_EVENT_SUCCESSFUL_PURCHASE);
    register_setting(SAFE2PAY_NOTIFICATIONS_SETTING_GROUP, SAFE2PAY_NOTIFICATION_EVENT_FAILED_PURCHASE);
    register_setting(SAFE2PAY_NOTIFICATIONS_SETTING_GROUP, SAFE2PAY_NOTIFICATION_USER_NAME);
    register_setting(SAFE2PAY_NOTIFICATIONS_SETTING_GROUP, SAFE2PAY_NOTIFICATION_PASSWORD);
}

function safe2pay_notifications_settings_page() {
    ?>
    <div class="wrap">
        <h1>Safe2Pay Notifications</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields(SAFE2PAY_NOTIFICATIONS_SETTING_GROUP);
            do_settings_sections(SAFE2PAY_NOTIFICATIONS_SETTING_GROUP);
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Notification Email Address</th>
                    <td><input type="email" placeholder="admin@example.com" required style="width:85%;" name="<?= SAFE2PAY_NOTIFICATIONS_NOTIFY_EMAIL ?>" value="<?php echo esc_attr(get_option(SAFE2PAY_NOTIFICATIONS_NOTIFY_EMAIL)); ?>" required/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Events</th>
                    <td>
                        <input type="checkbox" id="<?= SAFE2PAY_NOTIFICATION_EVENT_SUCCESSFUL_PURCHASE ?>" name="<?= SAFE2PAY_NOTIFICATION_EVENT_SUCCESSFUL_PURCHASE ?>" value="1" <?php echo checked(1, get_option(SAFE2PAY_NOTIFICATION_EVENT_SUCCESSFUL_PURCHASE), false); ?> /> <label for="<?= SAFE2PAY_NOTIFICATION_EVENT_SUCCESSFUL_PURCHASE ?>">Successful Purchase</label><br/>
                        <input type="checkbox" id="<?= SAFE2PAY_NOTIFICATION_EVENT_FAILED_PURCHASE ?>" name="<?= SAFE2PAY_NOTIFICATION_EVENT_FAILED_PURCHASE ?>" value="1" <?php echo checked(1, get_option(SAFE2PAY_NOTIFICATION_EVENT_FAILED_PURCHASE), false); ?> /> <label for="<?= SAFE2PAY_NOTIFICATION_EVENT_FAILED_PURCHASE ?>">Failed Purchase</label><br/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">User ID</th>
                    <td>
                        <input type="text" id="<?= SAFE2PAY_NOTIFICATION_USER_NAME ?>" name="<?= SAFE2PAY_NOTIFICATION_USER_NAME ?>" value="<?php echo esc_attr(get_option(SAFE2PAY_NOTIFICATION_USER_NAME)); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Password</th>
                    <td>
                        <input type="password" id="<?= SAFE2PAY_NOTIFICATION_PASSWORD ?>" name="<?= SAFE2PAY_NOTIFICATION_PASSWORD ?>" value="<?php echo esc_attr(get_option(SAFE2PAY_NOTIFICATION_PASSWORD)); ?>" />
                    </td>
                </tr>
            </table>

            <?php submit_button();
            ?>

        </form>
    </div>
    <?php
}

function safe2pay_notifications_settings() {
    add_menu_page("Safe2Pay Notifications", "Safe2Pay Notifications", "administrator", __FILE__, "safe2pay_notifications_settings_page", "dashicons-megaphone");
    add_action("admin_init", "safe2pay_notifications_register_settings");
}

function safe2pay_notifications_init() {
// If the parent WC_Payment_Gateway class doesn't exist
// it means WooCommerce is not installed on the site
// so do nothing
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    add_action('parse_request', 'safe2pay_notifications_url_handler');
    add_action('safe2pay_notifications_hook', 'safe2pay_notifications_hook', 10, 1);
    add_action("admin_menu", "safe2pay_notifications_settings");
}

add_action('plugins_loaded', 'safe2pay_notifications_init', 0);
