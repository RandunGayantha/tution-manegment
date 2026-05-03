<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

$page = $_GET['page'] ?? 'login';

if ($page === 'login' && isLoggedIn()) {
    header('Location: /STUDENTDASHBOARD/backend-php/index.php?page=dashboard');
    exit;
}
if ($page === 'logout') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION = [];
    session_destroy();
    header('Location: /STUDENTDASHBOARD/backend-php/index.php?page=login');
    exit;
}
$protected = ['dashboard','classes','attendance','results','payments','announcements','profile'];
if (in_array($page, $protected)) requireLogin();

$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (file_exists($pageFile)) {
    require $pageFile;
} else {
    header('Location: /STUDENTDASHBOARD/backend-php/index.php?page=dashboard');
    exit;
}
?>