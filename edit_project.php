<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$project_id = $_GET['id'];

// Fetch project details
$query = "SELECT * FROM projects WHERE id = $project_id";
$result = mysqli_query($conn, $query);
$project = mysqli_fetch_assoc($result);

// Update project details on form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_name = $_POST['project_name'];
    $status = $_POST['status'];
    
    $update_query = "UPDATE projects SET project_name = '$project_name', status = '$status' WHERE id = $project_id";
    
    if (mysqli_query($conn, $update_query)) {
        header('Location: index.php');
        exit();
    } else {
        echo "Error updating project: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project</title>
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
            min-height: 100vh;
        }
        .container {
            background-color: white;
            max-width: 600px;
            width: 100%;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
            font-weight: 500;
            margin-bottom: 8px;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f9f9f9;
            transition: border-color 0.3s;
        }
        input:focus, select:focus {
            border-color: #007bff;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back-button {
            width: 100%;
            margin-top: 10px;
            background-color: #6c757d;
        }
        .back-button:hover {
            background-color: #5a6268;

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
    <div class="container">
        <h1>Edit Project</h1>
        <form method="POST">
            <label for="project_name">Project Name:</label>
            <input type="text" id="project_name" name="project_name" value="<?php echo $project['project_name']; ?>" required>
            
            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="Ongoing" <?php if ($project['status'] == 'Ongoing') echo 'selected'; ?>>Ongoing</option>
                <option value="Completed" <?php if ($project['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                <option value="On Hold" <?php if ($project['status'] == 'On Hold') echo 'selected'; ?>>On Hold</option>
            </select>
            
            <button type="submit">Update Project</button>
        </form>
        <form action="index.php" method="GET">
            <button class="back-button" type="submit">Back to Project List</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
