<?php
/**
 * admin/hirdetesek_kezelese.php - Hirdetések jóváhagyása, elutasítása, törlése, kiemelése
 */
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/email.php';
requireAdmin();

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$format = $_GET['format'] ?? 'json';

// ========================
// LISTÁZÁS
// ========================
if ($action === 'list') {
    $statusz = $_GET['statusz'] ?? 'fuggoben';
    $page = max(1, (int)($_GET['oldal'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    
    $validStatuses = ['fuggoben', 'aktiv', 'inaktiv', 'torolt'];
    if (!in_array($statusz, $validStatuses)) $statusz = 'fuggoben';
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM hirdetesek WHERE statusz = :statusz");
    $countStmt->execute([':statusz' => $statusz]);
    $total = (int)$countStmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT h.*, f.nev as felhasznalo_nev, f.email as felhasznalo_email,
               (SELECT fajl_nev FROM hirdetes_kepek WHERE hirdetes_id = h.id ORDER BY sorrend LIMIT 1) as elso_kep
        FROM hirdetesek h
        LEFT JOIN felhasznalok f ON h.felhasznalo_id = f.id
        WHERE h.statusz = :statusz
        ORDER BY h.letrehozva DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':statusz', $statusz);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $hirdetesek = $stmt->fetchAll();
    
    jsonResponse(true, "{$statusz} hirdetések", [
        'hirdetesek' => $hirdetesek,
        'total' => $total,
        'page' => $page,
        'total_pages' => ceil($total / $perPage)
    ]);
}

// ========================
// JÓVÁHAGYÁS
// ========================
if ($action === 'approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) jsonResponse(false, 'Érvénytelen azonosító!', [], 400);
    
    $stmt = $pdo->prepare("SELECT * FROM hirdetesek WHERE id = :id AND statusz = 'fuggoben'");
    $stmt->execute([':id' => $id]);
    $hirdetes = $stmt->fetch();
    
    if (!$hirdetes) {
        jsonResponse(false, 'A hirdetés nem található vagy már jóváhagyásra került!', [], 404);
    }
    
    $lejarat = calculateExpiryDate($hirdetes['ervenyesseg']);
    
    $stmt = $pdo->prepare("UPDATE hirdetesek SET statusz = 'aktiv', lejarat = :lejarat, admin_megjegyzes = NULL WHERE id = :id");
    $stmt->execute([':lejarat' => $lejarat, ':id' => $id]);
    
    // Email értesítés
    sendApprovalEmail($hirdetes['email'], $hirdetes['elado_nev'], $hirdetes['cim'], generateHirdetesUrl($id, $hirdetes['cim']));
    
    jsonResponse(true, 'Hirdetés jóváhagyva!');
}

// ========================
// ELUTASÍTÁS / TÖRLÉS
// ========================
if ($action === 'reject' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $ok = cleanInput($_POST['ok'] ?? '');
    
    if ($id <= 0) jsonResponse(false, 'Érvénytelen azonosító!', [], 400);
    if (empty($ok)) jsonResponse(false, 'Az elutasítás oka kötelező!', [], 422);
    
    $stmt = $pdo->prepare("UPDATE hirdetesek SET statusz = 'torolt', admin_megjegyzes = :ok WHERE id = :id");
    $stmt->execute([':ok' => $ok, ':id' => $id]);
    
    jsonResponse(true, 'Hirdetés elutasítva/törölve!');
}

// ========================
// KIEMELÉS VÁLTOZTATÁSA
// ========================
if ($action === 'toggle_kiemeles' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $ujKiemeles = $_POST['kiemeles'] ?? '';
    
    if (!in_array($ujKiemeles, ['alap', 'normal', 'premium'])) {
        jsonResponse(false, 'Érvénytelen kiemelési szint!', [], 400);
    }
    
    $stmt = $pdo->prepare("UPDATE hirdetesek SET kiemeles = :kiemeles WHERE id = :id");
    $stmt->execute([':kiemeles' => $ujKiemeles, ':id' => $id]);
    
    jsonResponse(true, "Kiemelés módosítva: {$ujKiemeles}");
}

// ========================
// STATISZTIKÁK (admin)
// ========================
if ($action === 'stats') {
    $statok = [
        'osszes_hirdetes' => $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz != 'torolt'")->fetchColumn(),
        'fuggoben' => $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'fuggoben'")->fetchColumn(),
        'aktiv' => $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'aktiv'")->fetchColumn(),
        'inaktiv' => $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'inaktiv'")->fetchColumn(),
        'torolt' => $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'torolt'")->fetchColumn(),
        'mai_uj' => $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE DATE(letrehozva) = CURDATE()")->fetchColumn(),
        'heti_uj' => $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE letrehozva >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
    ];
    
    jsonResponse(true, 'Statisztikák', $statok);
}
