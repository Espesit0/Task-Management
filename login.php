<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Differentiating between admin and user login

    $query = "SELECT * FROM users WHERE email = ? AND role = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $role); // Bind both email and role to the query
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Roboto', sans-serif;
            overflow: hidden; /* Prevent scrolling */
        }

        /* Fullscreen video background */
        .video-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensure the video covers the whole background */
            z-index: -1; /* Send video to the background */
            opacity: 0.7;
        }

        .container {
            position: relative; /* Make the container float over the video */
            z-index: 1; /* Ensure content appears above the video */
            background-color: ghostwhite; /* Semi-transparent white background */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); /* Add some shadow */
            width: 100%;
            max-width: 500px;
            text-align: center;
            margin: auto;
            top: 50%;
            transform: translateY(-50%); /* Vertically center the container */
        }
        .logo { 
            display: block;
            margin: 0 auto 10px; /* Center the logo and add space below it */
            width: 100px; /* Adjust width as needed */
            height: auto; /* Maintain aspect ratio */
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        input {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px;
            background-color: #ff0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #4CAF50;
        }

        p {
            text-align: center;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            text-align: center;
        }

        .tab-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .tab {
            background-color: #FF0000;
            color: white;
            padding: 10px;
            margin: 0 10px;
            cursor: pointer;
            border-radius: 4px;
            text-align: center;
            width: 100%;
        }

        .tab:hover {
            background-color: #0056b3;
        }

        .forgot-password {
            text-align: center;
            margin-top: -10px;
        }
    </style>
</head>
<body>

    <!-- Background Video -->
    <video autoplay muted loop class="video-bg">
        <source src="backgroundlooping.mp4" type="video/mp4"> <!-- Your video file here -->
        Your browser does not support HTML5 video.
    </video>

    <div class="container">
        <img src="logo_black.png" alt="Top Click Logo" class="logo">
        <h1>Top Click Task Management</h1>

        <?php if (isset($error)) { echo '<p class="error">' . htmlspecialchars($error) . '</p>'; } ?>

        <!-- Tabs for switching between Admin and User login forms -->
        <div class="tab-container">
            <div class="tab" id="admin-tab">Admin Login</div>
            <div class="tab" id="user-tab">User Login</div>
        </div>

        <!-- Admin Login Form -->
        <form id="admin-form" method="POST" action="" style="display:none;">
            <input type="email" name="email" placeholder="Admin Email" required>
            <input type="password" name="password" placeholder="Admin Password" required>
            <input type="hidden" name="role" value="admin"> <!-- Hidden field to specify admin role -->
            <button type="submit">Login as Admin</button>
        </form>

        <!-- User Login Form -->
        <form id="user-form" method="POST" action="">
            <input type="email" name="email" placeholder="User Email" required>
            <input type="password" name="password" placeholder="User Password" required>
            <input type="hidden" name="role" value="user"> <!-- Hidden field to specify user role -->
            <button type="submit">Login as User</button>
        </form>

        <!-- Forgot Password link -->
        <p class="forgot-password">
            <a href="forgot_password.php">Forgot Password?</a>
        </p>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    <script>
        // JavaScript to toggle between Admin and User login forms
        const adminTab = document.getElementById('admin-tab');
        const userTab = document.getElementById('user-tab');
        const adminForm = document.getElementById('admin-form');
        const userForm = document.getElementById('user-form');

        // Default to showing user login form
        userForm.style.display = 'block';

        // Show Admin login form when clicking on Admin tab
        adminTab.addEventListener('click', function() {
            userForm.style.display = 'none';
            adminForm.style.display = 'block';
        });

        // Show User login form when clicking on User tab
        userTab.addEventListener('click', function() {
            adminForm.style.display = 'none';
            userForm.style.display = 'block';
        });
    </script>
</body>
</html>
