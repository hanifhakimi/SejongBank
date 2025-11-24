<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';
$user_id = $_SESSION['user_id'];


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
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        a.action {
            background: #ffaa00;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>PiggyBank</h2>

    <a href="piggybank_add.php" class="add-btn">+ Create New PiggyBank</a>

    <table>
        <tr>
            <th>Name</th>
            <th>Target (RM)</th>
            <th>Saved (RM)</th>
            <th>Deadline</th>
            <th>Action</th>
        </tr>

        <?php while ($p = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $p['piggybank_name']; ?></td>
            <td><?php echo number_format($p['target_amount'], 2); ?></td>
            <td><?php echo number_format($p['current_amount'], 2); ?></td>
            <td><?php echo $p['deadline']; ?></td>
            <td>
                <a href="piggybank_update.php?id=<?php echo $p['piggybank_id']; ?>" class="action">Add Money</a>
            </td>
        </tr>
        <?php } ?>

    </table>
</div>

</body>
</html>
