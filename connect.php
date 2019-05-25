<?php
require_once('config.php');

$conn = new mysqli($server, $user, $password, $db, $port);

if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// echo "Success.";

mysqli_query($conn, "SET NAMES UTF8");
