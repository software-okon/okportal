<?php
/**
 * auth/login.php - Felhasználó bejelentkezés
 */
require_once __DIR__ . '/../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Csak POST metódus engedélyezett!', [], 405);
}

$email    = cleanInput($_POST['email'] ?? '');
$password = $_POST['jelszo'] ?? '';
$remember = isset($_POST['emlekezzen']);

if (empty($email) || empty($password)) {
    jsonResponse(false, 'Az e-mail és a jelszó megadása kötelező!', [], 422);
}

$result = loginUser($email, $password);

if ($result['success']) {
    // "Emlékezzen rám" funkció
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE felhasznalok SET jelszo_visszaallitas_token = :token, jelszo_token_lejarat = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = :id");
        $stmt->execute([':token' => $token, ':id' => $result['user']['id']]);
        
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
    }
    
    jsonResponse(true, 'Sikeres bejelentkezés!', [
        'user' => [
            'id'    => $result['user']['id'],
            'email' => $result['user']['email'],
            'nev'   => $result['user']['nev']
        ],
        'redirect' => BASE_URL . '/fiokom'
    ]);
} else {
    jsonResponse(false, $result['message'], [], 401);
}