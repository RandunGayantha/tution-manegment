<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/helpers.php';
renderHead('Attendance');
$sid  = (int)$_SESSION['student_id'];
$conn = getDB();
$classes = dbQuery($conn,'SELECT DISTINCT class_name FROM student_dashboard_view WHERE student_id=?',[$sid]);
$records = dbQuery($conn,'SELECT a.attendance_date,a.status,c.class_name
    FROM attendance a JOIN enrollments e ON e.enrollment_id=a.enrollment_id
    JOIN classes c ON c.class_id=e.class_id
    WHERE e.student_id=? ORDER BY a.attendance_date DESC',[$sid]);
$conn->close();
renderSidebar('attendance');
?>
<div class="main-content">
  <div class="page-header"><h1>Attendance</h1><p>Session-by-session attendance record</p></div>
  <?php foreach($classes as $cls):
    $cn  = $cls['class_name'];
    $rec = array_values(array_filter($records,fn($r)=>$r['class_name']===$cn));
    $present = count(array_filter($rec,fn($r)=>$r['status']==='Present'));
    $absent  = count(array_filter($rec,fn($r)=>$r['status']==='Absent'));
    $late    = count(array_filter($rec,fn($r)=>$r['status']==='Late'));
    $pct     = $rec ? (int)round($present/count($rec)*100) : 0; ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem;">
      <div style="font-weight:600;"><?= htmlspecialchars($cn) ?></div>
      <div style="display:flex;gap:1rem;font-size:0.82rem;">
        <span style="color:var(--green);">● <?= $present ?> Present</span>
        <span style="color:var(--red);">● <?= $absent ?> Absent</span>
        <span style="color:var(--amber);">● <?= $late ?> Late</span>
        <span style="color:var(--accent2);font-weight:600;"><?= $pct ?>%</span>
      </div>
    </div>
    <?= progressBar($pct) ?>
    <div style="margin-top:1rem;" class="table-wrap">
      <table class="etable">
        <thead><tr><th>Date</th><th>Class</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach($rec as $r): ?>
          <tr><td><?= fmtDate($r['attendance_date']) ?></td><td><?= htmlspecialchars($r['class_name']) ?></td><td><?= attBadge($r['status']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
</div></div></body></html>