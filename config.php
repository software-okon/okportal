<?php
/**
 * config.php - Adatbázis kapcsolat és alapbeállítások
 * MÓDOSÍTVA: Alkönyvtár támogatás (http://localhost/orszagkozepe/)
 */

// Hibajelentés
error_reporting(E_ALL);
ini_set('display_errors', 0);  // Élesben 0, fejlesztésben 1
ini_set('log_errors', 1);

// Logs mappa ellenőrzése/létrehozása
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . '/error.log');

// Időzóna
date_default_timezone_set('Europe/Budapest');

// ========================
// ADATBÁZIS KAPCSOLAT
// ========================
define('DB_HOST', '127.0.0.1;port=3307');
define('DB_NAME', 'orszagkozepe_hirdetesek');
define('DB_USER', 'root');      // XAMPP: root
define('DB_PASS', '');          // XAMPP: üres, MAMP: root
define('DB_CHARSET', 'utf8mb4');

// ========================
// FÁJLFELTÖLTÉSI BEÁLLÍTÁSOK
// ========================
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);  // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_IMAGES', 10);
define('THUMBNAIL_WIDTH', 300);

// ========================
// URL BEÁLLÍTÁSOK (ALKÖNYVTÁR TÁMOGATÁSSAL)
// ========================
// Automatikusan érzékeli, hogy alkönyvtárban van-e, a config.php helyzete alapján
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$realDocRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$realDir = rtrim(str_replace('\\', '/', __DIR__), '/');

if (!empty($realDocRoot) && stripos($realDir, $realDocRoot) === 0) {
    $scriptDir = substr($realDir, strlen($realDocRoot));
} else {
    $scriptDir = '/orszagkozepe';
}
$scriptDir = rtrim(str_replace('\\', '/', $scriptDir), '/');

define('BASE_URL', $protocol . $host . $scriptDir);
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Ha kézzel akarod beállítani:
// define('BASE_URL', 'http://localhost/orszagkozepe');
// define('UPLOAD_URL', 'http://localhost/orszagkozepe/uploads/');

// ========================
// EGYÉB BEÁLLÍTÁSOK
// ========================
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);
define('PER_PAGE', 20);

// ========================
// MUNKAMENET INDÍTÁSA
// ========================
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,     // Élesben true
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Automatikus bejelentkezés emlékező token alapján
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM felhasznalok WHERE jelszo_visszaallitas_token = :token AND jelszo_token_lejarat > NOW() AND aktiv = 1");
        $stmt->execute([':token' => $_COOKIE['remember_token']]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nev'] = $user['nev'];
        }
    } catch (PDOException $e) {
        // Csendben elnyomjuk a hibát, ne törjön el az oldal ha nincs DB
        error_log("Automatikus bejelentkezés hiba: " . $e->getMessage());
    }
}

// ========================
// ADATBÁZIS KAPCSOLAT FÜGGVÉNY
// ========================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Adatbázis hiba: " . $e->getMessage());
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Adatbázis hiba.']));
        }
    }
    return $pdo;
}

// ========================
// SEGÉDFÜGGVÉNYEK
// ========================
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['admin_id']);
}

function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

function jsonResponse(bool $success, string $message, array $data = [], int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}
