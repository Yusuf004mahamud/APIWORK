<?php
session_start();
require_once 'config.php';
require_once 'utils.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    redirect('calendar.php');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Task Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
  <div class="logo">TaskManager</div>
  <nav>
    <a href="#services">Services</a>
    <a href="#testimonials">Testimonials</a>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
    <a href="change_password.php">Change Password</a> <!-- Added link for logged-in users -->
  </nav>
</header>

<section class="hero">
  <div class="left">
    <h1>Welcome to Task Manager</h1>
    <p>Stay organized, stay ahead. Plan, track, and complete tasks effortlessly with our modern task management system.</p>
  </div>
  <div class="right">
    <form action="login.php" method="post" class="form-box">
      <h2>Sign In</h2>
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
      <p style="text-align:center;margin-top:8px;"><a href="register.php">Create account</a></p>
    </form>
  </div>
</section>

<section id="services" class="services">
  <h2>Our Services</h2>
  <div class="service-items">
    <div class="service"><h3>Smart Scheduling</h3><p>Automatically organize your tasks and deadlines efficiently.</p></div>
    <div class="service"><h3>Reminders</h3><p>Never miss an important task with timely notifications.</p></div>
    <div class="service"><h3>Collaboration</h3><p>Share tasks with your team and boost productivity together.</p></div>
  </div>
</section>

<section id="testimonials" class="testimonials">
  <h2>What People Say</h2>
  <div class="testimonial">“Task Manager changed my productivity!” – Jane D.</div>
  <div class="testimonial">“So easy and beautiful.” – Mark P.</div>
</section>

</body>
</html>
