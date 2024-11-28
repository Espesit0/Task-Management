<?php
session_start();
include 'db.php';

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if project_id is set in the URL
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param('i', $project_id);

    if ($stmt->execute()) {
        header('Location: index.php'); // Redirect to the project list after deletion
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error: Project ID not provided.";
}

$conn->close(); // Close the database connection
?>
