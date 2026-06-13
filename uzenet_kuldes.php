<?php
/**
 * uzenet_kuldes.php - Üzenet küldése egy hirdetés hirdetőjének
 */
require_once __DIR__ . '/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Csak POST metódus!', [], 405);
}

$hirdetesId   = (int)($_POST['hirdetes_id'] ?? 0);
$kuldoNev     = cleanInput($_POST['kuldo_nev'] ?? '');
$kuldoEmail   = cleanInput($_POST['kuldo_email'] ?? '');
$kuldoTelefon = cleanInput($_POST['kuldo_telefon'] ?? '');
$targy        = cleanInput($_POST['targy'] ?? '');
$uzenet       = trim($_POST['uzenet'] ?? '');

// Validáció
$errors = [];
if ($hirdetesId <= 0) $errors[] = 'Érvénytelen hirdetés!';
if (mb_strlen($kuldoNev) < 2) $errors[] = 'A név legalább 2 karakter!';
if (!filter_var($kuldoEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Érvénytelen e-mail!';
if (empty($targy)) $errors[] = 'A tárgy megadása kötelező!';
if (mb_strlen($uzenet) < 10) $errors[] = 'Az üzenet legalább 10 karakter!';

if (!empty($errors)) {
    jsonResponse(false, 'Validációs hiba!', ['errors' => $errors], 422);
}

// Hirdetés ellenőrzése
$stmt = $pdo->prepare("SELECT id, email, elado_nev FROM hirdetesek WHERE id = :id AND statusz = 'aktiv'");
$stmt->execute([':id' => $hirdetesId]);
$hirdetes = $stmt->fetch();

if (!$hirdetes) {
    jsonResponse(false, 'A hirdetés nem található!', [], 404);
}

// Üzenet mentése
$stmt = $pdo->prepare("
    INSERT INTO uzenetek (hirdetes_id, kuldo_id, cimzett_email, kuldo_nev, kuldo_email, kuldo_telefon, targy, uzenet)
    VALUES (:hid, :kid, :cemail, :knev, :kemail, :ktel, :targy, :uzenet)
");
$stmt->execute([
    ':hid'    => $hirdetesId,
    ':kid'    => isLoggedIn() ? getCurrentUserId() : null,
    ':cemail' => $hirdetes['email'],
    ':knev'   => $kuldoNev,
    ':kemail' => $kuldoEmail,
    ':ktel'   => $kuldoTelefon,
    ':targy'  => $targy,
    ':uzenet' => $uzenet
]);

// Opcionális: email értesítés küldése a hirdetőnek
// mail($hirdetes['email'], "Új üzenet: {$targy}", $uzenet, "From: {$kuldoEmail}");

jsonResponse(true, 'Üzenet sikeresen elküldve!');