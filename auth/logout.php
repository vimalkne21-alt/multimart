<?php
/**
 * MultiMart - Logout Handler
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (is_logged_in()) {
    log_activity('LOGOUT', 'USER', $_SESSION['user_id'] ?? null, "User logged out.");
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start fresh session for flash message
session_start();
set_flash('info', 'You have been successfully signed out.');
header("Location: " . base_url('index.php'));
exit;
