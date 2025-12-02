<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$user_id = (int)$_SESSION['user_id'];
$piggybank_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($piggybank_id > 0) {
    //delete only if this tabung belongs to current user
    $sql = "DELETE FROM piggybank WHERE piggybank_id = $piggybank_id AND user_id = $user_id";
    mysqli_query($conn, $sql);
}

header("Location: tabung.php");
exit;
