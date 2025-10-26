<?php
require_once 'config.php';
require_once 'utils.php';
session_start();
if (!isset($_SESSION['user_id'])) redirect('login.php');
$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];
// fetch some user info
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard</title><link rel="stylesheet" href="style.css"></head><body>
<header class="navbar"><div class="logo">TaskManager</div><nav><a href="enable_2fa.php">Enable 2FA</a> <a href="change_password.php">Change password</a> <a href="logout.php">Logout</a></nav></header>

<main class="calendar-wrap" style="padding-top:100px;">
  <div class="calendar-header">
    <div>
      <button id="prev">&lt; Prev</button>
      <button id="next">Next &gt;</button>
    </div>
    <div><h2 id="monthLabel">Month</h2></div>
    <div><button id="addTaskBtn">Add Activity</button></div>
  </div>

  <div id="calendar" class="calendar-grid">
    <!-- days will be injected by JS -->
  </div>
</main>

<!-- modal -->
<div id="modal" class="modal">
  <div class="modal-content">
    <h3>Add Activity</h3>
    <form id="taskForm">
      <input type="text" name="title" placeholder="Title" required>
      <input type="date" name="date" required>
      <input type="time" name="time_from" placeholder="From">
      <input type="time" name="time_to" placeholder="To">
      <select name="color"><option value="#ff6a00">Work</option><option value="#2ecc71">Personal</option><option value="#3498db">Study</option></select>
      <textarea name="description" placeholder="Description"></textarea>
      <input type="hidden" name="action" value="add">
      <button type="submit">Save</button>
      <button type="button" id="closeModal">Cancel</button>
    </form>
  </div>
</div>

<script>
// client-side calendar (monthly)
let current = new Date();
const calendarEl = document.getElementById('calendar');
const monthLabel = document.getElementById('monthLabel');

function renderCalendar(date){
  calendarEl.innerHTML = '';
  const year = date.getFullYear();
  const month = date.getMonth();
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const startDay = firstDay.getDay(); // 0-6 Sun-Sat
  const daysInMonth = lastDay.getDate();
  monthLabel.textContent = date.toLocaleString('default', {month:'long', year:'numeric'});
  // fill blanks
  for(let i=0;i<startDay;i++){
    const empty = document.createElement('div');
    empty.className='calendar-day';
    calendarEl.appendChild(empty);
  }
  for(let d=1; d<=daysInMonth; d++){
    const day = document.createElement('div');
    day.className='calendar-day';
    const dateStr = year + '-' + String(month+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
    day.innerHTML = '<div class="date">'+d+'</div><div class="task-list" data-date="'+dateStr+'"></div>';
    // click to open modal prefill date
    day.addEventListener('dblclick', ()=>{ openModalWithDate(dateStr); });
    calendarEl.appendChild(day);
  }
  fetchTasksForMonth(year, month+1);
}

function fetchTasksForMonth(year, month){
  fetch('fetch_tasks.php?year='+year+'&month='+month)
    .then(r=>r.json())
    .then(data=>{
      // place tasks into days
      document.querySelectorAll('.task-list').forEach(el => el.innerHTML = '');
      data.forEach(t=>{
        const el = document.querySelector('.task-list[data-date="'+t.date+'"]');
        if(el){
          const b = document.createElement('a');
          b.href='#';
          b.className='task-block';
          b.textContent = t.title + (t.time_from?(' @'+t.time_from):'');
          b.style.background = t.color || '#ff6a00';
          b.dataset.id = t.id;
          b.addEventListener('click',(e)=>{ e.preventDefault(); if(confirm('Delete this activity?')) deleteTask(t.id); });
          el.appendChild(b);
        }
      });
    });
}

function deleteTask(id){
  fetch('delete_task.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})})
  .then(r=>r.json()).then(res=>{ if(res.success) renderCalendar(current); else alert(res.error||'Error'); });
}

document.getElementById('prev').addEventListener('click', ()=>{ current.setMonth(current.getMonth()-1); renderCalendar(current); });
document.getElementById('next').addEventListener('click', ()=>{ current.setMonth(current.getMonth()+1); renderCalendar(current); });
document.getElementById('addTaskBtn').addEventListener('click', ()=>{ openModalWithDate(new Date().toISOString().slice(0,10)); });
document.getElementById('closeModal').addEventListener('click', ()=>{ document.getElementById('modal').classList.remove('show'); });

function openModalWithDate(dateStr){
  document.querySelector('#taskForm [name=date]').value = dateStr;
  document.getElementById('modal').classList.add('show');
}

document.getElementById('taskForm').addEventListener('submit', function(e){
  e.preventDefault();
  const fd = new FormData(this);
  fetch('add_task.php',{method:'POST',body:fd}).then(r=>r.json()).then(res=>{
    if(res.success){ document.getElementById('modal').classList.remove('show'); renderCalendar(current); }
    else alert(res.error||'Error');
  });
});

renderCalendar(current);
</script>
</body></html>
