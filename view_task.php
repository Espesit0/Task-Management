<?php
session_start();
include 'db.php'; // Ensure this includes your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get task_id from the URL
$task_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Fetch task details based on task_id
$task_query = "SELECT * FROM tasks WHERE id = ?";
$task_stmt = $conn->prepare($task_query);
$task_stmt->bind_param('i', $task_id);
$task_stmt->execute();
$task_result = $task_stmt->get_result();

if ($task_result->num_rows !== 1) {
    die("Task not found.");
}

$task = $task_result->fetch_assoc();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["pdf_file"]["name"]);

    // Check if the file is a valid PDF
    if ($_FILES["pdf_file"]["type"] == "application/pdf") {
        if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
            // Update the task with the PDF file path
            $update_query = "UPDATE tasks SET pdf_file = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('si', $target_file, $task_id);
            $update_stmt->execute();
            
            echo "<p style='color: green;'>File uploaded successfully!</p>";
        } else {
            echo "<p style='color: red;'>Sorry, there was an error uploading your file.</p>";
        }
    } else {
        echo "<p style='color: red;'>Please upload a valid PDF file.</p>";
    }
}

// Handle task completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_task'])) {
    $update_query = "UPDATE tasks SET status = 'Completed' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('i', $task_id);
    $update_stmt->execute();
    
    echo "<p style='color: green;'>Task marked as completed!</p>";
    $task['status'] = 'Completed'; // Update the task status in memory to reflect the change
}

// Google Calendar link generation
$calendar_title = urlencode($task['task_name']);
$calendar_description = urlencode($task['description']);
$calendar_start = urlencode(date("Ymd\THis", strtotime($task['due_date'])));
$calendar_end = urlencode(date("Ymd\THis", strtotime($task['due_date'] . ' +1 hour'))); // Assuming the event lasts 1 hour

$google_calendar_url = "https://www.google.com/calendar/render?action=TEMPLATE&text={$calendar_title}&dates={$calendar_start}/{$calendar_end}&details={$calendar_description}";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            border-bottom: 2px solid #2980b9;
            padding-bottom: 10px;
        }
        .task {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .task strong {
            color: #2980b9; /* Blue for titles */
        }
        .task a {
            color: #2980b9; /* Blue for links */
            text-decoration: none;
        }
        .task a:hover {
            text-decoration: underline;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        input[type="file"] {
            margin-bottom: 10px;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
            width: 80%;
        }
        button {
            background-color: #2980b9;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 80%;
            margin: 5px 0;
        }
        button:hover {
            background-color: #1c5980; /* Darker blue */
        }
        .complete-btn {
            background-color: #27ae60; /* Green */
        }
        .complete-btn:hover {
            background-color: #219150; /* Darker green */
        }
        .calendar-btn {
            background-color: #f39c12; /* Orange */
        }
        .calendar-btn:hover {
            background-color: #e67e22; /* Darker orange */
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2980b9;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Task Details</h2>
    <div class="task">
        <strong>Task:</strong> <?php echo htmlspecialchars($task['task_name']); ?><br>
        <strong>Description:</strong> <?php echo htmlspecialchars($task['description']); ?><br>
        <strong>Due Date:</strong> <?php echo htmlspecialchars($task['due_date']); ?><br>
        <strong>Status:</strong> <?php echo htmlspecialchars($task['status']); ?><br>
        <?php if (!empty($task['pdf_file'])): ?>
            <strong>Attached PDF:</strong> <a href="<?php echo htmlspecialchars($task['pdf_file']); ?>" target="_blank">View PDF</a><br>
        <?php endif; ?>
    </div>

    <h2>Upload PDF</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="pdf_file" accept=".pdf" required>
        <button type="submit">Upload</button>
    </form>

    <!-- Button to mark task as complete -->
    <?php if ($task['status'] !== 'Completed'): ?>
        <form method="POST">
            <button type="submit" name="complete_task" class="complete-btn">Complete Task</button>
        </form>
    <?php endif; ?>

    <!-- Google Calendar Button -->
    <form method="GET" action="<?php echo $google_calendar_url; ?>" target="_blank">
        <button type="submit" class="calendar-btn">Add to Calendar</button>
    </form>

    <a class="back-link" href="index.php">Back to Project List</a>
</div>

</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
