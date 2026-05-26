<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "clinic_db";

/* Create connection */
$conn = new mysqli($host, $user, $pass, $db);

/* Check connection */
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

/* Set charset */
$conn->set_charset("utf8");
?>
