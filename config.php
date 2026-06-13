<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');
date_default_timezone_set('Europe/Budapest');

define('DB_HOST', 'localhost');
define('DB_NAME', 'orszagkozepe_hirdetesek');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_IMAGES', 10);
define('THUMBNAIL_WIDTH', 300);
define('BASE_URL', 'https://orszagkozepe.hu');
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);
define('PER_PAGE', 20);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime' => 86400 * 7, 'path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET];
        try { $pdo = new PDO($dsn, DB_USER, DB_PASS, $options); }
        catch (PDOException $e) { error_log("DB hiba: " . $e->getMessage()); http_response_code(500); die(json_encode(['success' => false, 'message' => 'Adatbázis hiba.'])); }
    }
    return $pdo;
}

function isLoggedIn(): bool { return isset($_SESSION['user_id']); }
function isAdmin(): bool { return isset($_SESSION['admin_id']); }
function getCurrentUserId(): ?int { return $_SESSION['user_id'] ?? null; }
function redirect(string $url): void { header("Location: {$url}"); exit; }
function jsonResponse(bool $success, string $message, array $data = [], int $statusCode = 200): void { http_response_code($statusCode); header('Content-Type: application/json; charset=utf-8'); echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE); exit; }