<?php
session_start();
include 'db.php';

// Check if user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Check if project_id is set in the URL
if (!isset($_GET['project_id'])) {
    die('Error: Project ID not provided.');
}

$project_id = intval($_GET['project_id']); // Retrieve and sanitize project_id from URL

// Fetch project to ensure it belongs to the user or the user is an admin
$project_query = "SELECT * FROM projects WHERE id = ?";
$project_stmt = $conn->prepare($project_query);
$project_stmt->bind_param('i', $project_id);
$project_stmt->execute();
$project_result = $project_stmt->get_result();

if ($project_result->num_rows == 0) {
    die('Error: Project not found.');
}

$project = $project_result->fetch_assoc();

// Check if the logged-in user is an admin
$is_admin = $_SESSION['role'] === 'admin'; // Adjust this condition based on how you define roles in your application

// If the user is not an admin, check if they own the project
if (!$is_admin && $project['user_id'] !== $user_id) {
    die('Error: You do not have permission to add tasks to this project.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $assigned_user = intval($_POST['assigned_user']);

    // Ensure the assigned user exists
    $user_check_query = "SELECT * FROM users WHERE id = ?";
    $user_check_stmt = $conn->prepare($user_check_query);
    $user_check_stmt->bind_param('i', $assigned_user);
    $user_check_stmt->execute();
    $user_check_result = $user_check_stmt->get_result();
    if ($user_check_result->num_rows == 0) {
        die('Error: Assigned user does not exist.');
    }

    // Use prepared statements to prevent SQL injection
    $query = "INSERT INTO tasks (task_name, description, due_date, assigned_user, project_id, user_id, status) 
              VALUES (?, ?, ?, ?, ?, ?, 'Pending')";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('sssiii', $task_name, $description, $due_date, $assigned_user, $project_id, $user_id);

        if ($stmt->execute()) {
            // Redirect to the view project page
            header("Location: view_project.php?id=" . $project_id);
            exit();
        } else {
            echo "Error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    } else {
        echo "Error: " . htmlspecialchars(mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Task</title>
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

        input[type="text"], input[type="date"], select, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #dddddd;
            border-radius: 5px;
            font-size: 14px;
            color: #333333;
        }

        textarea {
            height: 100px;
            resize: vertical;
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

            input[type="text"], input[type="date"], select, textarea {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h2>Add New Task</h2>
        <label for="task_name">Task Name:</label>
        <input type="text" id="task_name" name="task_name" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>

        <label for="due_date">Due Date:</label>
        <input type="date" id="due_date" name="due_date" required>

        <label for="assigned_user">Assign to:</label>
        <select name="assigned_user" id="assigned_user" required>
            <?php
            // Fetch all users (including managers) to assign the task to
            $users_query = "SELECT * FROM users WHERE role IN ('user', 'manager')"; 
            $users_result = mysqli_query($conn, $users_query);
            while ($user = mysqli_fetch_assoc($users_result)) {
                echo "<option value='" . intval($user['id']) . "'>" . htmlspecialchars($user['name']) . "</option>"; 
            }
            ?>
        </select>

        <button type="submit">Add Task</button>

        <a href="index.php" class="back-link">Back to Project List</a>
    </form>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
