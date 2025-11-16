<?php
$host = "localhost";
$user = "root";      // default for XAMPP
$pass = "";          // default password is empty
$dbname = "sejongbank";  // your actual database name

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
