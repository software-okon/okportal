<?php
require_once __DIR__ . '/functions.php';
$fok = cleanInput($_GET['fokategoria'] ?? '');
if (!isValidFokategoria($fok)) redirect('/');
$pageTitle = getKategoriaNev($fok) . ' hirdetések - Ország Közepe';
$page = max(1, (int)($_GET['oldal'] ?? 1));
$perPage = PER_PAGE;
$offset = ($page - 1) * $perPage;
$pdo = getDB();

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM hirdetesek WHERE fokategoria = :fok AND statusz = 'aktiv' AND lejarat > NOW()");
$countStmt->execute([':fok' => $fok]); $total = (int)$countStmt->fetchColumn(); $totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT *, (SELECT fajl_nev FROM hirdetes_kepek WHERE hirdetes_id = hirdetesek.id ORDER BY sorrend LIMIT 1) as elso_kep FROM hirdetesek WHERE fokategoria = :fok AND statusz = 'aktiv' AND lejarat > NOW() ORDER BY letrehozva DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':fok', $fok); $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT); $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute(); $results = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 style="margin-bottom:20px;"><?= getKategoriaIcon($fok) ?> <?= getKategoriaNev($fok) ?> <span style="font-size:0.9rem;color:var(--text-light);">(<?= $total ?> hirdetés)</span></h1>
    
    <?php if (empty($results)): ?>
        <div class="card" style="text-align:center;padding:40px;"><p style="font-size:1.2rem;color:var(--text-light);">Jelenleg nincs hirdetés ebben a kategóriában.</p></div>
    <?php else: ?>
        <div class="grid grid-4">
            <?php foreach ($results as $h): ?>
            <a href="/hirdetes/<?= $h['id'] ?>-<?= generateSlug($h['cim']) ?>" class="listing-card">
                <div class="image" style="background-image:url('<?= $h['elso_kep'] ? UPLOAD_URL . $h['elso_kep'] : '/images/placeholder.jpg' ?>');"></div>
                <div class="info"><div class="title"><?= htmlspecialchars($h['cim']) ?></div><div class="price"><?= arFormatum($h) ?></div><div class="meta"><span><?= htmlspecialchars($h['varos']) ?></span><span><?= date('m.d.', strtotime($h['letrehozva'])) ?></span></div></div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?><span class="active"><?= $i ?></span>
                <?php else: ?><a href="?oldal=<?= $i ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>