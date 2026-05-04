<?php
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['student_id'])) {
        header('Location: /STUDENTDASHBOARD/backend-php/index.php?page=login');
        exit;
    }
}

function currentStudent(): array {
    return [
        'student_id' => $_SESSION['student_id']   ?? 0,
        'name'       => $_SESSION['student_name'] ?? '',
        'email'      => $_SESSION['email']        ?? '',
        'first_name' => $_SESSION['first_name']   ?? '',
    ];
}

function isLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['student_id']);
}
?>