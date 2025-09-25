<?php
// view.php

$tasks = [];
if (file_exists("tasks.txt")) {
    $tasks = file("tasks.txt", FILE_IGNORE_NEW_LINES);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Tasks</title>
</head>
<body>
    <h2>My Tasks</h2>

    <?php if (empty($tasks)): ?>
        <p>No tasks found. Add one <a href="add.php">here</a>.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tasks as $task): ?>
                <li><?php echo htmlspecialchars($task); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <br>
    <a href="add.php">Add More Tasks</a>
</body>
</html>