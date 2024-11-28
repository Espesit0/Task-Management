<?php
session_start();
include 'db.php'; // Connect to the database

// Check if user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Check if project_id is set in the URL
if (!isset($_GET['id'])) {
    die('Error: Project ID not provided.');
}

$project_id = intval($_GET['id']);

// Query to fetch project details, allowing access for admins
$project_query = "SELECT * FROM projects WHERE id = ?";
$project_stmt = $conn->prepare($project_query);
if ($project_stmt === false) {
    die("ERROR: Could not prepare query: $project_query. " . htmlspecialchars($conn->error));
}
$project_stmt->bind_param('i', $project_id);
$project_stmt->execute();
$project_result = $project_stmt->get_result();

if ($project_result->num_rows === 0) {
    die('Error: Project not found or you do not have permission to view this project.');
}

$project = $project_result->fetch_assoc();

// Check if the logged-in user is an admin
$is_admin = $_SESSION['role'] === 'admin'; // Adjust this condition based on how you define roles in your application

// If the user is not an admin, check if they own the project
if (!$is_admin && $project['user_id'] !== $user_id) {
    die('Error: You do not have permission to view this project.');
}

// Query to fetch tasks for this project
$tasks_query = "SELECT tasks.*, users.name AS assigned_user_name FROM tasks 
                JOIN users ON tasks.assigned_user = users.id 
                WHERE project_id = ?";
$tasks_stmt = $conn->prepare($tasks_query);
if ($tasks_stmt === false) {
    die("ERROR: Could not prepare query: $tasks_query. " . htmlspecialchars($conn->error));
}
$tasks_stmt->bind_param('i', $project_id);
$tasks_stmt->execute();
$tasks_result = $tasks_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #0056b3;
            margin-bottom: 20px;
        }
        .project-details, .task-list {
            margin: 20px 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            color: #343a40;
            margin-top: 0;
        }
        p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        a {
            display: inline-block;
            margin: 10px 0;
            text-decoration: none;
            color: #007BFF;
            font-weight: 700;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Project: <?php echo htmlspecialchars($project['project_name']); ?></h1>

    <div class="project-details">
        <h2>Project Details</h2>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($project['due_date'] ?? 'Not specified'); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($project['status']); ?></p>
    </div>

    <div class="task-list">
        <h2>Tasks</h2>
        <table>
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tasks_result->num_rows > 0) : ?>
                    <?php while ($task = $tasks_result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                            <td><?php echo htmlspecialchars($task['description']); ?></td>
                            <td><?php echo htmlspecialchars($task['due_date'] ?? 'Not specified'); ?></td>
                            <td><?php echo htmlspecialchars($task['assigned_user_name']); ?></td>
                            <td><?php echo htmlspecialchars($task['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No tasks found for this project.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a class="button" href="add_task.php?project_id=<?php echo $project_id; ?>">Add New Task</a>
        <a class="button" href="index.php">Back to Project List</a>
    </div>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
