<?php
session_start();
include 'db.php';

// Check if the token exists in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists in the database and hasn't expired
    $query = "SELECT * FROM users WHERE reset_token = ? AND reset_expiration > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Process the form submission to reset the password
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Update the password in the database and clear the reset token and expiration
                $update_query = "UPDATE users SET password = ?, reset_token = NULL, reset_expiration = NULL WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param('si', $hashed_password, $user['id']);
                $update_stmt->execute();

                echo "Your password has been successfully reset!";
                // Redirect to login page after password reset
                header('Location: login.php');
                exit();
            } else {
                $error = "Passwords do not match!";
            }
        }
    } else {
        echo "Invalid or expired token!";
        exit();
    }
} else {
    echo "No token provided!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        p {
            text-align: center;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        <?php if (isset($error)) { echo '<p class="error">' . htmlspecialchars($error) . '</p>'; } ?>
        <form method="POST" action="">
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
