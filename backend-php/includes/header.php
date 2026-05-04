<?php
function renderHead(string $title = 'EduTrack'): void { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($title) ?> — EduTrack</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg:#0b0f1a; --bg2:#111827; --bg3:#1a2236;
      --border:#1f2d45; --border2:#2a3d5c;
      --text:#e2ddd6; --muted:#6b7891;
      --accent:#4f8ef7; --accent2:#7eb5ff;
      --green:#34d399; --amber:#fbbf24;
      --red:#f87171; --purple:#a78bfa;
      --radius:14px;
    }
    html { font-size: 16px; }
    body {
      font-family:'Outfit',sans-serif;
      background:var(--bg); color:var(--text);
      min-height:100vh; -webkit-font-smoothing:antialiased;
    }
    h1,h2,h3 { font-family:'Playfair Display',serif; }
    a { color:inherit; text-decoration:none; }
    ::-webkit-scrollbar { width:5px; }
    ::-webkit-scrollbar-track { background:var(--bg2); }
    ::-webkit-scrollbar-thumb { background:var(--border2); border-radius:3px; }
    .app-layout { display:flex; min-height:100vh; }
    .main-content {
      flex:1; padding:2rem 2.5rem;
      background:radial-gradient(ellipse 50% 40% at 90% 10%,
        rgba(79,142,247,0.05) 0%,transparent 55%),var(--bg);
    }
    .page-header { margin-bottom:1.8rem; }
    .page-header h1 { font-size:1.9rem; margin-bottom:0.2rem; }
    .page-header p { color:var(--muted); font-size:0.88rem; }
    .card {
      background:var(--bg2); border:1px solid var(--border);
      border-radius:var(--radius); padding:1.4rem 1.6rem; margin-bottom:1rem;
    }
    .card-accent {
      background:linear-gradient(135deg,#1a2540,#0f1117);
      border:1px solid #2e4070; border-radius:var(--radius);
      padding:1.2rem 1.4rem; margin-bottom:0.8rem;
    }
    .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.8rem; }
    .stats-grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.8rem; }
    .stat-card {
      background:var(--bg2); border:1px solid var(--border);
      border-radius:var(--radius); padding:1.2rem 1.5rem; text-align:center;
    }
    .stat-value { font-family:'Playfair Display',serif; font-size:2rem; color:var(--accent2); line-height:1; margin-bottom:0.3rem; }
    .stat-value.green { color:var(--green); }
    .stat-value.amber { color:var(--amber); }
    .stat-value.purple { color:var(--purple); }
    .stat-label { font-size:0.72rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); font-weight:500; }
    .badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:0.73rem; font-weight:600; letter-spacing:0.04em; }
    .badge-green  { background:#0d2e1a; color:#34d399; border:1px solid #166534; }
    .badge-blue   { background:#0d1f3c; color:#7eb5ff; border:1px solid #1e40af; }
    .badge-amber  { background:#2d1d00; color:#fbbf24; border:1px solid #92400e; }
    .badge-red    { background:#2d0d0d; color:#f87171; border:1px solid #991b1b; }
    .badge-purple { background:#1a0d2e; color:#a78bfa; border:1px solid #6b21a8; }
    .table-wrap { overflow-x:auto; }
    table.etable { width:100%; border-collapse:collapse; font-size:0.87rem; }
    table.etable th {
      text-align:left; padding:10px 14px; background:#131d30; color:var(--muted);
      font-size:0.72rem; letter-spacing:0.08em; text-transform:uppercase;
      font-weight:500; border-bottom:1px solid var(--border);
    }
    table.etable td { padding:12px 14px; color:var(--text); border-bottom:1px solid var(--border); }
    table.etable tr:last-child td { border-bottom:none; }
    table.etable tr:hover td { background:#131d30; }
    .prog-bg { background:#1a2236; border-radius:6px; height:8px; overflow:hidden; }
    .prog-fill { height:8px; border-radius:6px; transition:width 0.6s ease; }
    .prog-label { font-size:0.78rem; color:var(--muted); margin-bottom:0.35rem; }
    .two-col { display:grid; grid-template-columns:1.3fr 1fr; gap:1.2rem; }
    .two-col-e { display:grid; grid-template-columns:1fr 1.6fr; gap:1.5rem; }
    .ann-card {
      background:var(--bg2); border-left:3px solid var(--accent);
      border-radius:0 12px 12px 0; padding:1rem 1.2rem; margin-bottom:0.8rem;
    }
    .ann-title { font-weight:600; color:var(--text); margin-bottom:0.25rem; }
    .ann-meta { font-size:0.78rem; color:var(--muted); margin-bottom:0.45rem; }
    .ann-body { font-size:0.87rem; color:#9ca3af; line-height:1.6; }
    .section-label { font-weight:600; color:var(--text); margin:1.2rem 0 0.7rem; }
    @media(max-width:900px) {
      .stats-grid,.stats-grid-3 { grid-template-columns:repeat(2,1fr); }
      .two-col,.two-col-e { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>
<div class="app-layout">
<?php } ?>