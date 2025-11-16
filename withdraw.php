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

// Handle withdrawal form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = $_POST['account_id'];
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        echo "<script>alert('Amount must be greater than 0'); window.location='withdraw.php';</script>";
        exit;
    }

    // Get current balance
    $bal_query = "SELECT balance FROM accounts WHERE account_id = $account_id AND user_id = $user_id";
    $bal_result = mysqli_query($conn, $bal_query);
    $bal_row = mysqli_fetch_assoc($bal_result);

    $current_balance = $bal_row['balance'];

    // Check if enough balance
    if ($amount > $current_balance) {
        echo "<script>alert('Insufficient balance!'); window.location='withdraw.php';</script>";
        exit;
    }

    // Deduct amount
    $update = "UPDATE accounts 
               SET balance = balance - $amount 
               WHERE account_id = $account_id AND user_id = $user_id";
    mysqli_query($conn, $update);

    // Insert transaction
    $insert_tx = "INSERT INTO transactions (user_id, account_id, type, amount, description)
                  VALUES ($user_id, $account_id, 'Withdrawal', $amount, 'Account withdrawal')";
    mysqli_query($conn, $insert_tx);

    echo "<script>alert('Withdrawal successful!'); window.location='home.php';</script>";
    exit;
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

        select, input {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            border-radius: 8px;
            border: 1px solid #bbb;
            font-size: 15px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        select:focus, input:focus {
            border-color: #004b87;
            outline: none;
            box-shadow: 0 0 6px rgba(0,75,135,0.3);
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

<div class="withdraw-container">
    <div class="logo">Sejong<span>Bank</span></div>
    <h2>Withdraw Money</h2>

    <form method="POST">

        <label>Select Account</label>
        <select name="account_id" required>
            <option disabled selected>-- Choose account --</option>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <option value="<?php echo $row['account_id']; ?>">
                    <?php echo $row['account_type']; ?>
                    (RM <?php echo number_format($row['balance'], 2); ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Amount (RM)</label>
        <input type="number" step="0.01" min="0" name="amount" placeholder="Enter amount to withdraw" required>

        <button type="submit">Confirm Withdrawal</button>
    </form>

    <div class="back">
        <a href="home.php">← Back to Home</a>
    </div>
</div>

</body>
</html>
