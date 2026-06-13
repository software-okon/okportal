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
    FROM uzenetek u
    JOIN hirdetesek h ON u.hirdetes_id = h.id
    WHERE u.cimzett_email = :email
    ORDER BY u.letrehozva DESC
    LIMIT 20
");
$stmt->execute([':email' => $user['email']]);
$uzenetek = $stmt->fetchAll();

$activeTab = $_GET['tab'] ?? 'hirdetesek';
$pageTitle = 'Fiókom - Ország Közepe';

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1000px; padding: 20px 0 60px;">
    <!-- Üdvözlés -->
    <div class="card" style="margin-bottom: 24px; padding: 24px;">
        <h2 style="font-family:'Outfit',sans-serif; color:var(--primary); margin-bottom: 6px;">Üdv, <?= htmlspecialchars($user['nev']) ?>!</h2>
        <p style="color:var(--text-light); font-size: 0.9rem;">
            <?= htmlspecialchars($user['email']) ?> | Fiók regisztrálva: <?= date('Y.m.d', strtotime($user['regisztracio_datum'])) ?>
        </p>
    </div>

    <!-- Fülek (Tabs) -->
    <div style="display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 2px solid var(--border); overflow-x: auto; padding-bottom: 6px;">
        <button class="btn <?= $activeTab === 'hirdetesek' ? 'btn-primary' : 'btn-outline' ?> btn-sm" onclick="switchTab('hirdetesek')" id="tab-btn-hirdetesek">Hirdetéseim (<?= count($sajatHirdetesek) ?>)</button>
        <button class="btn <?= $activeTab === 'kedvencek' ? 'btn-primary' : 'btn-outline' ?> btn-sm" onclick="switchTab('kedvencek')" id="tab-btn-kedvencek">Kedvencek (<?= count($kedvencek) ?>)</button>
        <button class="btn <?= $activeTab === 'uzenetek' ? 'btn-primary' : 'btn-outline' ?> btn-sm" onclick="switchTab('uzenetek')" id="tab-btn-uzenetek">Üzenetek (<?= count($uzenetek) ?>)</button>
        <button class="btn <?= $activeTab === 'profil' ? 'btn-primary' : 'btn-outline' ?> btn-sm" onclick="switchTab('profil')" id="tab-btn-profil">Profil adatok</button>
    </div>

    <!-- Hirdetéseim Szekció -->
    <div class="tab-content-panel" id="panel-hirdetesek" style="display: <?= $activeTab === 'hirdetesek' ? 'block' : 'none' ?>;">
        <div class="card" style="padding: 24px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color:var(--primary);">Aktív és függő hirdetéseim</h3>
            <?php if (empty($sajatHirdetesek)): ?>
                <p style="text-align:center; color:var(--text-light); padding:30px 10px;">Még nincsenek hirdetéseid. <a href="<?= BASE_URL ?>/hirdetes-feladas" style="font-weight:700;">Adj fel egyet most!</a></p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Cím</th>
                                <th>Kategória</th>
                                <th>Ár</th>
                                <th>Státusz</th>
                                <th>Dátum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sajatHirdetesek as $sh): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/hirdetes/<?= $sh['id'] ?>-<?= generateSlug($sh['cim']) ?>" style="font-weight: 600; color:var(--primary);"><?= htmlspecialchars($sh['cim']) ?></a></td>
                                <td style="text-transform: capitalize;"><?= htmlspecialchars($sh['fokategoria']) ?></td>
                                <td><?= match($sh['ar_tipus']) { 'ingyen' => 'Ingyen', 'megbeszeles' => 'Megb. szerint', default => number_format($sh['ar'], 0, ',', ' ') . ' Ft' } ?></td>
                                <td><span class="badge badge-<?= $sh['statusz'] ?>"><?= $sh['statusz'] ?></span></td>
                                <td style="font-size:0.8rem; color:var(--text-light);"><?= date('m.d. H:i', strtotime($sh['letrehozva'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kedvencek Szekció -->
    <div class="tab-content-panel" id="panel-kedvencek" style="display: <?= $activeTab === 'kedvencek' ? 'block' : 'none' ?>;">
        <div class="card" style="padding: 24px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color:var(--primary);">Elmentett kedvencek</h3>
            <?php if (empty($kedvencek)): ?>
                <p style="text-align:center; color:var(--text-light); padding:30px 10px;">Még nincsenek kedvenc hirdetéseid.</p>
            <?php else: ?>
                <div class="grid grid-4">
                    <?php foreach ($kedvencek as $k): ?>
                    <div class="listing-card">
                        <div class="image" style="background-image:url('<?= $k['elso_kep'] ? UPLOAD_URL . $k['elso_kep'] : BASE_URL . '/images/placeholder.jpg' ?>'); height: 140px;"></div>
                        <div class="info" style="padding: 12px;">
                            <a href="<?= BASE_URL ?>/hirdetes/<?= $k['id'] ?>-<?= generateSlug($k['cim']) ?>" style="font-family:'Outfit',sans-serif; font-weight:700; font-size:0.9rem; color:var(--primary);"><?= htmlspecialchars($k['cim']) ?></a>
                            <div style="font-size:0.75rem; color:var(--text-light); margin-top: 6px;">Mentve: <?= date('Y.m.d.', strtotime($k['mentve'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Üzenetek Szekció -->
    <div class="tab-content-panel" id="panel-uzenetek" style="display: <?= $activeTab === 'uzenetek' ? 'block' : 'none' ?>;">
        <div class="card" style="padding: 24px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color:var(--primary);">Beérkező üzenetek</h3>
            <?php if (empty($uzenetek)): ?>
                <p style="text-align:center; color:var(--text-light); padding:30px 10px;">Nincsenek beérkezett üzeneteid.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Hirdetés</th>
                                <th>Küldő</th>
                                <th>Üzenet / Tárgy</th>
                                <th>Dátum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uzenetek as $u): ?>
                            <tr style="background: <?= !$u['olvasva'] ? 'rgba(30,70,32,0.03)' : 'transparent' ?>;">
                                <td><a href="<?= BASE_URL ?>/hirdetes/<?= $u['hirdetes_id'] ?>" style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($u['hirdetes_cim']) ?></a></td>
                                <td>
                                    <strong><?= htmlspecialchars($u['kuldo_nev']) ?></strong><br>
                                    <small style="color:var(--text-light);"><?= htmlspecialchars($u['kuldo_email']) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($u['targy']) ?></strong><br>
                                    <span style="font-size: 0.85rem; color: #475569;"><?= nl2br(htmlspecialchars($u['uzenet'])) ?></span>
                                </td>
                                <td style="font-size:0.8rem; color:var(--text-light); white-space:nowrap;"><?= date('Y.m.d. H:i', strtotime($u['letrehozva'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profil Szekció -->
    <div class="tab-content-panel" id="panel-profil" style="display: <?= $activeTab === 'profil' ? 'block' : 'none' ?>;">
        <div class="card" style="padding: 24px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color:var(--primary);">Profil adatok szerkesztése</h3>
            <form id="profilForm" style="max-width:500px;">
                <div class="form-group">
                    <label>Teljes név *</label>
                    <input type="text" name="nev" value="<?= htmlspecialchars($user['nev']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Telefonszám</label>
                    <input type="tel" name="telefon" value="<?= htmlspecialchars($user['telefon'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Megye</label>
                    <input type="text" name="megye" value="<?= htmlspecialchars($user['megye'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Város</label>
                    <input type="text" name="varos" value="<?= htmlspecialchars($user['varos'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Irányítószám</label>
                    <input type="text" name="iranyitoszam" maxlength="4" value="<?= htmlspecialchars($user['iranyitoszam'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Mentés</button>
                <span id="profilStatus" style="margin-left:16px; font-weight:600;"></span>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all panels
    document.querySelectorAll('.tab-content-panel').forEach(p => p.style.display = 'none');
    
    // Reset all buttons style to outline
    document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
        btn.className = "btn btn-outline btn-sm";
    });
    
    // Show active panel & button style
    document.getElementById('panel-' + tabName).style.display = 'block';
    document.getElementById('tab-btn-' + tabName).className = "btn btn-primary btn-sm";
}

document.getElementById('profilForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const status = document.getElementById('profilStatus');
    status.textContent = 'Mentés folyamatban...';
    status.style.color = 'var(--text-light)';
    
    try {
        const res = await fetch('<?= BASE_URL ?>/auth/profil.php', { method: 'POST', body: formData });
        const data = await res.json();
        status.textContent = data.success ? '✅ Profil sikeresen frissítve!' : '❌ ' + data.message;
        status.style.color = data.success ? 'var(--success)' : 'var(--danger)';
    } catch(err) { 
        status.textContent = '❌ Hálózati hiba történt!'; 
        status.style.color = 'var(--danger)'; 
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
