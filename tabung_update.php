<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$user_id = (int)$_SESSION['user_id'];
$piggybank_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- Get tabung info (for display) ---
$piggy_sql = "SELECT * FROM piggybank 
              WHERE piggybank_id = $piggybank_id AND user_id = $user_id";
$piggy_res = mysqli_query($conn, $piggy_sql);
$piggy = mysqli_fetch_assoc($piggy_res);

if (!$piggy) {
    die("Tabung not found.");
}

// --- Get all accounts for this user (Sejong Wallet + Personal Saver) ---
$accounts_sql = "SELECT account_id, account_type, account_number, balance
                 FROM accounts
                 WHERE user_id = $user_id";
$accounts_res = mysqli_query($conn, $accounts_sql);

$accounts = [];
while ($row = mysqli_fetch_assoc($accounts_res)) {
    $accounts[] = $row;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = (float)$_POST['amount'];
    $source_account_id = (int)$_POST['source_account_id'];

    if ($amount <= 0) {
        $error = "Amount must be greater than 0.";
    } elseif ($source_account_id <= 0) {
        $error = "Please choose an account.";
    } else {
        // Get selected account and verify it belongs to this user
        $acc_sql = "SELECT account_id, balance 
                    FROM accounts 
                    WHERE account_id = $source_account_id 
                      AND user_id = $user_id
                    LIMIT 1";
        $acc_res = mysqli_query($conn, $acc_sql);
        $account = mysqli_fetch_assoc($acc_res);

        if (!$account) {
            $error = "Invalid account selected.";
        } else {
            $current_balance = (float)$account['balance'];

            if ($current_balance < $amount) {
                $error = "Insufficient balance in selected account.";
            } else {
                // Deduct from account, add to tabung, and insert transaction
                mysqli_begin_transaction($conn);

                // 1) Deduct from selected account
                $update_account = "UPDATE accounts
                                   SET balance = balance - $amount
                                   WHERE account_id = " . (int)$account['account_id'];

                // 2) Add to piggybank
                $update_piggy = "UPDATE piggybank
                                 SET current_amount = current_amount + $amount
                                 WHERE piggybank_id = $piggybank_id
                                   AND user_id = $user_id";

                // 3) Insert into transactions (for history)
                $tx_type = 'Tabung Transfer';
                $desc_text = 'Transfer to tabung ' . $piggy['piggybank_name'];
                $desc = mysqli_real_escape_string($conn, $desc_text);

                $insert_tx = "INSERT INTO transactions 
                              (user_id, account_id, type, amount, description)
                              VALUES ($user_id, $source_account_id, '$tx_type', $amount, '$desc')";

                $ok1 = mysqli_query($conn, $update_account);
                $ok2 = mysqli_query($conn, $update_piggy);
                $ok3 = mysqli_query($conn, $insert_tx);

                if ($ok1 && $ok2 && $ok3) {
                    mysqli_commit($conn);
                    header("Location: tabung.php");
                    exit;
                } else {
                    mysqli_rollback($conn);
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Money</title>
<style>
    body { font-family: Arial; background: #f3f3f3; }
    input, button, select {
        width: 100%; padding: 10px; margin-top: 10px;
        border-radius: 5px; border: 1px solid #ccc;
    }
    button {
        background: #ffaa00; color: white; border: none;
    }
    .info-box {
        background:#f7f7f7;
        padding:10px;
        border-radius:6px;
        margin-bottom:10px;
        font-size:14px;
    }
    .error {
        margin-top: 10px;
        padding: 10px;
        background: #ffe0e0;
        color: #b00000;
        border-radius: 5px;
    }
</style>
</head>
<body style="
    margin:0;
    font-family:Arial;
    background: linear-gradient(135deg, #ff4d4d 0%, #ffffff 100%);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
">

<div style="
    width:600px;
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
">

    <h2 style="color:#ffaa00; text-align:center; margin-bottom:10px;">Add Money to Tabung</h2>

    <div class="info-box">
        <strong>Tabung:</strong> <?php echo htmlspecialchars($piggy['piggybank_name']); ?><br>
        <strong>Current Saved:</strong> RM <?php echo number_format($piggy['current_amount'], 2); ?><br>
        <strong>Target:</strong> RM <?php echo number_format($piggy['target_amount'], 2); ?>
    </div>

    <?php if (!empty($error)) { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">
        <label>From Account</label>
        <select name="source_account_id" required>
            <option value="">-- Select account --</option>
            <?php foreach ($accounts as $acc) { ?>
                <option value="<?php echo $acc['account_id']; ?>">
                    <?php echo htmlspecialchars($acc['account_type']); ?>
                    (<?php echo $acc['account_number']; ?>) - 
                    RM <?php echo number_format($acc['balance'], 2); ?>
                </option>
            <?php } ?>
        </select>

        <label>Amount (RM)</label>
        <input type="number" step="0.01" name="amount" required
               style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd; margin-top:10px;">

        <button type="submit" style="
            width:100%;
            margin-top:25px;
            background:#ffaa00;
            padding:14px;
            border:none;
            color:white;
            font-weight:bold;
            border-radius:8px;
            cursor:pointer;
            font-size:15px;
            box-shadow:0 2px 5px rgba(0,0,0,0.2);
        ">Add Money</button>
    </form>

</div>

</body>
</html>
