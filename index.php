<?php
// Landing page - index.php
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Task Manager</title>
<style>
/* Reset and base styles */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f4f7f8; color:#333; line-height:1.6; }

/* Navbar */
.navbar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 40px;
    background:linear-gradient(to right, #6a11cb, #2575fc);
    color:#fff;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
}
.navbar .logo { font-size:24px; font-weight:bold; }
.navbar nav a {
    color:#fff;
    text-decoration:none;
    margin-left:20px;
    font-weight:500;
    transition:0.3s;
}
.navbar nav a:hover { text-decoration:underline; }

/* Hero Section */
.hero {
    display:flex;
    flex-wrap:wrap;
    justify-content:space-between;
    align-items:center;
    padding:80px 40px;
    background:linear-gradient(to right, #6a11cb, #2575fc);
    color:#fff;
}
.hero .left {
    flex:1;
    min-width:280px;
    padding-right:20px;
}
.hero .left h1 { font-size:48px; margin-bottom:20px; }
.hero .left p { font-size:18px; max-width:500px; }
.hero .right {
    flex:1;
    min-width:280px;
}
.form-box {
    background:#fff;
    color:#333;
    padding:30px 25px;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
}
.form-box h2 { text-align:center; margin-bottom:20px; }
.form-box input { width:100%; padding:12px; margin:10px 0; border-radius:6px; border:1px solid #ccc; }
.form-box button {
    width:100%;
    padding:12px;
    background:#2575fc;
    color:#fff;
    border:none;
    border-radius:6px;
    font-size:16px;
    cursor:pointer;
    transition:0.3s;
}
.form-box button:hover { background:#6a11cb; }
.form-box a { color:#2575fc; text-decoration:none; }
.form-box a:hover { text-decoration:underline; }

/* Services Section */
.services {
    padding:60px 40px;
    text-align:center;
    background:#fff;
}
.services h2 { font-size:32px; margin-bottom:40px; }
.service-items { display:flex; flex-wrap:wrap; justify-content:center; gap:20px; }
.service { background:#f4f7f8; padding:25px; border-radius:12px; width:250px; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:0.3s; }
.service:hover { transform:translateY(-5px); box-shadow:0 6px 18px rgba(0,0,0,0.15); }
.service h3 { margin-bottom:10px; color:#2575fc; }
.service p { font-size:14px; }

/* Testimonials */
.testimonials {
    padding:60px 40px;
    background:#f0f4f8;
    text-align:center;
}
.testimonials h2 { font-size:32px; margin-bottom:40px; }
.testimonial {
    background:#fff;
    max-width:500px;
    margin:0 auto 20px auto;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    font-style:italic;
}

/* Responsive */
@media(max-width:900px) { .hero { flex-direction:column; } .hero .left, .hero .right { padding:0; text-align:center; } }
@media(max-width:600px) { .service-items { flex-direction:column; align-items:center; } }
</style>
</head>
<body>

<header class="navbar">
  <div class="logo">TaskManager</div>
  <nav>
    <a href="#services">Services</a>
    <a href="#testimonials">Testimonials</a>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
  </nav>
</header>

<section class="hero">
  <div class="left">
    <h1>Welcome to Task Manager</h1>
    <p>Stay organized, stay ahead. Plan, track, and complete tasks effortlessly with our modern task management system.</p>
  </div>
  <div class="right">
    <form action="login.php" method="get" class="form-box">
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
