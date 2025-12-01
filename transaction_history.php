<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
include 'db_connect.php';

// Fetch transactions
$query = "SELECT * FROM transactions WHERE user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | SejongBank</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #ff4d4d 0%, #ffffff 100%);
        }

        .header {
            background-color: #fff;
            padding: 15px 40px;
            font-size: 22px;
            color: #004b87;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #004b87;
            color: white;
            padding: 12px;
            font-size: 15px;
        }

        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        .deposit {
            color: #2ecc71;
            /* green */
            font-weight: bold;
        }

        .withdraw {
            color: #e74c3c;
            /* red */
            font-weight: bold;
        }

        .bill {
            color: #2980b9;
            /* blue */
            font-weight: bold;
        }

        .back-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background: #004b87;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .back-btn:hover {
            background: #003766;
        }
    </style>
</head>

<body>

    <div class="header">Transaction History</div>

    <div class="container">

        <h2>Your Recent Transactions</h2>

        <table>
            <tr>
                <th>Account</th>
                <th>Type</th>
                <th>Amount (RM)</th>
                <th>Date</th>
            </tr>

            <?php
            if (mysqli_num_rows($result) > 0) {

                while ($row = mysqli_fetch_assoc($result)) {

                    // CASE 1: Deposit
                    if ($row['type'] === 'Deposit') {
                        $type_class = "deposit";
                        $type_display = "Deposit";
                    }

                    // CASE 2: Withdrawal
                    elseif ($row['type'] === 'Withdrawal') {
                        $type_class = "withdraw";
                        $type_display = "Withdrawal";
                    }

                    // CASE 3: Bill payment with proper type
                    elseif ($row['type'] === 'Bill Payment') {
                        $type_class = "bill";
                        $type_display = "Bill";
                    }

                    // CASE 4: Bill payment saved as NULL (fallback detection)
                    elseif (empty($row['type']) && strpos($row['description'], 'Bill Payment') !== false) {
                        $type_class = "bill";
                        $type_display = "Bill";
                    }

                    // fallback
                    else {
                        $type_class = "";
                        $type_display = $row['type'];
                    }

                    $amount = number_format($row['amount'], 2);

                    echo "
    <tr>
        <td>{$row['account_id']}</td>
        <td class='$type_class'>{$type_display}</td>
        <td>RM {$amount}</td>
        <td>{$row['created_at']}</td>
    </tr>";
                }


            } else {
                echo "
            <tr>
                <td colspan='4'>No transactions found.</td>
            </tr>";
            }
            ?>
        </table>

        <a href='home.php' class='back-btn'>← Back to Home</a>

    </div>

</body>

</html>