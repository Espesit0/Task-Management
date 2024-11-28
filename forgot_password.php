<?php
session_start();
include 'db.php';  // Adjust this path according to your setup

if(isset($_POST['submit'])) {
    $email = $_POST['email'];
    $newpassword = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);  // Use password_hash for better security

    // Check if the email exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the password
        $update_query = "UPDATE users SET password = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('ss', $newpassword, $email);
        if ($update_stmt->execute()) {
            echo "<script>alert('Your password has been successfully changed');</script>";
            header("Location: login.php");  // Redirect to login page after password reset
        } else {
            echo "<script>alert('Error updating password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Email not found');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 2rem;
        }
        form {
            width: 100%;
        }
        fieldset {
            border: none;
            padding: 0;
        }
        .field {
            margin-bottom: 1.5rem;
        }
        .field label {
            display: block;
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
        }
        .field input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            color: #333;
            transition: border-color 0.3s;
        }
        .field input:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            display: inline-block;
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: #fff;
            font-weight: 500;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .container {
                padding: 1.5rem;
            }
            h1 {
                font-size: 20px;
            }
            .field input, button {
                font-size: 14px;
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>
        <form method="post" name="chngpwd" onSubmit="return validate();">
            <fieldset>
                <div class="field">
                    <label for="email">Email:</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="field">
                    <label for="newpassword">New Password:</label>
                    <input type="password" name="newpassword" placeholder="Enter new password" required>
                </div>
                <div class="field">
                    <label for="confirmpassword">Confirm Password:</label>
                    <input type="password" name="confirmpassword" placeholder="Confirm new password" required>
                </div>
                <div class="field">
                    <button type="submit" name="submit">Reset Password</button>
                </div>
            </fieldset>
        </form>
        <a href="login.php">Back to Login</a>
    </div>
</body>
</html>
