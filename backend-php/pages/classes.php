<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/helpers.php';
renderHead('My Classes');
$sid  = (int)$_SESSION['student_id'];
$conn = getDB();
$classes = dbQuery($conn,'SELECT * FROM student_dashboard_view WHERE student_id=?',[$sid]);
$attSum  = dbQuery($conn,'SELECT * FROM attendance_summary_view WHERE student_id=?',[$sid]);
$conn->close();
$attByClass = array_column($attSum,'attendance_pct','class_name');
renderSidebar('classes');
?>
<div class="main-content">
  <div class="page-header"><h1>My Classes</h1><p>Enrolled subjects and schedules</p></div>
  <?php foreach($classes as $c):
    $att = (int)($attByClass[$c['class_name']] ?? 0); ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.9rem;">
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:1.1rem;"><?= htmlspecialchars($c['class_name']) ?></div>
        <div style="font-size:0.82rem;color:var(--muted);margin-top:0.2rem;"><?= htmlspecialchars($c['subject_name']) ?></div>
      </div>
      <?= badge($c['enrollment_status'],'green') ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1rem;">
      <div><div style="font-size:0.7rem;color:var(--muted);text-transform:uppercase;">Teacher</div>
           <div style="font-size:0.87rem;margin-top:0.25rem;"><?= htmlspecialchars($c['teacher_name']) ?></div></div>
      <div><div style="font-size:0.7rem;color:var(--muted);text-transform:uppercase;">Schedule</div>
           <div style="font-size:0.87rem;margin-top:0.25rem;"><?= htmlspecialchars($c['schedule_day']) ?><br><?= substr($c['start_time'],0,5) ?>–<?= substr($c['end_time'],0,5) ?></div></div>
      <div><div style="font-size:0.7rem;color:var(--muted);text-transform:uppercase;">Subject</div>
           <div style="font-size:0.87rem;margin-top:0.25rem;"><?= htmlspecialchars($c['subject_name']) ?></div></div>
    </div>
    <div class="prog-label">Attendance — <?= $att ?>%</div>
    <?= progressBar($att) ?>
  </div>
  <?php endforeach; ?>
</div></div></body></html>