<?php
/**
 * auth/logout.php - Kijelentkezés
 */
require_once __DIR__ . '/../config.php';

// Munkamenet törlése
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

// Emlékezzen rám süti törlése
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

redirect(BASE_URL);