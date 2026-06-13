<?php
$pageTitle = 'Keresési találatok - Ország Közepe';
require_once __DIR__ . '/functions.php';
$pdo = getDB();

$q = cleanInput($_GET['q'] ?? '');
$fokategoria = cleanInput($_GET['fokategoria'] ?? '');
$megye = cleanInput($_GET['megye'] ?? '');
$arMin = $_GET['ar_min'] ?? '';
$arMax = $_GET['ar_max'] ?? '';
$page = max(1, (int)($_GET['oldal'] ?? 1));
$perPage = PER_PAGE;
$offset = ($page - 1) * $perPage;

$sql = "SELECT h.*, (SELECT fajl_nev FROM hirdetes_kepek WHERE hirdetes_id = h.id ORDER BY sorrend LIMIT 1) as elso_kep FROM hirdetesek h WHERE h.statusz = 'aktiv' AND h.lejarat > NOW()";
$countSql = "SELECT COUNT(*) FROM hirdetesek h WHERE h.statusz = 'aktiv' AND h.lejarat > NOW()";
$params = [];

if (!empty($q)) { $sql .= " AND MATCH(h.cim, h.leiras) AGAINST(:q IN BOOLEAN MODE)"; $countSql .= " AND MATCH(h.cim, h.leiras) AGAINST(:q IN BOOLEAN MODE)"; $params[':q'] = $q; }
if (!empty($fokategoria) && isValidFokategoria($fokategoria)) { $sql .= " AND h.fokategoria = :fok"; $countSql .= " AND h.fokategoria = :fok"; $params[':fok'] = $fokategoria; }
if (!empty($megye)) { $sql .= " AND h.megye = :megye"; $countSql .= " AND h.megye = :megye"; $params[':megye'] = $megye; }
if ($arMin !== '') { $sql .= " AND (h.ar >= :arMin OR h.ar_tipus = 'ingyen')"; $countSql .= " AND (h.ar >= :arMin OR h.ar_tipus = 'ingyen')"; $params[':arMin'] = (int)$arMin; }
if ($arMax !== '') { $sql .= " AND (h.ar <= :arMax OR h.ar IS NULL)"; $countSql .= " AND (h.ar <= :arMax OR h.ar IS NULL)"; $params[':arMax'] = (int)$arMax; }
$sql .= " ORDER BY h.kiemeles='premium' DESC, h.kiemeles='normal' DESC, h.letrehozva DESC LIMIT :limit OFFSET :offset";

$countStmt = $pdo->prepare($countSql); $countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll();

$pageTitle = !empty($q) ? "{$q} - Keresési találatok" : "Hirdetések";
if (!empty($fokategoria)) $pageTitle = getKategoriaNev($fokategoria) . " - " . $pageTitle;

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 style="margin-bottom:20px;"><?= htmlspecialchars($pageTitle) ?> <span style="font-size:0.9rem;color:var(--text-light);">(<?= $total ?> találat)</span></h1>
    
    <?php if (empty($results)): ?>
        <div class="card" style="text-align:center;padding:40px;">
            <p style="font-size:1.2rem;color:var(--text-light);">Nincs találat.</p>
            <a href="/" class="btn btn-primary" style="margin-top:16px;">Vissza a főoldalra</a>
        </div>
    <?php else: ?>
        <div class="grid grid-4">
            <?php foreach ($results as $h): ?>
            <a href="/hirdetes/<?= $h['id'] ?>-<?= generateSlug($h['cim']) ?>" class="listing-card">
                <div class="image" style="background-image:url('<?= $h['elso_kep'] ? UPLOAD_URL . $h['elso_kep'] : '/images/placeholder.jpg' ?>');"><?php if ($h['kiemeles'] !== 'alap'): ?><span class="badge">Kiemelt</span><?php endif; ?></div>
                <div class="info"><div class="title"><?= htmlspecialchars($h['cim']) ?></div><div class="price"><?= arFormatum($h) ?></div><div class="meta"><span><?= htmlspecialchars($h['varos']) ?></span><span><?= date('m.d.', strtotime($h['letrehozva'])) ?></span></div></div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?><a href="?<?= http_build_query(array_merge($_GET, ['oldal' => $page - 1])) ?>">← Előző</a><?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?><span class="active"><?= $i ?></span>
                <?php else: ?><a href="?<?= http_build_query(array_merge($_GET, ['oldal' => $i])) ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?><a href="?<?= http_build_query(array_merge($_GET, ['oldal' => $page + 1])) ?>">Következő →</a><?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>