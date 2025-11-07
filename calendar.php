<?php
session_start();
require_once "config.php";
require_once "utils.php";

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Fetch tasks for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT task_id, title, due_date, status, priority 
                       FROM tasks 
                       WHERE user_id = :user_id 
                       ORDER BY due_date ASC");
$stmt->execute(['user_id' => $user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare events for FullCalendar
$events = [];
foreach ($tasks as $task) {
    $events[] = [
        'id' => $task['task_id'],
        'title' => $task['title'] . ' [' . ucfirst($task['status']) . ']',
        'start' => $task['due_date'],
        'color' => $task['status'] == 'completed' ? '#28a745' :
                  ($task['status'] == 'in_progress' ? '#ffc107' : '#dc3545')
    ];
}
$events_json = json_encode($events);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🌈 My Task Calendar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- FullCalendar & Bootstrap -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css" rel="stylesheet">
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%);
        min-height: 100vh;
        font-family: 'Poppins', sans-serif;
        color: #333;
    }
    h2 {
        margin-top: 30px;
        font-weight: 600;
        color: #004aad;
    }
    #calendar {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        transition: transform 0.2s ease-in-out;
    }
    #calendar:hover {
        transform: scale(1.01);
    }
    .navbar {
        background: rgba(255,255,255,0.9);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .navbar-brand {
        font-weight: 600;
        color: #007bff !important;
    }
    .btn-primary {
        background: #007bff;
        border: none;
        transition: 0.3s;
    }
    .btn-primary:hover {
        background: #0056b3;
    }
    .modal-content {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .fc-event {
        border: none !important;
        font-weight: 500;
    }
    .footer {
        text-align: center;
        padding: 15px;
        color: white;
        font-size: 14px;
        margin-top: 40px;
        opacity: 0.9;
    }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
  <a class="navbar-brand" href="#">🗓️ Task Calendar</a>
  <div class="ml-auto">
      <a href="logout.php" class="btn btn-outline-primary btn-sm">Logout</a>
  </div>
</nav>

<!-- Main content -->
<div class="container py-4">
    <h2 class="text-center mb-4">Your Upcoming Tasks</h2>
    <div id="calendar"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="event_entry_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="modalLabel">Add New Task</h5>
				<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
				  <label for="event_name">Task Title</label>
				  <input type="text" id="event_name" class="form-control" placeholder="Enter task title">
				</div>
				<div class="form-row">
					<div class="form-group col-md-6">
					  <label for="event_start_date">Start Date</label>
					  <input type="date" id="event_start_date" class="form-control">
					</div>
					<div class="form-group col-md-6">
					  <label for="event_end_date">End Date</label>
					  <input type="date" id="event_end_date" class="form-control">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" onclick="save_event()">Save Task</button>
			</div>
		</div>
	</div>
</div>

<!-- Footer -->
<div class="footer">
  <p>✨ Stay organized. Stay productive. ✨</p>
</div>

<!-- JS Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    var events = <?php echo $events_json; ?>;

    $('#calendar').fullCalendar({
        defaultView: 'month',
        timeZone: 'local',
        editable: false,
        selectable: true,
        selectHelper: true,
        events: events,
        eventRender: function(event, element) {
            element.attr('title', event.title);
        },
        select: function(start, end) {
            $('#event_start_date').val(moment(start).format('YYYY-MM-DD'));
            $('#event_end_date').val(moment(end).format('YYYY-MM-DD'));
            $('#event_entry_modal').modal('show');
        }
    });
});

function save_event() {
    var event_name = $("#event_name").val();
    var event_start_date = $("#event_start_date").val();
    var event_end_date = $("#event_end_date").val();

    if (event_name === "" || event_start_date === "" || event_end_date === "") {
        alert("Please fill in all fields.");
        return;
    }

    $.ajax({
        url: "save_event.php",
        type: "POST",
        dataType: "json",
        data: {
            event_name: event_name,
            event_start_date: event_start_date,
            event_end_date: event_end_date
        },
        success: function(response) {
            $('#event_entry_modal').modal('hide');
            alert(response.msg);
            if (response.status === true) {
                location.reload();
            }
        },
        error: function() {
            alert("Error saving event. Please try again.");
        }
    });
}
</script>
</body>
</html>
