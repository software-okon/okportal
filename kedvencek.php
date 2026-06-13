<?php
/**
 * kedvencek.php - Kedvencek hozzáadása/törlése/lekérése
 */
require_once __DIR__ . '/functions.php';
requireLogin();

$pdo = getDB();
$userId = getCurrentUserId();
$method = $_SERVER['REQUEST_METHOD'];

// GET: kedvencek listázása
if ($method === 'GET') {
    $page = max(1, (int)($_GET['oldal'] ?? 1));
    $perPage = PER_PAGE;
    $offset = ($page - 1) * $perPage;
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM kedvencek WHERE felhasznalo_id = :uid");
    $countStmt->execute([':uid' => $userId]);
    $total = (int)$countStmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT h.*, k.mentve,
               (SELECT fajl_nev FROM hirdetes_kepek WHERE hirdetes_id = h.id ORDER BY sorrend LIMIT 1) as elso_kep
        FROM kedvencek k
        JOIN hirdetesek h ON k.hirdetes_id = h.id
        WHERE k.felhasznalo_id = :uid AND h.statusz = 'aktiv'
        ORDER BY k.mentve DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $kedvencek = $stmt->fetchAll();
    
    jsonResponse(true, 'Kedvencek listája', [
        'kedvencek' => $kedvencek,
        'total'     => $total
    ]);
}

// POST: kedvenc hozzáadása vagy törlése
if ($method === 'POST') {
    $hirdetesId = (int)($_POST['hirdetes_id'] ?? 0);
    $action = $_POST['action'] ?? 'add'; // 'add' vagy 'remove'
    
    if ($hirdetesId <= 0) {
        jsonResponse(false, 'Érvénytelen hirdetés azonosító!', [], 400);
    }
    
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO kedvencek (felhasznalo_id, hirdetes_id) VALUES (:uid, :hid)");
        $stmt->execute([':uid' => $userId, ':hid' => $hirdetesId]);
        jsonResponse(true, 'Hirdetés hozzáadva a kedvencekhez!');
    } else {
        $stmt = $pdo->prepare("DELETE FROM kedvencek WHERE felhasznalo_id = :uid AND hirdetes_id = :hid");
        $stmt->execute([':uid' => $userId, ':hid' => $hirdetesId]);
        jsonResponse(true, 'Hirdetés eltávolítva a kedvencekből!');
    }
}
