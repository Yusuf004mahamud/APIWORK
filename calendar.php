<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt=$pdo->prepare("SELECT task_id,title,due_date,status,priority FROM tasks WHERE user_id=:user_id");
$stmt->execute(['user_id'=>$user_id]);
$tasks=$stmt->fetchAll(PDO::FETCH_ASSOC);

$events=[];
foreach($tasks as $task){
    $events[]=[
        'id'=>$task['task_id'],
        'title'=>$task['title'].' ['.$task['status'].']',
        'start'=>$task['due_date'],
        'color'=>$task['status']=='completed'?'#28a745':($task['status']=='in_progress'?'#ffc107':'#dc3545')
    ];
}
$events_json=json_encode($events);
?>

<!DOCTYPE html>
<html>
<head>
<title>Task Calendar</title>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<style>
body {
    margin:0;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background:#f4f7f8;
}
header {
    background: linear-gradient(to right, #6a11cb, #2575fc);
    color: #fff;
    padding: 20px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
header h2 { margin:0; font-size:24px;}
header a {
    background:#fff;
    color:#2575fc;
    padding:8px 15px;
    border-radius:6px;
    text-decoration:none;
    font-weight:500;
    transition:0.3s;
}
header a:hover { background:#f0f0f0;}
.container {
    max-width:1200px;
    margin:30px auto;
    padding:20px;
}
#taskForm {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:20px;
    background:#fff;
    padding:15px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}
#taskForm input,#taskForm select, #taskForm button {
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
}
#taskForm button {
    background:#2575fc;
    color:#fff;
    border:none;
    cursor:pointer;
    transition:0.3s;
}
#taskForm button:hover { background:#6a11cb; }
#calendar {
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<header>
<h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
<a href="logout.php">Logout</a>
</header>

<div class="container">
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
document.addEventListener('DOMContentLoaded', function(){
var calendarEl=document.getElementById('calendar');
var calendar=new FullCalendar.Calendar(calendarEl,{
    initialView:'dayGridMonth',
    events: <?php echo $events_json; ?>,
    editable:true,
    selectable:true,
    eventClick:function(info){
        let action=prompt("Enter 'edit' to update or 'delete' to remove task:");
        if(action==='edit'){
            let newTitle=prompt("New title:",info.event.title);
            if(newTitle!==null){
                let newStatus=prompt("New status (pending, in_progress, completed):","pending");
                if(newStatus!==null){
                    let newPriority=prompt("Priority (low, medium, high):","medium");
                    axios.post('calendar.php', new URLSearchParams({
                        action:'update',
                        id: info.event.id,
                        title:newTitle,
                        status:newStatus,
                        priority:newPriority
                    })).then(()=>{ 
                        info.event.setProp('title', newTitle+' ['+newStatus+']');
                        alert("Task updated!");
                    }).catch(err=>alert(err));
                }
            }
        } else if(action==='delete'){
            if(confirm("Are you sure to delete this task?")){
                axios.post('calendar.php', new URLSearchParams({
                    action:'delete',
                    id: info.event.id
                })).then(()=>{ 
                    info.event.remove();
                    alert("Task deleted!");
                }).catch(err=>alert(err));
            }
        }
    },
    eventDrop:function(info){
        axios.post('calendar.php', new URLSearchParams({
            action:'update',
            id: info.event.id,
            due_date: info.event.start.toISOString()
        })).then(()=>{ alert("Task date updated!"); })
        .catch(err=>alert(err));
    }
});
calendar.render();

document.getElementById('taskForm').addEventListener('submit',function(e){
    e.preventDefault();
    let formData=new FormData(this);
    formData.append('action','add');
    axios.post('calendar.php', formData)
    .then(res=>{
        calendar.addEvent({
            id: res.data.id,
            title: res.data.title+' ['+res.data.status+']',
            start: res.data.due_date,
            color: res.data.status=='completed'?'#28a745':(res.data.status=='in_progress'?'#ffc107':'#dc3545')
        });
        this.reset();
        alert("Task added!");
    }).catch(err=>alert(err));
});
});
</script>

</body>
</html>
