<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$user_id = (int) $_SESSION['user_id'];

$query = "SELECT * FROM piggybank WHERE user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Tabung</title>
    <style>
        body {
            font-family: Arial;
            background: #f3f3f3;
            margin: 0;
        }

        .container {
            width: 900px;
            background: #fff;
            margin: 40px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px #ccc;
        }

        .add-btn {
            background: #004b87;
            padding: 12px 18px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background: #004b87;
            color: white;
            padding: 12px;
            text-align: left;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        a.action {
            display: inline-block;
            background: #ffaa00;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            margin-right: 5px;
        }

        a.delete-btn {
            display: inline-block;
            background: #cccccc;
            color: #333;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }

        a.delete-btn:hover {
            background: #ff4d4d;
            color: #fff;
        }

        .back-home {
            display: inline-block;
            margin-top: 25px;
            background: #004b87;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .back-home:hover {
            background: #00345d;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Tabung</h2>

        <a href="tabung_add.php" class="add-btn">+ Create New Tabung</a>

        <table>
            <tr>
                <th>Name</th>
                <th>Target (RM)</th>
                <th>Saved (RM)</th>
                <th>Remaining Days</th>
                <th>Action</th>
            </tr>

            <?php while ($p = mysqli_fetch_assoc($result)) {

                // Calculate remaining days
                $remainingText = '-';
                if (!empty($p['deadline']) && $p['deadline'] !== '0000-00-00') {
                    $today = new DateTime();
                    $deadlineDate = new DateTime($p['deadline']);
                    $diff = $today->diff($deadlineDate);
                    $days = (int) $diff->format('%r%a'); // signed days
            
                    if ($days < 0) {
                        $remainingText = 'Deadline passed';
                    } elseif ($days == 0) {
                        $remainingText = 'Today';
                    } else {
                        $remainingText = $days . ' day' . ($days > 1 ? 's' : '');
                    }
                }
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['piggybank_name']); ?></td>
                    <td><?php echo number_format($p['target_amount'], 2); ?></td>
                    <td><?php echo number_format($p['current_amount'], 2); ?></td>
                    <td><?php echo $remainingText; ?></td>
                    <td>
                        <a href="tabung_update.php?id=<?php echo $p['piggybank_id']; ?>" class="action">Add Money</a>

                        <a href="tabung_delete.php?id=<?php echo $p['piggybank_id']; ?>" class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete this tabung?');">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php } ?>

        </table>

        <a href="home.php" class="back-home">← Back to Home</a>

    </div>

</body>

</html>