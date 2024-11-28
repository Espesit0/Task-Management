<?php
session_start();
include 'db.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Get the current logged-in user's ID

// Query to get user role (assuming you have roles set up)
$sql_role = "SELECT role FROM users WHERE id = ?";
$stmt_role = $conn->prepare($sql_role);
$stmt_role->bind_param('i', $user_id);
$stmt_role->execute();
$result_role = $stmt_role->get_result();
$user = $result_role->fetch_assoc();
$role = $user['role'];

// Fetch projects based on user role
if ($role === 'admin') {
    $sql = "SELECT * FROM projects"; // Admin sees all projects
} else {
    $sql = "SELECT * FROM projects WHERE user_id = ?"; // Regular users see only their assigned projects
}

$stmt = $conn->prepare($sql);
if ($role !== 'admin') {
    $stmt->bind_param('i', $user_id);
}
$stmt->execute();
$project_result = $stmt->get_result();

if (!$project_result) {
    die("Error fetching projects: " . $conn->error);
}

// Handle task completion, deletion, and project deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['complete_task_id'])) {
        $complete_task_id = intval($_POST['complete_task_id']);
        $update_query = "UPDATE tasks SET status = 'Completed' WHERE id = ? AND user_id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param('ii', $complete_task_id, $user_id);
        if (!$stmt_update->execute()) {
            echo "Error completing task: " . mysqli_error($conn);
        }
    }

    if (isset($_POST['delete_task_id'])) {
        $delete_task_id = intval($_POST['delete_task_id']);
        $delete_query = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
        $stmt_delete = $conn->prepare($delete_query);
        $stmt_delete->bind_param('ii', $delete_task_id, $user_id);
        if (!$stmt_delete->execute()) {
            echo "Error deleting task: " . mysqli_error($conn);
        }
    }

    if (isset($_POST['delete_project_id'])) {
        $delete_project_id = intval($_POST['delete_project_id']);
        $delete_project_query = "DELETE FROM projects WHERE id = ?";
        $stmt_delete_project = $conn->prepare($delete_project_query);
        $stmt_delete_project->bind_param('i', $delete_project_id);
        if (!$stmt_delete_project->execute()) {
            echo "Error deleting project: " . mysqli_error($conn);
        }
    }

    // After handling POST, re-fetch the projects
    $stmt->execute();
    $project_result = $stmt->get_result();
}

// Logout logic
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
         body {
            font-family: 'Roboto', sans-serif;
            background-color: #6a6a6a; /* Red body background */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

         header {
            background-color: #ffffff; /* White header background */
            color: #ff0000; /* Red text color */
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

         header img {
            height: 50px; /* Logo height */
            width: auto;
        }
        
        header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            align-items: center;
        }
        
        .user-icon {
            font-size: 24px;
            cursor: pointer;
        }

        .user-icon:hover {
            color: #007BFF;
        }

        .dropdown {
            display: none;
            position: absolute;
            right: 20px;
            top: 60px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }
        .dropdown a {
            display: block;
            padding: 10px 20px;
            color: #343a40;
            text-decoration: none;
            font-size: 14px;
        }
        .dropdown a:hover {
            background-color: #f8f9fa;
        }
        .dropdown a.logout-btn {
            color: #800000
        }

        .container {
            max-width: 1200px;
            width: 100%;
            margin: 20px auto;
            padding: 0 20px;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .action-bar a, .action-bar form button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .action-bar a:hover, .action-bar form button:hover {
            background-color: #0056b3;
        }

        .logout-btn {
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .project-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .project-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.2s ease;
        }

        .project-card:hover {
            transform: translateY(-5px);
        }

        .project-card h2 {
            font-size: 20px;
            color: #343a40;
            margin-bottom: 10px;
        }

        .project-card p {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .project-card .buttons {
            display: flex;
            justify-content: space-between;
        }

        .project-card .buttons a, .project-card .buttons form button {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            transition: background-color 0.3s ease;
        }

        .project-card .buttons a:hover, .project-card .buttons form button:hover {
            background-color: #218838;
        }

        .task-list {
            margin-top: 10px;
        }

        .task-list p {
            color: #6c757d;
            font-size: 13px;
        }

        footer {
            margin-top: auto;
            padding: 20px;
            background-color: #ffffff; /* White footer background */
            color: #000000; /* Red footer text */
            text-align: center;
        }

        button.edit-btn, button.delete-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button.edit-btn:hover, button.delete-btn:hover {
            background-color: #0056b3;
        }

        button.delete-btn {
            background-color: #dc3545;
        }

        button.delete-btn:hover {
            background-color: #c82333;
        }
    </style>
    <script>
        function confirmDelete(message) {
            return confirm(message);
        }

         // Toggle dropdown visibility
        function toggleDropdown() {
            var dropdown = document.getElementById("userDropdown");
            if (dropdown.style.display === "none" || dropdown.style.display === "") {
                dropdown.style.display = "block";
            } else {
                dropdown.style.display = "none";
            }
        }

        // Close dropdown if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('.user-icon')) {
                var dropdown = document.getElementById("userDropdown");
                if (dropdown.style.display === "block") {
                    dropdown.style.display = "none";
                }
            }
        }
    </script>
</head>
<body>

    <header>
        <img src="logo_black.png" alt="Top Click Sdn Bhd"> <!-- Logo -->
        <h1>Welcome to Top Click Sdn Bhd Task Management</h1>
         <!-- User Icon with Dropdown -->
        <i class="fas fa-user user-icon" onclick="toggleDropdown()"></i>
        <div class="dropdown" id="userDropdown">
            <a href="profile.php">Profile</a>
            <a href="change_password.php">Change Password</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

   <div class="container">
        <div class="action-bar">
            <a href="add_project.php">Add New Project</a>    
        </div>

        <div class="project-list">
            <?php
            if ($project_result->num_rows > 0) {
                while ($project = $project_result->fetch_assoc()) {
                    echo "<div class='project-card'>";
                    echo "<h2>" . htmlspecialchars($project['project_name']) . "</h2>";
                    echo "<p>Status: " . htmlspecialchars($project['status']) . "</p>";

                    echo "<div class='buttons'>";
                    echo "<a href='view_project.php?id=" . $project['id'] . "'>View Project</a>"; // Link to view_project.php
                    echo "<a href='edit_project.php?id=" . $project['id'] . "'>Edit</a>";
                    echo "<a href='add_task.php?project_id=" . $project['id'] . "'>Add Task</a>";
                    echo "<form method='POST' action='' onsubmit='return confirmDelete(\"Are you sure you want to delete this project?\");'>";
                    echo "<input type='hidden' name='delete_project_id' value='" . $project['id'] . "'>";
                    echo "<button class='delete-btn' type='submit'>Delete</button>";
                    echo "</form>";
                    echo "</div>";

                    $project_id = $project['id'];
                    $sql_tasks = "SELECT * FROM tasks WHERE project_id = ?";
                    $stmt_tasks = $conn->prepare($sql_tasks);
                    $stmt_tasks->bind_param('i', $project_id);
                    $stmt_tasks->execute();
                    $task_result = $stmt_tasks->get_result();

                    if ($task_result->num_rows > 0) {
                        echo "<div class='task-list'>";
                        while ($task = $task_result->fetch_assoc()) {
                            echo "<p><a href='view_task.php?id=" . $task['id'] . "'>" . htmlspecialchars($task['task_name']) . "</a></p>"; // Clickable task name
                            echo "<form method='POST' action='' onsubmit='return confirmDelete(\"Are you sure you want to delete this task?\");'>";
                            echo "<input type='hidden' name='delete_task_id' value='" . $task['id'] . "'>";
                            echo "<button class='delete-btn' type='submit'>Delete Task</button>";
                            echo "</form>";
                        }
                        echo "</div>";
                    } else {
                        echo "<p>No tasks available for this project.</p>";
                    }
                    echo "</div>"; // Close project-card
                }
            } else {
                echo "<p>No projects available.</p>";
            }
            ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Top Click Sdn Bhd Task Management. All Rights Reserved.</p>
    </footer>

</body>
</html>
