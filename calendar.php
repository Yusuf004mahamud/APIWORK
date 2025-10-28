<?php
session_start();
require_once "config.php";
require_once "utils.php";

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Fetch tasks for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT task_id, title, due_date, status, priority FROM tasks WHERE user_id=:user_id ORDER BY due_date ASC");
$stmt->execute(['user_id'=>$user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare events for FullCalendar
$events = [];
foreach ($tasks as $task) {
    $events[] = [
        'id' => $task['task_id'],
        'title' => $task['title'] . ' [' . $task['status'] . ']',
        'start' => $task['due_date'],
        'color' => $task['status'] == 'completed' ? '#28a745' : ($task['status'] == 'in_progress' ? '#ffc107' : '#dc3545')
    ];
}
$events_json = json_encode($events);
?>
<!DOCTYPE html>
<html>
<head>
<title>Task Calendar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="calendar-header">
<h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
<div>
    <a href="change_password.php">Change Password</a>
    <a href="logout.php">Logout</a>
</div>
</header>

<div class="calendar-container">
<form id="taskForm">
    <input type="text" name="title" placeholder="Task Title" required>
    <select name="priority">
        <option value="low">Low</option>
        <option value="medium" selected>Medium</option>
        <option value="high">High</option>
    </select>
    <input type="datetime-local" name="due_date" required>
    <select name="status">
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
    </select>
    <button type="submit">Add Task</button>
</form>

<div id='calendar'></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: <?php echo $events_json; ?>,
        editable: true,
        selectable: true,
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false }, // show HH:MM
        eventClick: function(info) {
            let action = prompt("Enter 'edit' to update or 'delete' to remove task:");
            if(action === 'edit') {
                let newTitle = prompt("New title:", info.event.title.split(' [')[0]);
                if(newTitle !== null) {
                    let newStatus = prompt("New status (pending, in_progress, completed):", "pending");
                    let newPriority = prompt("Priority (low, medium, high):", "medium");
                    axios.post('calendar.php', new URLSearchParams({
                        action: 'update',
                        id: info.event.id,
                        title: newTitle,
                        status: newStatus,
                        priority: newPriority
                    })).then(() => {
                        info.event.setProp('title', newTitle + ' [' + newStatus + ']');
                        alert("Task updated!");
                    }).catch(err => alert(err));
                }
            } else if(action === 'delete') {
                if(confirm("Are you sure to delete this task?")) {
                    axios.post('calendar.php', new URLSearchParams({
                        action: 'delete',
                        id: info.event.id
                    })).then(() => {
                        info.event.remove();
                        alert("Task deleted!");
                    }).catch(err => alert(err));
                }
            }
        },
        eventDrop: function(info) {
            axios.post('calendar.php', new URLSearchParams({
                action: 'update',
                id: info.event.id,
                due_date: info.event.start.toISOString()
            })).then(()=>{ alert("Task date updated!"); })
            .catch(err => alert(err));
        }
    });
    calendar.render();

    // Add new task
    document.getElementById('taskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append('action', 'add');
        axios.post('calendar.php', formData)
        .then(res => {
            let t = res.data;
            calendar.addEvent({
                id: t.id,
                title: t.title + ' [' + t.status + ']',
                start: t.due_date,
                color: t.status=='completed'?'#28a745':(t.status=='in_progress'?'#ffc107':'#dc3545')
            });
            this.reset();
            alert("Task added!");
        }).catch(err => alert(err));
    });
});
</script>

</body>
</html>
