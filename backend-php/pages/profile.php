<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/helpers.php';
renderHead('My Profile');
$sid  = (int)$_SESSION['student_id'];
$conn = getDB();
$rows = dbQuery($conn,'SELECT * FROM students WHERE student_id=?',[$sid]);
$conn->close();
$s   = $rows[0] ?? [];
$sidp= str_pad($s['student_id']??0,4,'0',STR_PAD_LEFT);
$fields=[
  'Full Name'         => ($s['first_name']??'').' '.($s['last_name']??''),
  'Email'             => $s['email']??'—',
  'Phone'             => $s['phone']??'—',
  'Date of Birth'     => fmtDate($s['dob']??''),
  'Address'           => $s['address']??'—',
  'Parent / Guardian' => $s['parent_name']??'—',
  'Parent Contact'    => $s['parent_phone']??'—',
  'Member Since'      => fmtDate($s['created_at']??''),
];
renderSidebar('profile');
?>
<div class="main-content">
  <div class="page-header"><h1>My Profile</h1><p>Your personal information</p></div>
  <div class="two-col-e">
    <div class="card" style="text-align:center;padding:2rem;">
      <div style="width:80px;height:80px;background:var(--bg3);border-radius:50%;margin:0 auto 1rem;
                  display:flex;align-items:center;justify-content:center;font-size:2rem;border:2px solid var(--accent);">👤</div>
      <div style="font-family:'Playfair Display',serif;font-size:1.15rem;margin-bottom:0.3rem;">
        <?= htmlspecialchars(($s['first_name']??'').' '.($s['last_name']??'')) ?>
      </div>
      <div style="font-size:0.82rem;color:var(--muted);margin-bottom:0.8rem;"><?= htmlspecialchars($s['email']??'') ?></div>
      <?= badge('Active Student','green') ?>
      <hr style="border:none;border-top:1px solid var(--border);margin:1rem 0;">
      <div style="font-size:0.75rem;color:var(--muted);">Student ID</div>
      <div style="font-size:1rem;color:var(--accent2);font-weight:600;">#<?= $sidp ?></div>
    </div>
    <div class="card">
      <div style="font-weight:600;margin-bottom:1rem;">Personal Information</div>
      <?php foreach($fields as $label=>$value): ?>
      <div style="display:flex;justify-content:space-between;padding:0.7rem 0;border-bottom:1px solid var(--border);">
        <div style="font-size:0.8rem;color:var(--muted);"><?= $label ?></div>
        <div style="font-size:0.87rem;"><?= htmlspecialchars($value) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div></div></body></html>