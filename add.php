<?php
// add.php 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task = trim($_POST['task']);

    if (!empty($task)) {
        // Save task into tasks.txt
        file_put_contents("tasks.txt", $task . PHP_EOL, FILE_APPEND);
        echo " Task added successfully! <br><br>";
    } else {
        echo " Please enter a task! <br><br>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Task</title>
</head>
<body>
    <h2>Add a New Task</h2>
    <form method="post" action="add.php">
        <input type="text" name="task" placeholder="Enter your task">
        <button type="submit">Add Task</button>
    </form>

    <br>
    <a href="view.php">View Tasks</a>
</body>
</html>
