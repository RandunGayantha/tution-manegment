<?php
function renderSidebar(string $active = ''): void {
    $s = currentStudent();
    $nav = [
        ['page'=>'dashboard',     'icon'=>'⊞',  'label'=>'Dashboard'],
        ['page'=>'classes',       'icon'=>'📖', 'label'=>'My Classes'],
        ['page'=>'attendance',    'icon'=>'📋', 'label'=>'Attendance'],
        ['page'=>'results',       'icon'=>'📊', 'label'=>'Results'],
        ['page'=>'payments',      'icon'=>'💳', 'label'=>'Payments'],
        ['page'=>'announcements', 'icon'=>'📢', 'label'=>'Announcements'],
        ['page'=>'profile',       'icon'=>'👤', 'label'=>'My Profile'],
    ];
    $sid = str_pad($s['student_id'], 4, '0', STR_PAD_LEFT);
?>
<aside style="width:240px;min-height:100vh;background:var(--bg2);border-right:1px solid var(--border);
              display:flex;flex-direction:column;padding:1.5rem 1rem;position:sticky;top:0;flex-shrink:0;">
  <div style="margin-bottom:1.8rem;padding-left:0.5rem;">
    <div style="font-family:'Playfair Display',serif;font-size:1.35rem;color:var(--accent2);margin-bottom:0.15rem;">📚 EduTrack</div>
    <div style="font-size:0.7rem;color:var(--muted);letter-spacing:0.1em;text-transform:uppercase;">Student Portal</div>
  </div>
  <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:0.9rem;margin-bottom:1.5rem;">
    <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($s['name']) ?></div>
    <div style="font-size:0.75rem;color:var(--muted);margin-top:0.1rem;"><?= htmlspecialchars($s['email']) ?></div>
    <div style="font-size:0.72rem;color:var(--accent);margin-top:0.35rem;">ID #<?= $sid ?></div>
  </div>
  <nav style="flex:1;">
    <?php foreach ($nav as $item):
      $isActive = $active === $item['page'];
      $bg    = $isActive ? 'var(--bg3)'     : 'transparent';
      $color = $isActive ? 'var(--accent2)' : 'var(--muted)';
      $bdr   = $isActive ? '1px solid var(--border2)' : '1px solid transparent';
    ?>
    <a href="/STUDENTDASHBOARD/backend-php/index.php?page=<?= $item['page'] ?>"
       style="display:flex;align-items:center;gap:0.65rem;padding:0.55rem 0.75rem;
              border-radius:8px;margin-bottom:0.2rem;font-size:0.9rem;
              color:<?= $color ?>;background:<?= $bg ?>;border:<?= $bdr ?>;transition:all 0.15s;">
      <span><?= $item['icon'] ?></span><?= $item['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <a href="/STUDENTDASHBOARD/backend-php/index.php?page=logout"
     style="display:block;padding:0.55rem;border-radius:8px;background:transparent;
            border:1px solid var(--border);color:var(--muted);font-size:0.87rem;
            text-align:center;margin-top:1rem;transition:all 0.2s;"
     onmouseover="this.style.borderColor='var(--red)';this.style.color='var(--red)';"
     onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)';">
    🚪 Sign Out
  </a>
</aside>
<?php } ?>