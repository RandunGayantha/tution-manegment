<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/helpers.php';
renderHead('Dashboard');
$sid  = (int)$_SESSION['student_id'];
$conn = getDB();
$classes = dbQuery($conn, 'SELECT * FROM student_dashboard_view WHERE student_id = ?', [$sid]);
$attSum  = dbQuery($conn, 'SELECT * FROM attendance_summary_view WHERE student_id = ?', [$sid]);
$payments= dbQuery($conn, 'SELECT * FROM payment_status_view WHERE student_id = ?', [$sid]);
$results = dbQuery($conn, 'SELECT r.*,c.class_name,s.subject_name FROM results r
    JOIN enrollments e ON e.enrollment_id=r.enrollment_id
    JOIN classes c ON c.class_id=e.class_id
    JOIN subjects s ON s.subject_id=c.subject_id
    WHERE e.student_id=? ORDER BY r.exam_date DESC', [$sid]);
$announcements = dbQuery($conn, 'SELECT a.*,c.class_name,CONCAT(t.first_name," ",t.last_name) AS teacher_name
    FROM announcements a JOIN classes c ON c.class_id=a.class_id
    JOIN teachers t ON t.teacher_id=a.teacher_id
    WHERE a.class_id IN (SELECT class_id FROM enrollments WHERE student_id=?)
    ORDER BY a.posted_at DESC', [$sid]);
$conn->close();
$firstName  = htmlspecialchars($_SESSION['first_name'] ?? 'Student');
$today      = date('l, d F Y');
$paidCount  = count(array_filter($payments, fn($p) => $p['status']==='Paid'));
$attOverall = $attSum ? (int)round(array_sum(array_column($attSum,'attendance_pct'))/count($attSum)) : 0;
$avgM       = avgMarks($results);
renderSidebar('dashboard');
?>
<div class="main-content">
  <div class="page-header">
    <h1>Welcome back, <?= $firstName ?> 👋</h1>
    <p><?= $today ?></p>
  </div>
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-value"><?= count($classes) ?></div><div class="stat-label">Enrolled Classes</div></div>
    <div class="stat-card"><div class="stat-value <?= $attOverall>=80?'green':'amber' ?>"><?= $attOverall ?>%</div><div class="stat-label">Attendance Rate</div></div>
    <div class="stat-card"><div class="stat-value"><?= $avgM ?>%</div><div class="stat-label">Average Marks</div></div>
    <div class="stat-card"><div class="stat-value purple"><?= $paidCount ?></div><div class="stat-label">Payments Made</div></div>
  </div>
  <div class="two-col">
    <div>
      <div class="section-label">📖 My Classes</div>
      <?php foreach($classes as $c): ?>
      <div class="card-accent">
        <div style="font-weight:600;margin-bottom:0.2rem;"><?= htmlspecialchars($c['class_name']) ?></div>
        <div style="font-size:0.82rem;color:var(--muted);"><?= htmlspecialchars($c['teacher_name']) ?> · <?= htmlspecialchars($c['schedule_day']) ?> <?= substr($c['start_time'],0,5) ?>–<?= substr($c['end_time'],0,5) ?></div>
        <div style="font-size:0.79rem;color:var(--accent);margin-top:0.4rem;"><?= htmlspecialchars($c['subject_name']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div>
      <div class="section-label">📢 Latest Notices</div>
      <?php foreach(array_slice($announcements,0,3) as $a): ?>
      <div class="ann-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.25rem;">
          <div class="ann-title"><?= htmlspecialchars($a['title']) ?></div>
          <?= badge($a['class_name'],'blue') ?>
        </div>
        <div class="ann-meta"><?= htmlspecialchars($a['teacher_name']) ?> · <?= fmtDate($a['posted_at']) ?></div>
        <div class="ann-body"><?= htmlspecialchars(substr($a['content'],0,90)) ?>…</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="section-label">📊 Recent Results</div>
  <div class="card">
    <div class="table-wrap">
      <table class="etable">
        <thead><tr><th>Subject</th><th>Exam</th><th>Marks</th><th>Grade</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach(array_slice($results,0,5) as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['subject_name']) ?></td>
            <td><?= htmlspecialchars($r['exam_type']) ?></td>
            <td><?= $r['marks_obtained'] ?>/<?= $r['total_marks'] ?></td>
            <td><?= gradeBadge($r['grade']) ?></td>
            <td><?= fmtDate($r['exam_date']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(!$results): ?><tr><td colspan="5" style="color:var(--muted);">No results yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div></body></html>