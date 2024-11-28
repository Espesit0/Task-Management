<?php
session_start();
include 'db.php';

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if task_id is set in the URL
if (!isset($_GET['id'])) {
    echo "Error: Task ID not provided.";
    exit();
}

$task_id = $_GET['id'];

// Fetch task details
$query = "SELECT * FROM tasks WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

// Update task details on form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_name = $_POST['task_name'];
    $status = $_POST['status'];

    // Use prepared statements to prevent SQL injection
    $update_query = "UPDATE tasks SET task_name = ?, status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('ssi', $task_name, $status, $task_id);
    
    if ($update_stmt->execute()) {
        header('Location: index.php');
        exit();
    } else {
        echo "Error: " . $update_stmt->error;
    }

    $update_stmt->close();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            max-width: 600px;
            width: 100%;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        h1 {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #555;
        }

        input[type="text"], select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f1f3f6;
        }

        input[type="text"]:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 20px;
            }

            input[type="text"], select {
                font-size: 14px;
            }

            button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Task</h1>
        
        <form method="POST">
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <label for="task_name">Task Name:</label>
            <input type="text" id="task_name" name="task_name" value="<?php echo htmlspecialchars($task['task_name']); ?>" required>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="Pending" <?php if ($task['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                <option value="In Progress" <?php if ($task['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                <option value="Completed" <?php if ($task['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
            </select>

            <button type="submit">Update Task</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
