<?php
$host = 'localhost';  // or your hosting server
$dbname = 'task_management';  // your database name
$username = 'root';  // the default MySQL user (change if different)
$password = '';  // your MySQL password (if set)

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
