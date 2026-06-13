<?php
require_once __DIR__ . '/functions.php';
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { redirect(BASE_URL . '/'); }

$stmt = $pdo->prepare("SELECT * FROM hirdetesek WHERE id = :id AND statusz IN ('aktiv','fuggoben')");
$stmt->execute([':id' => $id]); 
$h = $stmt->fetch();

if (!$h) { 
    http_response_code(404); 
    $pageTitle = 'Hirdetés nem található'; 
    include __DIR__ . '/includes/header.php'; 
    echo '<div class="container" style="text-align:center;padding:80px 20px;"><h1>404 - Hirdetés nem található</h1><p style="color:var(--text-light);margin:16px 0 24px;">A keresett hirdetés nem létezik vagy törölték.</p><a href="'.BASE_URL.'/" class="btn btn-primary">Vissza a főoldalra</a></div>'; 
    include __DIR__ . '/includes/footer.php'; 
    exit; 
}

$pageTitle = htmlspecialchars($h['cim']) . ' - Ország Közepe';
$pageDescription = htmlspecialchars(mb_substr(strip_tags($h['leiras']), 0, 160));
logMegtekintes($id);

// Képek lekérése
$stmt = $pdo->prepare("SELECT * FROM hirdetes_kepek WHERE hirdetes_id = :id ORDER BY sorrend");
$stmt->execute([':id' => $id]);
$kepek = $stmt->fetchAll();

// Kategória specifikus részletek
$katTable = match($h['fokategoria']) { 
    'allas'=>'hirdetes_allas',
    'ingatlan'=>'hirdetes_ingatlan',
    'jarmu'=>'hirdetes_jarmu',
    'muszaki'=>'hirdetes_muszaki',
    'haztartas'=>'hirdetes_haztartas',
    'szolgaltatas'=>'hirdetes_szolgaltatas',
    'hobbi'=>'hirdetes_hobbi',
    'ruhazat'=>'hirdetes_ruhazat',
    'allatok'=>'hirdetes_allatok',
    'egyeb'=>'hirdetes_egyeb', 
    default=>null 
};
$katData = null;
if ($katTable) { 
    $stmt = $pdo->prepare("SELECT * FROM {$katTable} WHERE hirdetes_id = :id"); 
    $stmt->execute([':id' => $id]); 
    $katData = $stmt->fetch(); 
}

// Hirdető további hirdetései
$stmt = $pdo->prepare("SELECT id, cim, ar, ar_tipus, varos, letrehozva FROM hirdetesek WHERE email = :email AND id != :id AND statusz = 'aktiv' ORDER BY letrehozva DESC LIMIT 4");
$stmt->execute([':email' => $h['email'], ':id' => $id]);
$tobbi = $stmt->fetchAll();

$isKedvenc = false;
if (isLoggedIn()) { 
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM kedvencek WHERE felhasznalo_id = :uid AND hirdetes_id = :hid"); 
    $stmt->execute([':uid' => getCurrentUserId(), ':hid' => $id]); 
    $isKedvenc = $stmt->fetchColumn() > 0; 
}

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="ad-layout" style="padding: 24px 0 60px;">
        <!-- Fő tartalom -->
        <div>
            <!-- Galéria -->
            <div class="card ad-gallery" style="margin-bottom:24px; padding: 20px;">
                <?php if (!empty($kepek)): ?>
                    <div class="main-image">
                        <img src="<?= UPLOAD_URL . $kepek[0]['fajl_nev'] ?>" id="mainImg" alt="<?= htmlspecialchars($h['cim']) ?>">
                    </div>
                    <?php if (count($kepek) > 1): ?>
                        <div class="thumbnails">
                            <?php foreach ($kepek as $i => $k): ?>
                                <img src="<?= UPLOAD_URL . $k['fajl_nev'] ?>" class="<?= $i === 0 ? 'active' : '' ?>" onclick="document.getElementById('mainImg').src=this.src; document.querySelectorAll('.thumbnails img').forEach(e=>e.classList.remove('active')); this.classList.add('active');" alt="Galéria kép">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="main-image" style="color:var(--text-light); font-size:1.1rem; font-weight:600; display:flex; flex-direction:column; gap:8px;">
                        <span>📷 Nincs kép feltöltve</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Leírás és Részletek -->
            <div class="card ad-content">
                <h1><?= htmlspecialchars($h['cim']) ?></h1>
                <div class="price-large"><?= arFormatum($h) ?></div>
                
                <?php if ($katData): ?>
                    <h3 style="margin-bottom:14px; font-family:'Outfit',sans-serif; color:var(--primary);">Hirdetés részletei</h3>
                    <div class="specs-grid" style="margin-bottom:32px;">
                        <?php foreach ($katData as $key => $value): 
                            if (in_array($key, ['id','hirdetes_id']) || empty($value)) continue; 
                            $label = str_replace('_', ' ', $key);
                        ?>
                            <div class="spec-item">
                                <div class="label"><?= htmlspecialchars(mb_convert_case($label, MB_CASE_TITLE, "UTF-8")) ?></div>
                                <div class="value"><?= htmlspecialchars($value) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <h3 style="margin-bottom:14px; font-family:'Outfit',sans-serif; color:var(--primary);">Leírás</h3>
                <div class="description"><?= nl2br(htmlspecialchars($h['leiras'])) ?></div>
                
                <?php if (!empty($h['video_url'])): ?>
                    <div style="margin-top:32px; border-top: 1px solid var(--border); padding-top:24px;">
                        <h3 style="margin-bottom:14px; font-family:'Outfit',sans-serif; color:var(--primary);">Kapcsolódó videó</h3>
                        <a href="<?= htmlspecialchars($h['video_url']) ?>" target="_blank" class="btn btn-outline">📺 Videó megtekintése (külső hivatkozás)</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Oldalsáv (Sidebar) -->
        <div class="ad-sidebar">
            <div class="card" style="padding: 24px;">
                <div style="font-family:'Outfit',sans-serif; font-weight:800; font-size:1.25rem; color: var(--primary); margin-bottom:14px;"><?= htmlspecialchars($h['elado_nev']) ?></div>
                <div style="margin-bottom:18px;"><a href="tel:<?= htmlspecialchars($h['telefon']) ?>" style="font-size:1.25rem; font-weight:800; color:var(--accent); display:flex; align-items:center; gap:6px;">📞 <?= htmlspecialchars($h['telefon']) ?></a></div>
                
                <div style="font-size:0.85rem; color:var(--text-light); margin-bottom:8px; display:flex; align-items:center; gap:6px;">📍 Helyszín: <strong><?= htmlspecialchars($h['varos']) ?>, <?= htmlspecialchars($h['megye']) ?></strong></div>
                <div style="font-size:0.85rem; color:var(--text-light); margin-bottom:8px; display:flex; align-items:center; gap:6px;">📅 Feladva: <strong><?= date('Y.m.d H:i', strtotime($h['letrehozva'])) ?></strong></div>
                <div style="font-size:0.85rem; color:var(--text-light); margin-bottom:8px; display:flex; align-items:center; gap:6px;">👁️ Megtekintették: <strong><?= $h['megtekintesek'] ?> alkalommal</strong></div>
                <div style="font-size:0.85rem; color:var(--text-light); margin-bottom:14px; display:flex; align-items:center; gap:6px;">⏳ Lejárat: <strong><?= date('Y.m.d', strtotime($h['lejarat'])) ?></strong></div>
                
                <?php if ($h['alku']): ?>
                    <div style="font-size:0.85rem; color:var(--success); font-weight:700; margin-bottom:16px; background: rgba(16,185,129,0.08); padding: 8px 12px; border-radius:8px; text-align:center;">🤝 Az ár minimálisan alkuképes</div>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <button class="btn btn-outline btn-sm" id="kedvencBtn" onclick="toggleKedvenc(<?= $id ?>)" style="width:100%; margin-bottom:10px;"><?= $isKedvenc ? '❤️ Eltávolítás a kedvencekből' : '🤍 Hozzáadás a kedvencekhez' ?></button>
                <?php endif; ?>
                
                <button class="btn btn-accent" onclick="document.getElementById('messageForm').style.display='block'; this.style.display='none';" style="width:100%;">✉️ Kapcsolatfelvétel az eladóval</button>
                
                <!-- Üzenetküldő Űrlap -->
                <div id="messageForm" style="display:none; margin-top:20px; border-top: 1px solid var(--border); padding-top:16px;">
                    <div class="form-group">
                        <label for="msgNev">Neved</label>
                        <input type="text" id="msgNev" value="<?= htmlspecialchars($_SESSION['user_nev'] ?? '') ?>" placeholder="Neved">
                    </div>
                    <div class="form-group">
                        <label for="msgEmail">E-mail címed</label>
                        <input type="email" id="msgEmail" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" placeholder="anna@email.hu">
                    </div>
                    <div class="form-group">
                        <label for="msgTelefon">Telefonszámod (opcionális)</label>
                        <input type="text" id="msgTelefon" placeholder="+36 30 123 4567">
                    </div>
                    <div class="form-group">
                        <label for="msgTargy">Tárgy</label>
                        <input type="text" id="msgTargy" value="Érdeklődés: <?= htmlspecialchars($h['cim']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="msgUzenet">Üzenet</label>
                        <textarea id="msgUzenet" placeholder="Írd le a kérdésedet..." style="min-height:90px;"></textarea>
                    </div>
                    <button class="btn btn-primary" onclick="sendMessage(<?= $id ?>)" style="width:100%;">Üzenet küldése</button>
                    <div id="msgStatus" style="margin-top:10px; font-size:0.85rem; font-weight:600; text-align:center;"></div>
                </div>
            </div>
            
            <?php if (!empty($tobbi)): ?>
                <div class="card" style="padding: 24px;">
                    <h4 style="font-family:'Outfit',sans-serif; font-weight:700; color:var(--primary); margin-bottom:14px; border-bottom: 1px solid var(--border); padding-bottom:8px;">Hirdető további ajánlatai</h4>
                    <?php foreach ($tobbi as $t): ?>
                        <a href="<?= BASE_URL ?>/hirdetes/<?= $t['id'] ?>-<?= generateSlug($t['cim']) ?>" style="display:block; padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:0.85rem; font-weight:500; color:var(--text); transition:color 0.2s;">
                            <div style="font-weight:600; color:var(--primary);"><?= htmlspecialchars($t['cim']) ?></div>
                            <div style="color:var(--accent); font-weight:700; margin-top:2px;"><?= arFormatum($t) ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
