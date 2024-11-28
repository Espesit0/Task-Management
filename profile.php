<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch the user's info from the database
$query = "SELECT name, email, role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    // Fetch the user data
    $user = $result->fetch_assoc();
} else {
    die('Error: User not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .profile-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 400px;
            max-width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333333;
            font-weight: 500;
        }

        p {
            font-size: 16px;
            color: #555555;
            margin: 10px 0;
        }

        .profile-info {
            font-weight: 500;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007BFF;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>User Profile</h2>

        <p><strong>Name:</strong> <span class="profile-info"><?php echo htmlspecialchars($user['name']); ?></span></p>
        <p><strong>Email:</strong> <span class="profile-info"><?php echo htmlspecialchars($user['email']); ?></span></p>
        <p><strong>Role:</strong> <span class="profile-info"><?php echo htmlspecialchars($user['role']); ?></span></p>

        <a href="index.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
