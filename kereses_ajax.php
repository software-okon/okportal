<?php
require_once __DIR__ . '/functions.php';
$pdo = getDB();
$q = cleanInput($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'search';

if (empty($q) || mb_strlen($q) < 2) jsonResponse(false, 'Minimum 2 karakter!', [], 400);

if ($type === 'suggest') {
    $stmt = $pdo->prepare("SELECT DISTINCT cim, fokategoria, id FROM hirdetesek WHERE statusz = 'aktiv' AND lejarat > NOW() AND (cim LIKE :q OR leiras LIKE :q2) ORDER BY megtekintesek DESC LIMIT 8");
    $stmt->execute([':q' => $q . '%', ':q2' => '%' . $q . '%']);
    $suggestions = [];
    foreach ($stmt->fetchAll() as $r) $suggestions[] = ['cim' => $r['cim'], 'fokategoria' => $r['fokategoria'], 'url' => generateHirdetesUrl($r['id'], $r['cim'])];
    jsonResponse(true, 'Javaslatok', ['suggestions' => $suggestions]);
}