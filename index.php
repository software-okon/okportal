<?php
$pageTitle = 'Ország Közepe Apróhirdetések - Ingyenes apróhirdetési portál';
$pageDescription = 'Ország Közepe - Több ezer apróhirdetés egy helyen. Állás, ingatlan, autó, műszaki cikk és még sok más.';
require_once __DIR__ . '/functions.php';
$pdo = getDB();

$kiemelt = $pdo->query("SELECT h.*, (SELECT fajl_nev FROM hirdetes_kepek WHERE hirdetes_id = h.id ORDER BY sorrend LIMIT 1) as elso_kep FROM hirdetesek h WHERE h.statusz = 'aktiv' AND h.lejarat > NOW() AND h.kiemeles IN ('premium','normal') ORDER BY h.kiemeles='premium' DESC, h.letrehozva DESC LIMIT 8")->fetchAll();
$friss = $pdo->query("SELECT h.*, (SELECT fajl_nev FROM hirdetes_kepek WHERE hirdetes_id = h.id ORDER BY sorrend LIMIT 1) as elso_kep FROM hirdetesek h WHERE h.statusz = 'aktiv' AND h.lejarat > NOW() ORDER BY h.letrehozva DESC LIMIT 24")->fetchAll();
$katStat = $pdo->query("SELECT fokategoria, COUNT(*) as db FROM hirdetesek WHERE statusz = 'aktiv' AND lejarat > NOW() GROUP BY fokategoria ORDER BY db DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Találd meg, amire szükséged van!</h1>
        <p>Több ezer apróhirdetés egy helyen - állás, ingatlan, autó, műszaki cikk és még sok más</p>
        <form class="search-box" action="<?= BASE_URL ?>/kereses" method="GET">
            <input type="text" name="q" id="mainSearch" placeholder="Mit keresel? Pl.: eladó autó, albérlet..." autocomplete="off">
            <select name="fokategoria">
                <option value="">Minden kategória</option>
                <?php foreach ($katStat as $k): ?>
                <option value="<?= $k['fokategoria'] ?>"><?= getKategoriaNev($k['fokategoria']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-accent">🔍 Keresés</button>
        </form>
        <div id="searchSuggestions" style="max-width:700px;margin:12px auto 0;text-align:left;display:none;position:relative;z-index:999;"></div>
    </div>
</section>

<section style="padding: 60px 0 30px;">
    <div class="container">
        <h2 style="text-align:center;margin-bottom:36px;font-family:'Outfit',sans-serif;font-weight:800;font-size:1.8rem;color:var(--primary);">Böngéssz kategóriák szerint</h2>
        <div class="grid grid-4">
            <?php foreach ($katStat as $k): ?>
            <a href="<?= BASE_URL ?>/kategoria/<?= $k['fokategoria'] ?>" class="cat-card">
                <span class="icon"><?= getKategoriaIcon($k['fokategoria']) ?></span>
                <div class="name"><?= getKategoriaNev($k['fokategoria']) ?></div>
                <div class="count"><?= $k['db'] ?> hirdetés</div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($kiemelt)): ?>
<section style="padding:30px 0;">
    <div class="container">
        <h2 style="margin-bottom:24px;font-family:'Outfit',sans-serif;font-weight:800;font-size:1.8rem;color:var(--primary);">⭐ Kiemelt ajánlatok</h2>
        <div class="grid grid-4">
            <?php foreach ($kiemelt as $h): ?>
            <a href="<?= BASE_URL ?>/hirdetes/<?= $h['id'] ?>-<?= generateSlug($h['cim']) ?>" class="listing-card">
                <div class="image" style="background-image:url('<?= $h['elso_kep'] ? UPLOAD_URL . $h['elso_kep'] : BASE_URL . '/images/placeholder.jpg' ?>');">
                    <span class="badge">Kiemelt</span>
                </div>
                <div class="info">
                    <div class="title"><?= htmlspecialchars($h['cim']) ?></div>
                    <div class="price"><?= arFormatum($h) ?></div>
                    <div class="meta">
                        <span>📍 <?= htmlspecialchars($h['varos']) ?></span>
                        <span>📅 <?= date('m.d.', strtotime($h['letrehozva'])) ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section style="padding:30px 0 60px;">
    <div class="container">
        <h2 style="margin-bottom:24px;font-family:'Outfit',sans-serif;font-weight:800;font-size:1.8rem;color:var(--primary);">🆕 Legfrissebb hirdetések</h2>
        <div class="grid grid-4">
            <?php foreach ($friss as $h): ?>
            <a href="<?= BASE_URL ?>/hirdetes/<?= $h['id'] ?>-<?= generateSlug($h['cim']) ?>" class="listing-card">
                <div class="image" style="background-image:url('<?= $h['elso_kep'] ? UPLOAD_URL . $h['elso_kep'] : BASE_URL . '/images/placeholder.jpg' ?>');"></div>
                <div class="info">
                    <div class="title"><?= htmlspecialchars($h['cim']) ?></div>
                    <div class="price"><?= arFormatum($h) ?></div>
                    <div class="meta">
                        <span>📍 <?= htmlspecialchars($h['varos']) ?></span>
                        <span>📅 <?= date('m.d.', strtotime($h['letrehozva'])) ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="<?= BASE_URL ?>/hirdetesek" class="btn btn-primary btn-lg">Összes hirdetés böngészése →</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
