<?php
session_start();
include 'db.php'; // Ensure this includes your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch tasks from the database
$sql_tasks = "SELECT * FROM tasks ORDER BY due_date ASC";
$stmt_tasks = $conn->prepare($sql_tasks);
$stmt_tasks->execute();
$task_result = $stmt_tasks->get_result();

// Group tasks by due date
$tasks_by_date = [];
while ($task = $task_result->fetch_assoc()) {
    $date = date('Y-m-d', strtotime($task['due_date']));
    $tasks_by_date[$date][] = $task;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Calendar</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Three columns for three months */
            gap: 20px;
            margin-top: 20px;
        }
        .month {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .month-name {
            font-weight: bold;
            font-size: 1.5em;
            text-align: center;
            margin-bottom: 10px;
        }
        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr); /* Seven days of the week */
        }
        .day {
            border: 1px solid #ccc;
            padding: 5px;
            height: 80px; /* Height for the day cell */
            position: relative;
        }
        .date {
            font-weight: bold;
            font-size: 1.2em;
        }
        .tasks {
            margin-top: 5px;
            font-size: 0.9em;
            color: #555;
        }
        .calendar-button {
            display: inline-block;
            margin-top: 5px;
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .calendar-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h2>Task Calendar for <?php echo date('Y'); ?></h2>

<div class="calendar">
    <?php
    // Generate the calendar for the current year
    $current_year = date('Y');
    
    for ($month = 1; $month <= 12; $month++) {
        echo "<div class='month'>";
        echo "<div class='month-name'>" . date('F', mktime(0, 0, 0, $month, 1)) . "</div>";
        echo "<div class='days'>";
        
        // Get the number of days in the current month
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $current_year);
        
        // Print empty cells for days of the week before the first day of the month
        $first_day_of_month = date('w', strtotime("$current_year-$month-01"));
        for ($i = 0; $i < $first_day_of_month; $i++) {
            echo "<div class='day'></div>";
        }
        
        // Print the days of the month
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = sprintf("%04d-%02d-%02d", $current_year, $month, $day);
            echo "<div class='day'>";
            echo "<div class='date'>" . htmlspecialchars($day) . "</div>";
            if (isset($tasks_by_date[$date])) {
                foreach ($tasks_by_date[$date] as $task) {
                    echo "<div class='tasks'>• " . htmlspecialchars($task['task_name']) . "</div>";
                    // Add the calendar button
                    $task_name = urlencode($task['task_name']);
                    $task_description = urlencode($task['description']);
                    $due_date = urlencode($task['due_date']);
                    echo "<a class='calendar-button' href='https://calendar.google.com/calendar/render?action=TEMPLATE&text=$task_name&details=$task_description&dates=$due_date/$due_date'>Add to Calendar</a>";
                }
            } else {
                echo "<div class='tasks'>No tasks</div>";
            }
            echo "</div>";
        }
        
        echo "</div></div>"; // Close the days and month divs
    }
    ?>
</div>

<a href="index.php">Back to Project List</a>

</body>
</html>
