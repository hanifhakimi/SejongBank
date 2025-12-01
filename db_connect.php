<?php
$host = "172.30.1.15"; #test
$user = "remote_user";
$pass = "Software123";
$dbname = "sejongbank";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
