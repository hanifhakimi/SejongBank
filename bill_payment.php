<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
include 'db_connect.php';

/* NEW: check if this user's card is frozen */
$cardFrozen = false;
$freeze_query = "SELECT is_frozen FROM cards WHERE user_id = $user_id LIMIT 1";
$freeze_result = mysqli_query($conn, $freeze_query);
if ($freeze_result && mysqli_num_rows($freeze_result) > 0) {
    $freeze_row  = mysqli_fetch_assoc($freeze_result);
    $cardFrozen  = ((int)$freeze_row['is_frozen'] === 1);
}

/* Load accounts as before */
$query    = "SELECT account_id, account_type, balance FROM accounts WHERE user_id = $user_id";
$accounts = mysqli_query($conn, $query);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($cardFrozen) {
        // Do not process payment when card is frozen
        $message = "<p style='color:red;'>Your card is frozen. You cannot pay bills. Please unfreeze your card first.</p>";
    } else {

        $account_id  = $_POST['account_id'];
        $bill_type   = $_POST['bill_type'];
        $amount      = floatval($_POST['amount']);
        $entered_pin = $_POST['pin'];

        // PIN CHECK
        $pin_query  = "SELECT pin FROM cards WHERE user_id = $user_id LIMIT 1";
        $pin_result = mysqli_query($conn, $pin_query);
        $pin_row    = mysqli_fetch_assoc($pin_result);

        if ($entered_pin !== $pin_row['pin']) {
            $message = "<p style='color:red;'>Incorrect PIN! Payment denied.</p>";
        } elseif ($amount <= 0) {
            $message = "<p style='color:red;'>Amount must be greater than 0.</p>";
        } else {
            // CHECK BALANCE
            $bal_query  = "SELECT balance FROM accounts WHERE account_id = $account_id AND user_id = $user_id";
            $bal_result = mysqli_query($conn, $bal_query);
            $bal_row    = mysqli_fetch_assoc($bal_result);

            if ($bal_row['balance'] < $amount) {
                $message = "<p style='color:red;'>Insufficient balance.</p>";
            } else {
                // DEDUCT
                mysqli_query(
                    $conn,
                    "UPDATE accounts 
                     SET balance = balance - $amount 
                     WHERE account_id = $account_id AND user_id = $user_id"
                );

                // INSERT RECORD
                $desc = "Bill Payment - $bill_type";
                mysqli_query(
                    $conn,
                    "INSERT INTO transactions (user_id, account_id, type, amount, description)
                     VALUES ($user_id, $account_id, 'Bill Payment', $amount, '$desc')"
                );

                $message = "<p style='color:green;'>Bill payment successful!</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SejongBank | Bill Payment</title>

    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #ff4d4d, #ffffff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* SAME SIZE AS WITHDRAW.PHP */
        .bill-container {
            width: 420px;
            background: rgba(255, 255, 255, 0.88);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.9s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #004b87;
            margin-bottom: 10px;
        }

        .logo span {
            color: #ff4d4d;
        }

        h2 {
            text-align: center;
            margin-bottom: 22px;
            color: #333;
            font-size: 22px;
            font-weight: 600;
        }

        label {
            font-weight: 600;
            margin-top: 10px;
            display: block;
            color: #333;
        }

        /* SAME INPUT DESIGN AS WITHDRAW.PHP */
        select,
        input {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            border-radius: 8px;
            border: 1px solid #bbb;
            font-size: 15px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        select:focus,
        input:focus {
            border-color: #004b87;
            outline: none;
            box-shadow: 0 0 6px rgba(0, 75, 135, 0.3);
        }

        .pin-box {
            letter-spacing: 4px;
            font-size: 20px;
            text-align: center;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 18px;
            background: #004b87;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.25s ease;
        }

        button:hover {
            background: #00345d;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0,0,0,0.15);
        }

        .msg-success {
            color: #0a7a26;
            padding: 10px;
            background: #dfffe6;
            border-left: 5px solid #0a7a26;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .msg-error {
            color: #b00000;
            padding: 10px;
            background: #ffe0e0;
            border-left: 5px solid #b00000;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .back a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #ff4d4d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.25s;
        }

        .back a:hover {
            background: #c20000;
        }
    </style>
</head>

<body>

    <div class="bill-container">
        <div class="logo">Sejong<span>Bank</span></div>
        <h2>Bill Payment</h2>

        <?php
        if (!empty($message)) {
            if (strpos($message, 'successful') !== false) {
                echo "<div class='msg-success'>$message</div>";
            } else {
                echo "<div class='msg-error'>$message</div>";
            }
        }

        if ($cardFrozen) {
            echo "<div class='msg-error'>Your card is frozen. You cannot pay bills.</div>";
        }
        ?>

        <form method="POST">

            <label>Select Account</label>
            <select name="account_id" required <?= $cardFrozen ? 'disabled' : '' ?>>
                <option value="">-- Choose account --</option>
                <?php while ($acc = mysqli_fetch_assoc($accounts)) { ?>
                    <option value="<?= $acc['account_id']; ?>">
                        <?= $acc['account_type']; ?> (RM <?= number_format($acc['balance'], 2); ?>)
                    </option>
                <?php } ?>
            </select>

            <label>Select Bill Type</label>
            <select name="bill_type" required <?= $cardFrozen ? 'disabled' : '' ?>>
                <option value="">-- Choose Bill --</option>
                <option value="Electricity">Electricity</option>
                <option value="Water">Water</option>
                <option value="Internet">Internet</option>
                <option value="Mobile Postpaid">Mobile Postpaid</option>
                <option value="Astro TV">Astro TV</option>
                <option value="Car Loan">Car Loan</option>
            </select>

            <label>Amount (RM)</label>
            <input type="number" name="amount" step="0.01" placeholder="Enter payment amount"
                   required <?= $cardFrozen ? 'disabled' : '' ?>>

            <label>Enter PIN</label>
            <input type="password" name="pin" maxlength="4" minlength="4"
                   class="pin-box" placeholder="••••" pattern="\d{4}"
                   required <?= $cardFrozen ? 'disabled' : '' ?>>

            <button type="submit" <?= $cardFrozen ? 'disabled' : '' ?>>Pay Bill</button>
        </form>

        <div class="back">
            <a href="home.php">← Back to Home</a>
        </div>

    </div>

</body>
</html>
