<?php
/**
 * admin/statisztikak_frissitese.php - Napi statisztikák frissítése (CRON job)
 * Futtatandó: éjfélkor, pl. 0 0 * * * php /path/to/admin/statisztikak_frissitese.php
 */
require_once __DIR__ . '/../functions.php';

$pdo = getDB();
$datum = date('Y-m-d');

$ujHirdetesek = $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE DATE(letrehozva) = CURDATE()")->fetchColumn();
$aktivHirdetesek = $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'aktiv' AND lejarat > NOW()")->fetchColumn();
$ujFelhasznalok = $pdo->query("SELECT COUNT(*) FROM felhasznalok WHERE DATE(regisztracio_datum) = CURDATE()")->fetchColumn();
$osszesMegtekintes = $pdo->query("SELECT COUNT(*) FROM megtekintes_naplo WHERE DATE(datum) = CURDATE()")->fetchColumn();
$osszesUzenet = $pdo->query("SELECT COUNT(*) FROM uzenetek WHERE DATE(letrehozva) = CURDATE()")->fetchColumn();

$sql = "INSERT INTO statisztikak (datum, uj_hirdetesek, aktiv_hirdetesek, uj_felhasznalok, osszes_megtekintes, osszes_uzenet)
        VALUES (:datum, :uj_hirdetesek, :aktiv_hirdetesek, :uj_felhasznalok, :osszes_megtekintes, :osszes_uzenet)
        ON DUPLICATE KEY UPDATE
            uj_hirdetesek = VALUES(uj_hirdetesek),
            aktiv_hirdetesek = VALUES(aktiv_hirdetesek),
            uj_felhasznalok = VALUES(uj_felhasznalok),
            osszes_megtekintes = VALUES(osszes_megtekintes),
            osszes_uzenet = VALUES(osszes_uzenet)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':datum'              => $datum,
    ':uj_hirdetesek'      => $ujHirdetesek,
    ':aktiv_hirdetesek'   => $aktivHirdetesek,
    ':uj_felhasznalok'    => $ujFelhasznalok,
    ':osszes_megtekintes' => $osszesMegtekintes,
    ':osszes_uzenet'      => $osszesUzenet
]);

// Lejárt hirdetések inaktiválása
$pdo->query("UPDATE hirdetesek SET statusz = 'inaktiv' WHERE statusz = 'aktiv' AND lejarat <= NOW()");

echo "Statisztikák frissítve: {$datum}\n";
echo "Új hirdetések: {$ujHirdetesek}\n";
echo "Aktív hirdetések: {$aktivHirdetesek}\n";
echo "Új felhasználók: {$ujFelhasznalok}\n";
echo "Megtekintések: {$osszesMegtekintes}\n";
echo "Üzenetek: {$osszesUzenet}\n";
