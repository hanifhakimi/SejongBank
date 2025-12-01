<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Fetch user accounts
$query = "SELECT account_id, account_type, balance FROM accounts WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);

$message = ""; // store success/error message

// Handle deposit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = $_POST['account_id'];
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $message = "<p style='color:red;'>Amount must be greater than 0.</p>";
    } else {

        // Update balance
        $update = "UPDATE accounts 
                   SET balance = balance + $amount 
                   WHERE account_id = $account_id AND user_id = $user_id";
        mysqli_query($conn, $update);

        // Insert transaction
        $insert_tx = "INSERT INTO transactions (user_id, account_id, type, amount, description)
                      VALUES ($user_id, $account_id, 'Deposit', $amount, 'Account deposit')";
        mysqli_query($conn, $insert_tx);

        $message = "<p>Deposit successful!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SejongBank | Deposit</title>
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

        .deposit-container {
            width: 420px;
            background: rgba(255, 255, 255, 0.88);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.9s ease;
            text-align: center; /* center the title + message */
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

        .logo span { color: #ff4d4d; }

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
            text-align: left;
        }

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
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        /* SUCCESS + ERROR MESSAGE BOXES (same as bill + withdraw) */
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

        .back {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .back a {
            color: #ff4d4d;
            text-decoration: none;
            font-weight: bold;
            transition: 0.25s;
        }

        .back a:hover {
            color: #c20000;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="deposit-container">
        <div class="logo">Sejong<span>Bank</span></div>
        <h2>Deposit Money</h2>

        <!-- Success/Error message -->
        <?php
        if (!empty($message)) {
            if (strpos($message, 'successful') !== false) {
                echo "<div class='msg-success'>$message</div>";
            } else {
                echo "<div class='msg-error'>$message</div>";
            }
        }
        ?>

        <form method="POST">

            <label>Select Account</label>
            <select name="account_id" required>
                <option disabled selected>-- Choose account --</option>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <option value="<?= $row['account_id']; ?>">
                        <?= $row['account_type']; ?> (RM <?= number_format($row['balance'], 2); ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Amount (RM)</label>
            <input type="number" step="0.01" min="0" name="amount" placeholder="Enter amount to deposit" required>

            <button type="submit">Confirm Deposit</button>
        </form>

        <div class="back">
            <a href="home.php">← Back to Home</a>
        </div>
    </div>

</body>
</html>
