<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user ID

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Check if new password matches confirm password
    if ($new_password != $confirm_password) {
        die('Error: New password and confirm password do not match.');
    }

    // Fetch the current password from the database
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify the current password
    if (!password_verify($current_password, $user['password'])) {
        die('Error: Current password is incorrect.');
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the database
    $update_query = "UPDATE users SET password = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('si', $hashed_password, $user_id);

    if ($update_stmt->execute()) {
        echo "Password successfully updated!";
    } else {
        echo "Error updating password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
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

        form {
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

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #555555;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #dddddd;
            border-radius: 5px;
            font-size: 14px;
            color: #333333;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            border: none;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
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

        @media (max-width: 768px) {
            form {
                width: 90%;
                padding: 20px;
            }

            button {
                font-size: 14px;
            }

            input[type="password"] {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <form method="POST" action="change_password.php">
        <h2>Change Password</h2>
        
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Update Password</button>

        <a href="index.php" class="back-link">Back to Dashboard</a>
    </form>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
