<?php
/**
 * auth/profil.php - Felhasználói profil adatainak lekérése és módosítása
 */
require_once __DIR__ . '/../functions.php';

// Bejelentkezés ellenőrzése
requireLogin();

$pdo = getDB();
$userId = getCurrentUserId();

// GET: profil adatok lekérése
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT id, email, nev, telefon, megye, varos, iranyitoszam, profil_kep, email_ellenorizve, regisztracio_datum, utolso_belepes FROM felhasznalok WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(false, 'Felhasználó nem található!', [], 404);
    }
    
    // Hirdetések száma
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM hirdetesek WHERE felhasznalo_id = :id AND statusz != 'torolt'");
    $stmt->execute([':id' => $userId]);
    $user['hirdetesek_szama'] = (int)$stmt->fetchColumn();
    
    // Kedvencek száma
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM kedvencek WHERE felhasznalo_id = :id");
    $stmt->execute([':id' => $userId]);
    $user['kedvencek_szama'] = (int)$stmt->fetchColumn();
    
    jsonResponse(true, 'Profil adatok', ['user' => $user]);
}

// PUT/POST: profil módosítása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nev      = cleanInput($_POST['nev'] ?? '');
    $telefon  = cleanInput($_POST['telefon'] ?? '');
    $megye    = cleanInput($_POST['megye'] ?? '');
    $varos    = cleanInput($_POST['varos'] ?? '');
    $irsz     = cleanInput($_POST['iranyitoszam'] ?? '');
    
    $errors = [];
    if (mb_strlen($nev) < 2) $errors[] = 'A név legalább 2 karakter!';
    if (!empty($irsz) && !preg_match('/^\d{4}$/', $irsz)) $errors[] = 'Érvénytelen irányítószám!';
    
    if (!empty($errors)) {
        jsonResponse(false, 'Validációs hiba!', ['errors' => $errors], 422);
    }
    
    // Profilkép feltöltése
    $profilKep = null;
    if (!empty($_FILES['profil_kep']) && $_FILES['profil_kep']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['profil_kep']);
        if ($uploadResult) {
            $profilKep = $uploadResult['fajl_nev'];
        }
    }
    
    $sql = "UPDATE felhasznalok SET nev = :nev, telefon = :telefon, megye = :megye, varos = :varos, iranyitoszam = :irsz";
    $params = [
        ':nev'     => $nev,
        ':telefon' => $telefon,
        ':megye'   => $megye,
        ':varos'   => $varos,
        ':irsz'    => $irsz,
        ':id'      => $userId
    ];
    
    if ($profilKep) {
        $sql .= ", profil_kep = :profil_kep";
        $params[':profil_kep'] = $profilKep;
    }
    
    $sql .= " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Munkamenet frissítése
    $_SESSION['user_nev'] = $nev;
    
    jsonResponse(true, 'Profil sikeresen frissítve!');
}