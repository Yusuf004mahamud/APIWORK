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
    <title>Task Calendar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- FullCalendar CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }
        #calendar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .fc-event {
            cursor: pointer;
        }
        h2 {
            margin: 30px 0;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <h2 class="text-center text-primary">📅 Task Calendar</h2>
    <div id="calendar"></div>
</div>

<!-- Modal: Add Event -->
<div class="modal fade" id="event_entry_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalLabel">Add New Event</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
				  <label for="event_name">Event Name</label>
				  <input type="text" id="event_name" class="form-control" placeholder="Enter event name">
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
				<button type="button" class="btn btn-primary" onclick="save_event()">Save Event</button>
			</div>
		</div>
	</div>
</div>

<!-- JS Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Use PHP-encoded tasks as events
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
        alert("Please enter all required details.");
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
