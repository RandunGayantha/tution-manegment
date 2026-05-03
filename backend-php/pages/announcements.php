<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/helpers.php';
renderHead('Announcements');
$sid  = (int)$_SESSION['student_id'];
$conn = getDB();
$announcements = dbQuery($conn,'SELECT a.*,c.class_name,CONCAT(t.first_name," ",t.last_name) AS teacher_name
    FROM announcements a JOIN classes c ON c.class_id=a.class_id
    JOIN teachers t ON t.teacher_id=a.teacher_id
    WHERE a.class_id IN (SELECT class_id FROM enrollments WHERE student_id=?)
    ORDER BY a.posted_at DESC',[$sid]);
$conn->close();
renderSidebar('announcements');
?>
<div class="main-content">
  <div class="page-header"><h1>Announcements</h1><p>Notices from your teachers</p></div>
  <?php if($announcements): ?>
    <?php foreach($announcements as $a): ?>
    <div class="ann-card" style="padding:1.2rem 1.4rem;border-radius:0 14px 14px 0;margin-bottom:1rem;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.35rem;">
        <div class="ann-title"><?= htmlspecialchars($a['title']) ?></div>
        <?= badge($a['class_name'],'blue') ?>
      </div>
      <div class="ann-meta"><?= htmlspecialchars($a['teacher_name']) ?> · <?= fmtDate($a['posted_at']) ?></div>
      <div class="ann-body"><?= nl2br(htmlspecialchars($a['content'])) ?></div>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="color:var(--muted);">No announcements yet.</p>
  <?php endif; ?>
</div></div></body></html>