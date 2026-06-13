<?php
require_once __DIR__ . '/functions.php';
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { redirect('/'); }

$stmt = $pdo->prepare("SELECT * FROM hirdetesek WHERE id = :id AND statusz IN ('aktiv','fuggoben')");
$stmt->execute([':id' => $id]); $h = $stmt->fetch();
if (!$h) { http_response_code(404); $pageTitle = 'Hirdetés nem található'; include __DIR__ . '/includes/header.php'; echo '<div class="container"><h1>404 - Hirdetés nem található</h1><p>A keresett hirdetés nem létezik vagy törölték.</p><a href="/" class="btn btn-primary">Vissza a főoldalra</a></div>'; include __DIR__ . '/includes/footer.php'; exit; }

$pageTitle = htmlspecialchars($h['cim']) . ' - Ország Közepe';
$pageDescription = htmlspecialchars(mb_substr(strip_tags($h['leiras']), 0, 160));
logMegtekintes($id);

$kepek = $pdo->prepare("SELECT * FROM hirdetes_kepek WHERE hirdetes_id = :id ORDER BY sorrend")->execute([':id' => $id]) ? $pdo->prepare("SELECT * FROM hirdetes_kepek WHERE hirdetes_id = :id ORDER BY sorrend")->fetchAll() : [];
$kepek = $pdo->query("SELECT * FROM hirdetes_kepek WHERE hirdetes_id = {$id} ORDER BY sorrend")->fetchAll();

$katTable = match($h['fokategoria']) { 'allas'=>'hirdetes_allas','ingatlan'=>'hirdetes_ingatlan','jarmu'=>'hirdetes_jarmu','muszaki'=>'hirdetes_muszaki','haztartas'=>'hirdetes_haztartas','szolgaltatas'=>'hirdetes_szolgaltatas','hobbi'=>'hirdetes_hobbi','ruhazat'=>'hirdetes_ruhazat','allatok'=>'hirdetes_allatok','egyeb'=>'hirdetes_egyeb', default=>null };
$katData = null;
if ($katTable) { $stmt = $pdo->prepare("SELECT * FROM {$katTable} WHERE hirdetes_id = :id"); $stmt->execute([':id' => $id]); $katData = $stmt->fetch(); }

$tobbi = $pdo->query("SELECT id, cim, ar, ar_tipus, varos, letrehozva FROM hirdetesek WHERE email = '{$h['email']}' AND id != {$id} AND statusz = 'aktiv' ORDER BY letrehozva DESC LIMIT 4")->fetchAll();

$isKedvenc = false;
if (isLoggedIn()) { $stmt = $pdo->prepare("SELECT COUNT(*) FROM kedvencek WHERE felhasznalo_id = :uid AND hirdetes_id = :hid"); $stmt->execute([':uid' => getCurrentUserId(), ':hid' => $id]); $isKedvenc = $stmt->fetchColumn() > 0; }

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="ad-layout" style="padding:24px 0;">
        <div>
            <div class="card ad-gallery" style="margin-bottom:20px;">
                <?php if (!empty($kepek)): ?>
                <div class="main-image"><img src="<?= UPLOAD_URL . $kepek[0]['fajl_nev'] ?>" id="mainImg" alt="<?= htmlspecialchars($h['cim']) ?>"></div>
                <?php if (count($kepek) > 1): ?>
                <div class="thumbnails">
                    <?php foreach ($kepek as $i => $k): ?>
                    <img src="<?= UPLOAD_URL . $k['fajl_nev'] ?>" class="<?= $i === 0 ? 'active' : '' ?>" onclick="document.getElementById('mainImg').src=this.src;document.querySelectorAll('.thumbnails img').forEach(e=>e.classList.remove('active'));this.classList.add('active');">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="main-image" style="color:#999;font-size:1.2rem;">Nincs kép</div>
                <?php endif; ?>
            </div>
            <div class="card ad-content">
                <h1><?= htmlspecialchars($h['cim']) ?></h1>
                <div class="price-large"><?= arFormatum($h) ?></div>
                <?php if ($katData): ?>
                <div class="specs-grid" style="margin-bottom:20px;">
                    <?php foreach ($katData as $key => $value): if (in_array($key, ['id','hirdetes_id']) || empty($value)) continue; ?>
                    <div class="spec-item"><div class="label"><?= htmlspecialchars(str_replace('_',' ',$key)) ?></div><div class="value"><?= htmlspecialchars($value) ?></div></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <h3 style="margin-bottom:12px;">Leírás</h3>
                <div class="description"><?= nl2br(htmlspecialchars($h['leiras'])) ?></div>
                <?php if (!empty($h['video_url'])): ?>
                <div style="margin-top:20px;"><h4>Videó</h4><a href="<?= htmlspecialchars($h['video_url']) ?>" target="_blank" class="btn btn-outline">Videó megtekintése</a></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="ad-sidebar">
            <div class="card">
                <div style="font-weight:600;font-size:1.1rem;margin-bottom:8px;"><?= htmlspecialchars($h['elado_nev']) ?></div>
                <div style="margin-bottom:12px;"><a href="tel:<?= htmlspecialchars($h['telefon']) ?>" style="font-size:1.1rem;font-weight:700;color:var(--primary);">📞 <?= htmlspecialchars($h['telefon']) ?></a></div>
                <div style="font-size:0.85rem;color:var(--text-light);margin-bottom:4px;">📍 <?= htmlspecialchars($h['varos']) ?>, <?= htmlspecialchars($h['megye']) ?></div>
                <div style="font-size:0.85rem;color:var(--text-light);margin-bottom:4px;">📅 <?= date('Y.m.d H:i', strtotime($h['letrehozva'])) ?></div>
                <div style="font-size:0.85rem;color:var(--text-light);margin-bottom:4px;">👁️ <?= $h['megtekintesek'] ?> megtekintés</div>
                <div style="font-size:0.85rem;color:var(--text-light);margin-bottom:4px;">⏳ Lejár: <?= date('Y.m.d', strtotime($h['lejarat'])) ?></div>
                <?php if ($h['alku']): ?><div style="font-size:0.85rem;color:var(--success);margin-bottom:8px;">✅ Alku elfogadva</div><?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                <button class="btn btn-outline btn-sm" id="kedvencBtn" onclick="toggleKedvenc(<?= $id ?>)" style="width:100%;margin-bottom:8px;"><?= $isKedvenc ? '❤️ Kedvencekben' : '🤍 Kedvencekhez adás' ?></button>
                <?php endif; ?>
                <button class="btn btn-accent" onclick="document.getElementById('messageForm').style.display='block';this.style.display='none';" style="width:100%;">✉️ Üzenet küldése</button>
                
                <div id="messageForm" style="display:none;margin-top:12px;">
                    <input type="text" id="msgNev" class="form-group" placeholder="Neved" value="<?= htmlspecialchars($_SESSION['user_nev'] ?? '') ?>" style="margin-bottom:8px;width:100%;padding:8px;border:1px solid var(--border);border-radius:var(--radius);">
                    <input type="email" id="msgEmail" placeholder="E-mail címed" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" style="margin-bottom:8px;width:100%;padding:8px;border:1px solid var(--border);border-radius:var(--radius);">
                    <input type="text" id="msgTelefon" placeholder="Telefonszámod (opcionális)" style="margin-bottom:8px;width:100%;padding:8px;border:1px solid var(--border);border-radius:var(--radius);">
                    <input type="text" id="msgTargy" value="Érdeklődés: <?= htmlspecialchars($h['cim']) ?>" style="margin-bottom:8px;width:100%;padding:8px;border:1px solid var(--border);border-radius:var(--radius);">
                    <textarea id="msgUzenet" placeholder="Üzenet szövege..." style="margin-bottom:8px;width:100%;padding:8px;border:1px solid var(--border);border-radius:var(--radius);min-height:80px;"></textarea>
                    <button class="btn btn-primary" onclick="sendMessage(<?= $id ?>)" style="width:100%;">Küldés</button>
                    <div id="msgStatus" style="margin-top:8px;font-size:0.85rem;"></div>
                </div>
            </div>
            <?php if (!empty($tobbi)): ?>
            <div class="card">
                <h4 style="margin-bottom:12px;">Hirdető további hirdetései</h4>
                <?php foreach ($tobbi as $t): ?>
                <a href="/hirdetes/<?= $t['id'] ?>-<?= generateSlug($t['cim']) ?>" style="display:block;padding:6px 0;border-bottom:1px solid #eee;font-size:0.85rem;"><?= htmlspecialchars($t['cim']) ?> - <?= arFormatum($t) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>