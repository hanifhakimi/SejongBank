<?php
$host = "172.30.1.85";
$user = "remote_user";      // default for XAMPP
$pass = "Software123";          // default password is empty
$dbname = "sejongbank";  // your actual database name

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
