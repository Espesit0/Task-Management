<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Error: You do not have permission to access this page.');
}

// Fetch all users from the 'users' table to display in the dropdown
$query = "SELECT id, name FROM users";
$user_result = $conn->query($query);

if (!$user_result) {
    die("Error fetching users: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_name = mysqli_real_escape_string($conn, $_POST['project_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $assigned_user_id = intval($_POST['user_id']); // Get selected user ID from the form

    // Insert the project with the assigned user_id
    $query = "INSERT INTO projects (project_name, description, start_date, end_date, status, user_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssssi', $project_name, $description, $start_date, $end_date, $status, $assigned_user_id);

    if ($stmt->execute()) {
        header('Location: index.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Project</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 500;
            color: #343a40;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #495057;
        }

        input[type="text"], input[type="date"], select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 15px;
            color: #495057;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
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
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <form method="POST" action="">
        <h2>Add New Project</h2>

        <label for="project_name">Project Name:</label>
        <input type="text" id="project_name" name="project_name" required>

        <label for="description">Description:</label>
        <input type="text" id="description" name="description" required>

        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
        </select>

        <label for="user_id">Assign to User:</label>
        <select id="user_id" name="user_id" required>
            <option value="">Select a user</option>
            <?php
            // Loop through users and create options for the dropdown
            while ($user = $user_result->fetch_assoc()) {
                echo "<option value='" . $user['id'] . "'>" . htmlspecialchars($user['name']) . "</option>";
            }
            ?>
        </select>

        <button type="submit">Add Project</button>

        <a href="index.php" class="back-link">Back to Project List</a>
    </form>

</body>
</html>

<?php
$conn->close();
?>
