<?php
/**
 * admin/index.php - Admin dashboard
 */
require_once __DIR__ . '/../functions.php';
requireAdmin();

$pdo = getDB();

// Statisztikák
$statok = [];

// Összes felhasználó
$statok['osszes_felhasznalo'] = $pdo->query("SELECT COUNT(*) FROM felhasznalok WHERE aktiv = 1")->fetchColumn();

// Összes hirdetés
$statok['osszes_hirdetes'] = $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz != 'torolt'")->fetchColumn();

// Függőben lévő hirdetések
$statok['fuggoben'] = $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'fuggoben'")->fetchColumn();

// Aktív hirdetések
$statok['aktiv'] = $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'aktiv' AND lejarat > NOW()")->fetchColumn();

// Lejárt hirdetések
$statok['lejart'] = $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'aktiv' AND lejarat <= NOW()")->fetchColumn();

// Összes üzenet
$statok['osszes_uzenet'] = $pdo->query("SELECT COUNT(*) FROM uzenetek")->fetchColumn();

// Összes megtekintés
$statok['osszes_megtekintes'] = $pdo->query("SELECT SUM(megtekintesek) FROM hirdetesek")->fetchColumn();

// Napi statisztikák (utolsó 30 nap)
$stmt = $pdo->query("
    SELECT datum, uj_hirdetesek, aktiv_hirdetesek, uj_felhasznalok, osszes_megtekintes, osszes_uzenet
    FROM statisztikak
    WHERE datum >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ORDER BY datum DESC
");
$napiStatok = $stmt->fetchAll();

// Legutóbbi hirdetések
$stmt = $pdo->query("
    SELECT h.*, f.nev as felhasznalo_nev,
           (SELECT fajl_nev FROM hirdetes_kepek WHERE hirdetes_id = h.id ORDER BY sorrend LIMIT 1) as elso_kep
    FROM hirdetesek h
    LEFT JOIN felhasznalok f ON h.felhasznalo_id = f.id
    WHERE h.statusz != 'torolt'
    ORDER BY h.letrehozva DESC
    LIMIT 20
");
$utolsoHirdetesek = $stmt->fetchAll();

// JSON válasz (API jelleggel)
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    jsonResponse(true, 'Admin statisztikák', [
        'statok' => $statok,
        'napi_statok' => $napiStatok,
        'utolso_hirdetesek' => $utolsoHirdetesek
    ]);
}

// HTML válasz
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ország Közepe</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #2c6e49; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { margin: 0 0 8px; color: #666; font-size: 14px; text-transform: uppercase; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #2c6e49; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #2c6e49; color: white; }
        .status-fuggoben { color: #f39c12; }
        .status-aktiv { color: #27ae60; }
        .status-inaktiv { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <p>Üdv, <?= htmlspecialchars($_SESSION['admin_nev'] ?? 'Admin') ?> | <a href="/auth/logout.php">Kijelentkezés</a></p>
        
        <div class="stats-grid">
            <div class="stat-card"><h3>Felhasználók</h3><div class="value"><?= $statok['osszes_felhasznalo'] ?></div></div>
            <div class="stat-card"><h3>Összes hirdetés</h3><div class="value"><?= $statok['osszes_hirdetes'] ?></div></div>
            <div class="stat-card"><h3>Függőben</h3><div class="value"><?= $statok['fuggoben'] ?></div></div>
            <div class="stat-card"><h3>Aktív</h3><div class="value"><?= $statok['aktiv'] ?></div></div>
            <div class="stat-card"><h3>Üzenetek</h3><div class="value"><?= $statok['osszes_uzenet'] ?></div></div>
            <div class="stat-card"><h3>Megtekintések</h3><div class="value"><?= number_format($statok['osszes_megtekintes']) ?></div></div>
        </div>
        
        <h2>Legutóbbi hirdetések</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Cím</th><th>Kategória</th><th>Felhasználó</th><th>Státusz</th><th>Dátum</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utolsoHirdetesek as $h): ?>
                <tr>
                    <td><?= $h['id'] ?></td>
                    <td><?= htmlspecialchars($h['cim']) ?></td>
                    <td><?= $h['fokategoria'] ?></td>
                    <td><?= htmlspecialchars($h['felhasznalo_nev'] ?? 'Vendég') ?></td>
                    <td class="status-<?= $h['statusz'] ?>"><?= $h['statusz'] ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($h['letrehozva'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>