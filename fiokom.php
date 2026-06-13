<?php
require_once __DIR__ . '/functions.php';
requireLogin();

$pdo = getDB();
$userId = getCurrentUserId();

// Felhasználó adatai
$stmt = $pdo->prepare("SELECT * FROM felhasznalok WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

// Felhasználó hirdetései
$stmt = $pdo->prepare("SELECT * FROM hirdetesek WHERE felhasznalo_id = :id AND statusz != 'torolt' ORDER BY letrehozva DESC");
$stmt->execute([':id' => $userId]);
$sajatHirdetesek = $stmt->fetchAll();

// Kedvencek
$stmt = $pdo->prepare("
    SELECT h.*, k.mentve,
           (SELECT fajl_nev FROM hirdetes_kepek WHERE hirdetes_id = h.id ORDER BY sorrend LIMIT 1) as elso_kep
    FROM kedvencek k
    JOIN hirdetesek h ON k.hirdetes_id = h.id
    WHERE k.felhasznalo_id = :id AND h.statusz = 'aktiv'
    ORDER BY k.mentve DESC
");
$stmt->execute([':id' => $userId]);
$kedvencek = $stmt->fetchAll();

// Üzenetek
$stmt = $pdo->prepare("
    SELECT u.*, h.cim as hirdetes_cim, h.id as hirdetes_id
// Üzenetek
    FROM uzenetek u
    JOIN hirdetesek h ON u.hirdetes_id = h.id
    WHERE u.cimzett_email = :email
    ORDER BY u.letrehozva DESC
    LIMIT 20
");
$stmt->execute([':email' => $user['email']]);
$uzenetek = $stmt->fetchAll();

$activeTab = $_GET['tab'] ?? 'hirdetesek';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiókom - Ország Közepe</title>
    <style>
        :root { --primary: #2c6e49; --accent: #f9a03f; --bg: #f7f9f8; --white: #fff; --border: #d1d9d6; --radius: 8px; --shadow: 0 2px 8px rgba(0,0,0,0.06); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: #1a1a1a; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        header { background: var(--white); border-bottom: 1px solid var(--border); padding: 14px 0; margin-bottom: 24px; box-shadow: var(--shadow); }
        header .container { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.3rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .logo span { color: var(--accent); }
        .btn { padding: 8px 18px; border-radius: var(--radius); font-weight: 600; border: none; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.85rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { border: 2px solid var(--primary); color: var(--primary); background: transparent; }
        .btn-danger { background: #c0392b; color: white; }
        .card { background: var(--white); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow); margin-bottom: 20px; }
        .tabs { display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 2px solid var(--border); }
        .tab { padding: 12px 24px; cursor: pointer; font-weight: 600; color: var(--primary); border-bottom: 3px solid transparent; margin-bottom: -2px; transition: 0.2s; }
        .tab:hover { background: rgba(44,110,73,0.05); }
        .tab.active { border-bottom-color: var(--accent); color: var(--accent); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { background: #f7f9f8; font-weight: 600; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-aktiv { background: #d4edda; color: #155724; }
        .badge-fuggoben { background: #fff3cd; color: #856404; }
        .badge-inaktiv { background: #f8d7da; color: #721c24; }
        .unread { font-weight: 700; background: #f0f8f4; }
        @media (max-width: 600px) { .tabs { flex-wrap: wrap; } .tab { padding: 10px 14px; font-size: 0.85rem; } }
    </style>
</head>
<body>

<header>
    <div class="container">
        <a href="/" class="logo">Ország<span>Közepe</span>.hu</a>
        <div>
            <a href="/hirdetes-feladas" class="btn btn-primary">+ Új hirdetés</a>
            <a href="/auth/logout.php" class="btn btn-outline">Kijelentkezés</a>
        </div>
    </div>
</header>

<div class="container">
    <div class="card">
        <h2>Üdv, <?= htmlspecialchars($user['nev']) ?>!</h2>
        <p style="color:#666;"><?= htmlspecialchars($user['email']) ?> | Regisztrálva: <?= date('Y.m.d', strtotime($user['regisztracio_datum'])) ?></p>
    </div>

    <div class="tabs">
        <div class="tab <?= $activeTab === 'hirdetesek' ? 'active' : '' ?>" onclick="switchTab('hirdetesek')">Hirdetéseim</div>
        <div class="tab <?= $activeTab === 'kedvencek' ? 'active' : '' ?>" onclick="switchTab('kedvencek')">Kedvencek</div>
        <div class="tab <?= $activeTab === 'uzenetek' ? 'active' : '' ?>" onclick="switchTab('uzenetek')">Üzenetek</div>
        <div class="tab <?= $activeTab === 'profil' ? 'active' : '' ?>" onclick="switchTab('profil')">Profil</div>
    </div>

    <!-- Hirdetéseim -->
    <div class="tab-content <?= $activeTab === 'hirdetesek' ? 'active' : '' ?>" id="tab-hirdetesek">
        <div class="card">
            <?php if (empty($sajatHirdetesek)): ?>
                <p style="text-align:center;color:#666;padding:20px;">Még nincsenek hirdetéseid. <a href="/hirdetes-feladas">Adj fel egyet most!</a></p>
            <?php else: ?>
            <table>
                <thead><tr><th>Cím</th><th>Kategória</th><th>Ár</th><th>Státusz</th><th>Dátum</th></tr></thead>
                <tbody>
                    <?php foreach ($sajatHirdetesek as $sh): ?>
                    <tr>
                        <td><a href="/hirdetes/<?= $sh['id'] ?>-<?= generateSlug($sh['cim']) ?>" style="color:var(--primary);"><?= htmlspecialchars($sh['cim']) ?></a></td>
                        <td><?= $sh['fokategoria'] ?></td>
                        <td><?= match($sh['ar_tipus']) { 'ingyen' => 'Ingyen', 'megbeszeles' => 'Megb. szerint', default => number_format($sh['ar'], 0, ',', ' ') . ' Ft' } ?></td>
                        <td><span class="badge badge-<?= $sh['statusz'] ?>"><?= $sh['statusz'] ?></span></td>
                        <td><?= date('m.d. H:i', strtotime($sh['letrehozva'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kedvencek -->
    <div class="tab-content <?= $activeTab === 'kedvencek' ? 'active' : '' ?>" id="tab-kedvencek">
        <div class="card">
            <?php if (empty($kedvencek)): ?>
                <p style="text-align:center;color:#666;padding:20px;">Még nincsenek kedvenc hirdetéseid.</p>
            <?php else: ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:12px;">
                <?php foreach ($kedvencek as $k): ?>
                <div style="border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;">
                    <div style="height:120px;background-size:cover;background-position:center;background-image:url('<?= $k['elso_kep'] ? UPLOAD_URL . $k['elso_kep'] : '/images/placeholder.jpg' ?>');"></div>
                    <div style="padding:10px;">
                        <a href="/hirdetes/<?= $k['id'] ?>-<?= generateSlug($k['cim']) ?>" style="font-weight:600;font-size:0.85rem;color:var(--primary);"><?= htmlspecialchars($k['cim']) ?></a>
                        <div style="font-size:0.8rem;color:#666;">Mentve: <?= date('m.d.', strtotime($k['mentve'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Üzenetek -->
    <div class="tab-content <?= $activeTab === 'uzenetek' ? 'active' : '' ?>" id="tab-uzenetek">
        <div class="card">
            <?php if (empty($uzenetek)): ?>
                <p style="text-align:center;color:#666;padding:20px;">Nincsenek üzeneteid.</p>
            <?php else: ?>
            <table>
                <thead><tr><th>Hirdetés</th><th>Küldő</th><th>Tárgy</th><th>Dátum</th></tr></thead>
                <tbody>
                    <?php foreach ($uzenetek as $u): ?>
                    <tr class="<?= !$u['olvasva'] ? 'unread' : '' ?>">
                        <td><a href="/hirdetes/<?= $u['hirdetes_id'] ?>"><?= htmlspecialchars($u['hirdetes_cim']) ?></a></td>
                        <td><?= htmlspecialchars($u['kuldo_nev']) ?><br><small><?= htmlspecialchars($u['kuldo_email']) ?></small></td>
                        <td><?= htmlspecialchars($u['targy']) ?><br><small style="color:#666;"><?= htmlspecialchars(mb_substr($u['uzenet'], 0, 60)) ?>...</small></td>
                        <td><?= date('m.d. H:i', strtotime($u['letrehozva'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profil -->
    <div class="tab-content <?= $activeTab === 'profil' ? 'active' : '' ?>" id="tab-profil">
        <div class="card">
            <h3>Profil szerkesztése</h3>
            <form id="profilForm" style="max-width:500px;">
                <div style="margin-bottom:12px;"><label style="display:block;font-weight:600;margin-bottom:4px;">Név</label><input type="text" name="nev" value="<?= htmlspecialchars($user['nev']) ?>" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:var(--radius);"></div>
                <div style="margin-bottom:12px;"><label style="display:block;font-weight:600;margin-bottom:4px;">Telefon</label><input type="tel" name="telefon" value="<?= htmlspecialchars($user['telefon'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:var(--radius);"></div>
                <div style="margin-bottom:12px;"><label style="display:block;font-weight:600;margin-bottom:4px;">Megye</label><input type="text" name="megye" value="<?= htmlspecialchars($user['megye'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:var(--radius);"></div>
                <div style="margin-bottom:12px;"><label style="display:block;font-weight:600;margin-bottom:4px;">Város</label><input type="text" name="varos" value="<?= htmlspecialchars($user['varos'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:var(--radius);"></div>
                <button type="submit" class="btn btn-primary">Mentés</button>
                <span id="profilStatus" style="margin-left:12px;font-size:0.9rem;"></span>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
    document.querySelector(`.tab[onclick="switchTab('${tabName}')"]`).classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

document.getElementById('profilForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const status = document.getElementById('profilStatus');
    try {
        const res = await fetch('/auth/profil.php', { method: 'POST', body: formData });
        const data = await res.json();
        status.textContent = data.success ? '✅ Mentve!' : '❌ ' + data.message;
        status.style.color = data.success ? '#27ae60' : '#c0392b';
    } catch(err) { status.textContent = '❌ Hiba!'; status.style.color = '#c0392b'; }
});
</script>
</body>
</html>