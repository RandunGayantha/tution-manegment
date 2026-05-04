<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = trim($_POST['email']    ?? '');
    $password= trim($_POST['password'] ?? '');
    $conn    = getDB();
    $rows    = dbQuery($conn, 'SELECT * FROM students WHERE email = ? AND password_hash = SHA2(?, 256)', [$email, $password]);
    $conn->close();
    if ($rows) {
        $s = $rows[0];
        $_SESSION['student_id']   = $s['student_id'];
        $_SESSION['student_name'] = $s['first_name'] . ' ' . $s['last_name'];
        $_SESSION['first_name']   = $s['first_name'];
        $_SESSION['email']        = $s['email'];
        header('Location: /STUDENTDASHBOARD/backend-php/index.php?page=dashboard');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>EduTrack — Sign In</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{--bg:#0b0f1a;--bg2:#111827;--bg3:#1a2236;--border:#1f2d45;--border2:#2a3d5c;
          --text:#e2ddd6;--muted:#6b7891;--accent:#4f8ef7;--accent2:#7eb5ff;--red:#f87171;}
    body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;
         display:flex;align-items:center;justify-content:center;
         background-image:radial-gradient(ellipse 60% 50% at 20% 80%,rgba(79,142,247,0.07) 0%,transparent 60%),
                          radial-gradient(ellipse 50% 40% at 80% 20%,rgba(167,139,250,0.05) 0%,transparent 50%);}
    .box{width:100%;max-width:420px;background:var(--bg2);border:1px solid var(--border);
         border-radius:20px;padding:2.8rem 2.4rem;box-shadow:0 4px 32px rgba(0,0,0,0.4);}
    .logo{text-align:center;margin-bottom:2rem;}
    .logo span{font-size:2.4rem;display:block;margin-bottom:0.5rem;}
    .logo h1{font-family:'Playfair Display',serif;font-size:1.75rem;color:var(--accent2);margin-bottom:0.3rem;}
    .logo p{color:var(--muted);font-size:0.85rem;}
    label{display:block;font-size:0.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.4rem;}
    .field{margin-bottom:1.1rem;}
    input[type=email],input[type=password]{width:100%;padding:0.75rem 1rem;background:var(--bg3);
      border:1px solid var(--border2);border-radius:10px;color:var(--text);
      font-family:'Outfit',sans-serif;font-size:0.95rem;outline:none;transition:border-color 0.2s;}
    input:focus{border-color:var(--accent);}
    .btn{width:100%;padding:0.78rem;background:var(--accent);color:#fff;border:none;border-radius:10px;
         font-family:'Outfit',sans-serif;font-weight:600;font-size:0.95rem;cursor:pointer;transition:background 0.2s;}
    .btn:hover{background:#3a7de0;}
    .err{background:#2d0d0d;border:1px solid #991b1b;color:var(--red);border-radius:8px;
         padding:0.65rem 1rem;font-size:0.85rem;margin-bottom:1rem;}
    .hint{margin-top:1.4rem;padding-top:1.2rem;border-top:1px solid var(--border);
          font-size:0.78rem;color:var(--muted);text-align:center;}
  </style>
</head>
<body>
<div class="box">
  <div class="logo">
    <span>📚</span>
    <h1>EduTrack</h1>
    <p>Tuition Class Student Portal</p>
  </div>
  <?php if($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <div class="field">
      <label>Email Address</label>
      <input type="email" name="email" placeholder="yourname@student.lk" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn">Sign In →</button>
  </form>
  <div class="hint">Demo: vimansa@student.lk / student123</div>
</div>
</body></html>