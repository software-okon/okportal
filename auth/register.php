<?php
/**
 * auth/register.php - Felhasználó regisztráció
 */
require_once __DIR__ . '/../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Csak POST metódus engedélyezett!', [], 405);
}

// Bemenetek tisztítása
$email    = cleanInput($_POST['email'] ?? '');
$password = $_POST['jelszo'] ?? '';
$password2 = $_POST['jelszo2'] ?? '';
$nev      = cleanInput($_POST['nev'] ?? '');
$telefon  = cleanInput($_POST['telefon'] ?? '');

// Validáció
$errors = [];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Érvénytelen e-mail cím!';
}

if (mb_strlen($password) < 8) {
    $errors[] = 'A jelszónak legalább 8 karakterből kell állnia!';
}
if (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'A jelszónak tartalmaznia kell legalább egy nagybetűt!';
}
if (!preg_match('/[0-9]/', $password)) {
    $errors[] = 'A jelszónak tartalmaznia kell legalább egy számot!';
}
if ($password !== $password2) {
    $errors[] = 'A két jelszó nem egyezik!';
}

if (mb_strlen($nev) < 2) {
    $errors[] = 'A névnek legalább 2 karakterből kell állnia!';
}

if (!empty($telefon)) {
    $cleaned = preg_replace('/[\s\-\(\)]/', '', $telefon);
    if (!preg_match('/^(\+36|06)\d{8,9}$/', $cleaned)) {
        $errors[] = 'Érvénytelen telefonszám formátum!';
    }
}

// ÁSZF elfogadása
if (empty($_POST['aszf'])) {
    $errors[] = 'A felhasználási feltételek elfogadása kötelező!';
}

if (!empty($errors)) {
    jsonResponse(false, implode('<br>', $errors), ['errors' => $errors], 422);
}

// Regisztráció végrehajtása
$result = registerUser($email, $password, $nev, !empty($telefon) ? $telefon : null);

if ($result['success']) {
    // Opcionális: email küldése a megerősítő linkkel
    // sendVerificationEmail($email, $result['email_token']);
    
    jsonResponse(true, 'Sikeres regisztráció! Most már bejelentkezhetsz.', [
        'user_id' => $result['user_id']
    ]);
} else {
    jsonResponse(false, $result['message'], [], 400);
}
