<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];

/* NEW: check if this user's card is frozen */
$cardFrozen = false;
$freeze_query = "SELECT is_frozen FROM cards WHERE user_id = $user_id LIMIT 1";
$freeze_result = mysqli_query($conn, $freeze_query);
if ($freeze_result && mysqli_num_rows($freeze_result) > 0) {
    $freeze_row  = mysqli_fetch_assoc($freeze_result);
    $cardFrozen = ((int)$freeze_row['is_frozen'] === 1);
}

/* Load accounts as before */
$query  = "SELECT account_id, account_type, balance FROM accounts WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);

$message = "";

/* Handle withdrawal form */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($cardFrozen) {
        // If card is frozen, do NOT process anything
        $message = "<p>Your card is frozen. You cannot withdraw money.</p>";
    } else {

        $account_id = $_POST['account_id'];
        $amount     = floatval($_POST['amount']);

        // Get user PIN from cards table
        $pin_query  = "SELECT pin FROM cards WHERE user_id = $user_id LIMIT 1";
        $pin_result = mysqli_query($conn, $pin_query);
        $pin_row    = mysqli_fetch_assoc($pin_result);

        $stored_pin  = $pin_row['pin'];
        $entered_pin = $_POST['pin'];

        if ($entered_pin !== $stored_pin) {
            echo "<script>alert('Incorrect PIN! Withdrawal denied.'); window.location='withdraw.php';</script>";
            exit;
        }

        if ($amount <= 0) {
            echo "<script>alert('Amount must be greater than 0'); window.location='withdraw.php';</script>";
            exit;
        }

        // Get current balance
        $bal_query  = "SELECT balance FROM accounts WHERE account_id = $account_id AND user_id = $user_id";
        $bal_result = mysqli_query($conn, $bal_query);
        $bal_row    = mysqli_fetch_assoc($bal_result);

        $current_balance = $bal_row['balance'];

        if ($amount > $current_balance) {
            echo "<script>alert('Insufficient balance!'); window.location='withdraw.php';</script>";
            exit;
        }

        // Deduct amount
        $update = "UPDATE accounts 
                   SET balance = balance - $amount 
                   WHERE account_id = $account_id AND user_id = $user_id";
        mysqli_query($conn, $update);

        // Insert into transactions table
        $insert_tx = "INSERT INTO transactions (user_id, account_id, type, amount, description)
                      VALUES ($user_id, $account_id, 'Withdrawal', $amount, 'Account withdrawal')";
        mysqli_query($conn, $insert_tx);

        $message = "<p>Withdrawal successful!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SejongBank | Withdrawal</title>

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

        .withdraw-container {
            width: 520px;
            background: rgba(255, 255, 255, 0.95);
            padding: 45px 50px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.22);
            text-align: center;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #004b87;
        }

        .logo span {
            color: #ff4d4d;
        }

        h2 {
            margin-top: 0;
            margin-bottom: 28px;
            font-size: 24px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 6px;
            margin-top: 18px;
            font-weight: 600;
            font-size: 15px;
            color: #333;
        }

        select,
        input {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #bbb;
            font-size: 16px;
            box-sizing: border-box;
        }

        .pin-box {
            letter-spacing: 4px;
            font-size: 20px;
            text-align: center;
        }

        button {
            width: 100%;
            padding: 16px;
            margin-top: 26px;
            background: #004b87;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #00345d;
        }

        .back {
            margin-top: 15px;
        }

        .back a {
            color: #ff4d4d;
            text-decoration: none;
        }

        .back a:hover {
            text-decoration: underline;
        }

        .account-preview {
            margin-top: 20px;
            padding: 22px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
            display: none;
            animation: slideUp 0.4s ease;
            text-align: center;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .account-title {
            font-size: 18px;
            font-weight: bold;
            color: #004b87;
        }

        .balance-text {
            margin-top: 6px;
            font-size: 15px;
            color: #444;
        }

        .bar-container {
            width: 100%;
            height: 12px;
            background: #f1f1f1;
            border-radius: 8px;
            margin-top: 10px;
        }

        .bar-fill {
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, #ff4d4d, #ff7a7a);
            border-radius: 8px;
            transition: width 0.35s ease;
        }

        .msg-success {
            color: #0a7a26;
            padding: 10px;
            background: #dfffe6;
            border-left: 5px solid #0a7a26;
            margin-bottom: 15px;
            border-radius: 6px;
            font-size: 15px;
        }

        .msg-error {
            color: #b00000;
            padding: 10px;
            background: #ffe0e0;
            border-left: 5px solid #b00000;
            margin-bottom: 15px;
            border-radius: 6px;
            font-size: 15px;
        }
    </style>

</head>

<body>

    <div class="withdraw-container">

        <div class="logo">Sejong<span>Bank</span></div>
        <h2>Withdraw Money</h2>

        <?php
        if (!empty($message)) {
            if (strpos($message, 'successful') !== false) {
                echo "<div class='msg-success'>$message</div>";
            } else {
                echo "<div class='msg-error'>$message</div>";
            }
        }

        if ($cardFrozen) {
            echo "<div class='msg-error'>Your card is frozen. You cannot withdraw money.</div>";
        }
        ?>

        <form method="POST">

            <label>Select Account</label>
            <select name="account_id" id="accountSelect" required <?= $cardFrozen ? 'disabled' : '' ?>>
                <option disabled selected>-- Choose account --</option>

                <?php mysqli_data_seek($result, 0);
                while ($row = mysqli_fetch_assoc($result)): ?>
                    <option value="<?= $row['account_id']; ?>" data-type="<?= $row['account_type']; ?>"
                        data-balance="<?= $row['balance']; ?>">
                        <?= $row['account_type']; ?> (RM <?= number_format($row['balance'], 2); ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="account-preview" id="previewCard">
                <div class="account-title" id="previewType">Account</div>
                <div class="balance-text" id="previewBalance">Balance: RM 0.00</div>

                <div class="bar-container">
                    <div class="bar-fill" id="balanceBar"></div>
                </div>
            </div>

            <label>Amount (RM)</label>
            <input type="number" step="0.01" min="0" id="amountInput" name="amount"
                   required <?= $cardFrozen ? 'disabled' : '' ?>>

            <label>Enter PIN</label>
            <input type="password" maxlength="4" minlength="4" name="pin" class="pin-box" placeholder="••••"
                   pattern="\d{4}" required <?= $cardFrozen ? 'disabled' : '' ?>>

            <button type="submit" <?= $cardFrozen ? 'disabled' : '' ?>>Confirm Withdrawal</button>
        </form>

        <div class="back">
            <a href="home.php">← Back to Home</a>
        </div>
    </div>

    <script>
        const accountSelect   = document.getElementById("accountSelect");
        const previewCard     = document.getElementById("previewCard");
        const previewType     = document.getElementById("previewType");
        const previewBalance  = document.getElementById("previewBalance");
        const balanceBar      = document.getElementById("balanceBar");
        const amountInput     = document.getElementById("amountInput");

        let currentBalance = 0;

        if (accountSelect) {
            accountSelect.addEventListener("change", function () {
                const selected = this.options[this.selectedIndex];
                const type = selected.dataset.type;
                currentBalance = parseFloat(selected.dataset.balance);

                previewType.textContent = type;
                previewBalance.textContent = "Balance: RM " + currentBalance.toFixed(2);

                previewCard.style.display = "block";
                balanceBar.style.width = "100%";
            });
        }

        if (amountInput) {
            amountInput.addEventListener("input", function () {
                const amount = parseFloat(this.value) || 0;
                let percent = 100 - ((amount / currentBalance) * 100);

                if (percent < 0) percent = 0;
                if (percent > 100) percent = 100;

                balanceBar.style.width = percent + "%";
            });
        }
    </script>

</body>
</html>
