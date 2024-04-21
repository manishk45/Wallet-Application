<?php
/*
Plugin Name: Wallet Application
Description: A simple wallet application to track credit and debit transactions.
Version: 1.0
Author: Your Name
*/

defined('ABSPATH') or die('Direct script access disallowed.');

register_activation_hook(__FILE__, 'wallet_app_install');

function wallet_app_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'wallet_transactions';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        wallet_id mediumint(9) NOT NULL,
        type varchar(10) NOT NULL,
        amount int(11) NOT NULL,
        description varchar(255) NOT NULL,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function wallet_app_balance_shortcode($atts) {
    global $wpdb;

    $atts = shortcode_atts(array(
        'wallet_id' => 1,
    ), $atts);

    $table_name = $wpdb->prefix . 'wallet_transactions';

    $balance = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $table_name WHERE wallet_id = %d", $atts['wallet_id']));

    if (!$balance) {
        $balance = 10000; // Assuming a starting balance of Rs. 10,000
    }

    return '<div class="wallet-balance wallet-balance-' . $atts['wallet_id'] . '">Your Wallet ' . $atts['wallet_id'] . ' Balance: <span>Rs. ' . $balance . '</span></div>';
}

add_shortcode('wallet_balance', 'wallet_app_balance_shortcode');

function wallet_app_credit_shortcode($atts) {
    ob_start();
    ?>
    <form method="post" class="wallet-credit-form wallet-credit-form-<?php echo $atts['wallet_id']; ?>">
        <label for="credit_amount_<?php echo $atts['wallet_id']; ?>">Credit Amount (Rs.):</label><br>
        <input type="number" id="credit_amount_<?php echo $atts['wallet_id']; ?>" name="credit_amount" required><br>
        <label for="credit_description_<?php echo $atts['wallet_id']; ?>">Description:</label><br>
        <input type="text" id="credit_description_<?php echo $atts['wallet_id']; ?>" name="credit_description" required><br><br>
        <input type="hidden" name="wallet_id" value="<?php echo $atts['wallet_id']; ?>">
        <button type="submit" class="wallet-credit-btn wallet-credit-btn-<?php echo $atts['wallet_id']; ?>">Credit Wallet</button>
        <p class="wallet-prompt wallet-prompt-<?php echo $atts['wallet_id']; ?>"></p>
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credit_amount']) && isset($_POST['credit_description'])) {
        $amount = intval($_POST['credit_amount']);
        $description = sanitize_text_field($_POST['credit_description']);
        $wallet_id = intval($_POST['wallet_id']);
        if ($amount > 0) {
            wallet_app_credit($wallet_id, $amount, $description);
            echo '<script>document.querySelector(".wallet-prompt-' . $atts['wallet_id'] . '").innerHTML = "Credited Rs. ' . $amount . ' with the description: ' . $description . '";</script>';
        } else {
            echo '<script>document.querySelector(".wallet-prompt-' . $atts['wallet_id'] . '").innerHTML = "Error: Amount must be a positive integer.";</script>';
        }
    }
    return ob_get_clean();
}

add_shortcode('wallet_credit', 'wallet_app_credit_shortcode');

function wallet_app_debit_shortcode($atts) {
    ob_start();
    ?>
    <form method="post" class="wallet-debit-form wallet-debit-form-<?php echo $atts['wallet_id']; ?>">
        <label for="debit_amount_<?php echo $atts['wallet_id']; ?>">Debit Amount (Rs.):</label><br>
        <input type="number" id="debit_amount_<?php echo $atts['wallet_id']; ?>" name="debit_amount" required><br>
        <label for="debit_description_<?php echo $atts['wallet_id']; ?>">Description:</label><br>
        <input type="text" id="debit_description_<?php echo $atts['wallet_id']; ?>" name="debit_description" required><br><br>
        <input type="hidden" name="wallet_id" value="<?php echo $atts['wallet_id']; ?>">
        <button type="submit" class="wallet-debit-btn wallet-debit-btn-<?php echo $atts['wallet_id']; ?>">Debit Wallet</button>
        <p class="wallet-prompt wallet-prompt-<?php echo $atts['wallet_id']; ?>"></p>
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['debit_amount']) && isset($_POST['debit_description'])) {
        $amount = intval($_POST['debit_amount']);
        $description = sanitize_text_field($_POST['debit_description']);
        $wallet_id = intval($_POST['wallet_id']);
        if ($amount > 0) {
            if (wallet_app_debit($wallet_id, $amount, $description)) {
                echo '<script>document.querySelector(".wallet-prompt-' . $atts['wallet_id'] . '").innerHTML = "Debited Rs. ' . $amount . ' with the description: ' . $description . '";</script>';
            } else {
                echo '<script>document.querySelector(".wallet-prompt-' . $atts['wallet_id'] . '").innerHTML = "Error: Insufficient funds.";</script>';
            }
        } else {
            echo '<script>document.querySelector(".wallet-prompt-' . $atts['wallet_id'] . '").innerHTML = "Error: Amount must be a positive integer.";</script>';
        }
    }
    return ob_get_clean();
}

add_shortcode('wallet_debit', 'wallet_app_debit_shortcode');

function wallet_app_credit($wallet_id, $amount, $description) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'wallet_transactions';

    $wpdb->insert(
        $table_name,
        array(
            'wallet_id' => $wallet_id,
            'type' => 'Credit',
            'amount' => $amount,
            'description' => $description,
            'timestamp' => current_time('mysql')
        )
    );

    // Update balance
    $balance = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $table_name WHERE wallet_id = %d", $wallet_id));
    update_option('wallet_balance_' . $wallet_id, $balance);
}

function wallet_app_debit($wallet_id, $amount, $description) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'wallet_transactions';

    $balance = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $table_name WHERE wallet_id = %d", $wallet_id));

    if (!$balance) {
        $balance = 10000; // Assuming a starting balance of Rs. 10,000
    }

    if ($balance >= $amount) {
        $wpdb->insert(
            $table_name,
            array(
                'wallet_id' => $wallet_id,
                'type' => 'Debit',
                'amount' => -$amount,
                'description' => $description,
                'timestamp' => current_time('mysql')
            )
        );

        // Update balance
        $balance = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $table_name WHERE wallet_id = %d", $wallet_id));
        update_option('wallet_balance_' . $wallet_id, $balance);
        
        return true;
    } else {
        return false;
    }
}

function wallet_app_transactions($atts) {
    global $wpdb;

    $atts = shortcode_atts(array(
        'wallet_id' => 1,
    ), $atts);

    $table_name = $wpdb->prefix . 'wallet_transactions';

    $transactions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE wallet_id = %d ORDER BY timestamp DESC", $atts['wallet_id']));

    echo '<div class="wallet-transactions wallet-transactions-' . $atts['wallet_id'] . '">';
    echo '<h2>Transaction Details for Wallet ' . $atts['wallet_id'] . '</h2>';
    echo '<table>';
    echo '<tr><th>Type</th><th>Amount</th><th>Description</th><th>Date</th></tr>';
    foreach ($transactions as $transaction) {
        echo '<tr>';
        echo '<td>' . $transaction->type . '</td>';
        echo '<td>' . $transaction->amount . '</td>';
        echo '<td style="font-size: 14px;">' . $transaction->description . '</td>';
        echo '<td>' . $transaction->timestamp . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
}

add_shortcode('wallet_transactions', 'wallet_app_transactions');

// Add custom stylesheet
function wallet_application_styles() {
    ?>
    <style>
        .wallet-balance,
        .wallet-credit-form,
        .wallet-debit-form,
        .wallet-transactions,
        .wallet-prompt {
            margin-bottom: 20px;
        }

        .wallet-balance span {
            font-weight: bold;
            color: #0d6efd;
        }

        .wallet-credit-form label,
        .wallet-debit-form label {
            margin-bottom: 5px;
            display: block;
            font-weight: bold;
        }

        .wallet-credit-form input[type="number"],
        .wallet-debit-form input[type="number"],
        .wallet-credit-form input[type="text"],
        .wallet-debit-form input[type="text"],
        .wallet-credit-form button,
        .wallet-debit-form button {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        .wallet-credit-form button,
        .wallet-debit-form button {
            background-color: #0d6efd;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .wallet-credit-form button:hover,
        .wallet-debit-form button:hover {
            background-color: #0b5ed7;
        }

        .wallet-transactions table {
            width: 100%;
            border-collapse: collapse;
        }

        .wallet-transactions th,
        .wallet-transactions td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .wallet-transactions th {
            background-color: #f2f2f2;
        }

        .wallet-prompt {
            color: green;
            margin-top: 10px;
        }

        .wallet-prompt.error {
            color: red;
        }

        /* Wallet 1 color scheme */
        .wallet-balance-1,
        .wallet-credit-form-1,
        .wallet-debit-form-1,
        .wallet-transactions-1,
        .wallet-prompt-1 {
            background-color: #ffe6e6;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
        }

        /* Wallet 1 font-size */
        .wallet-transactions-1 td {
            font-size: 14px;
        }

        /* Wallet 2 color scheme */
        .wallet-balance-2,
        .wallet-credit-form-2,
        .wallet-debit-form-2,
        .wallet-transactions-2,
        .wallet-prompt-2 {
            background-color: #e7f5ff;
            border: 1px solid #bee5eb;
            padding: 10px;
            border-radius: 5px;
        }

        /* Wallet 2 font-size */
        .wallet-transactions-2 td {
            font-size: 14px;
        }

        /* Wallet 3 color scheme */
        .wallet-balance-3,
        .wallet-credit-form-3,
        .wallet-debit-form-3,
        .wallet-transactions-3,
        .wallet-prompt-3 {
            background-color: #e6fbea;
            border: 1px solid #c3f9d8;
            padding: 10px;
            border-radius: 5px;
        }

        /* Wallet 3 font-size */
        .wallet-transactions-3 td {
            font-size: 14px;
        }
    </style>
    <?php
}

add_action('wp_head', 'wallet_application_styles');
