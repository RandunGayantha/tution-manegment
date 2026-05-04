<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/helpers.php';
renderHead('Results');
$sid  = (int)$_SESSION['student_id'];
$conn = getDB();
$results = dbQuery($conn,'SELECT r.*,c.class_name,s.subject_name FROM results r
    JOIN enrollments e ON e.enrollment_id=r.enrollment_id
    JOIN classes c ON c.class_id=e.class_id
    JOIN subjects s ON s.subject_id=c.subject_id
    WHERE e.student_id=? ORDER BY r.exam_date DESC',[$sid]);
$conn->close();
$avg  = avgMarks($results);
$best = $results ? array_reduce($results,fn($c,$r)=>(!$c||$r['marks_obtained']>$c['marks_obtained'])?$r:$c,null) : null;
$subjects = array_unique(array_column($results,'subject_name'));
renderSidebar('results');
?>
<div class="main-content">
  <div class="page-header"><h1>Exam Results</h1><p>Your academic performance</p></div>
  <div class="stats-grid-3">
    <div class="stat-card"><div class="stat-value"><?= $avg ?>%</div><div class="stat-label">Overall Average</div></div>
    <div class="stat-card"><div class="stat-value purple"><?= count($results) ?></div><div class="stat-label">Exams Taken</div></div>
    <div class="stat-card"><div class="stat-value green"><?= $best?$best['marks_obtained'].'%':'—' ?></div><div class="stat-label">Best Score</div></div>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table class="etable">
        <thead><tr><th>Subject</th><th>Exam</th><th>Marks</th><th>Percentage</th><th>Grade</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach($results as $r):
            $pct=round($r['marks_obtained']/$r['total_marks']*100,1); ?>
          <tr>
            <td><?= htmlspecialchars($r['subject_name']) ?></td>
            <td><?= htmlspecialchars($r['exam_type']) ?></td>
            <td><?= $r['marks_obtained'] ?>/<?= $r['total_marks'] ?></td>
            <td><?= $pct ?>%</td>
            <td><?= gradeBadge($r['grade']) ?></td>
            <td><?= fmtDate($r['exam_date']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if($subjects): ?>
  <div class="section-label">Performance by Subject</div>
  <div style="display:grid;grid-template-columns:repeat(<?= count($subjects) ?>,1fr);gap:1rem;">
    <?php foreach($subjects as $sub):
      $subR=array_values(array_filter($results,fn($r)=>$r['subject_name']===$sub));
      $subAvg=avgMarks($subR); ?>
    <div class="card" style="text-align:center;">
      <div style="font-size:0.8rem;color:var(--muted);margin-bottom:0.4rem;"><?= htmlspecialchars($sub) ?></div>
      <div style="font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--accent2);"><?= $subAvg ?>%</div>
      <div style="margin-top:0.6rem;"><?= progressBar((int)$subAvg) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></div></body></html>