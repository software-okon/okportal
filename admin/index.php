<?php
/**
 * admin/index.php - Admin dashboard & moderation center
 */
require_once __DIR__ . '/../functions.php';
requireAdmin();

$pdo = getDB();

// 1. Statisztikák lekérése
$statok = [];
$statok['felhasznalok'] = $pdo->query("SELECT COUNT(*) FROM felhasznalok WHERE aktiv = 1")->fetchColumn();
$statok['hirdetesek'] = $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz != 'torolt'")->fetchColumn();
$statok['fuggoben'] = $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'fuggoben'")->fetchColumn();
$statok['aktiv'] = $pdo->query("SELECT COUNT(*) FROM hirdetesek WHERE statusz = 'aktiv' AND lejarat > NOW()")->fetchColumn();
$statok['uzenetek'] = $pdo->query("SELECT COUNT(*) FROM uzenetek")->fetchColumn();
$statok['megtekintesek'] = $pdo->query("SELECT SUM(megtekintesek) FROM hirdetesek")->fetchColumn() ?? 0;

// 2. Függőben lévő (moderálásra váró) hirdetések lekérése
$stmt = $pdo->query("
    SELECT h.*, f.nev as felhasznalo_nev, f.email as felhasznalo_email
    FROM hirdetesek h
    LEFT JOIN felhasznalok f ON h.felhasznalo_id = f.id
    WHERE h.statusz = 'fuggoben'
    ORDER BY h.letrehozva ASC
");
$pendingHirdetesek = $stmt->fetchAll();

// 3. Legutóbbi aktív hirdetések
$stmt = $pdo->query("
    SELECT h.*, f.nev as felhasznalo_nev
    FROM hirdetesek h
    LEFT JOIN felhasznalok f ON h.felhasznalo_id = f.id
    WHERE h.statusz = 'aktiv'
    ORDER BY h.letrehozva DESC
    LIMIT 15
");
$utolsoHirdetesek = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Moderációs Központ - Ország Közepe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c6e49;
            --primary-hover: #1e4d33;
            --accent: #f9a03f;
            --bg: #f4f6f5;
            --white: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
            --shadow: 0 4px 12px rgba(0,0,0,0.03);
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.05);
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            min-height: 100vh;
        }

        /* Fejléc */
        header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow);
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
        }
        .logo span { color: var(--accent); }
        .logo-badge {
            background: rgba(44, 110, 73, 0.1);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            margin-left: 8px;
            vertical-align: middle;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-info span {
            font-size: 0.9rem;
            font-weight: 500;
        }
        .btn-logout {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--danger);
            background: rgba(239, 68, 68, 0.08);
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-logout:hover {
            background: var(--danger);
            color: white;
        }

        /* Fő tartalom */
        main {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Kártyák és rácsok */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-card h3 {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
        }
        .stat-card .value {
            font-size: 1.8rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--primary);
        }
        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary);
            opacity: 0.7;
        }
        .stat-card.fuggoben::after { background: var(--warning); }
        .stat-card.uzenetek::after { background: var(--accent); }

        /* Fülek (Tabs) */
        .tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
        }
        .tab-btn {
            background: transparent;
            border: none;
            padding: 10px 20px;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-light);
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .tab-btn:hover {
            color: var(--text);
            background: rgba(0,0,0,0.03);
        }
        .tab-btn.active {
            background: var(--primary);
            color: white;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Táblázatok */
        .table-container {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td {
            padding: 16px 20px;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border);
        }
        th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--text-light);
        }
        tr:last-child td { border-bottom: none; }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-status-aktiv { background: #d1fae5; color: #065f46; }
        .badge-status-fuggoben { background: #fef3c7; color: #92400e; }

        /* Moderációs sorok */
        .ad-item {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            transition: all 0.3s ease;
        }
        .ad-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .ad-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }
        .ad-desc {
            font-size: 0.9rem;
            color: var(--text-light);
            white-space: pre-wrap;
        }
        .ad-meta {
            font-size: 0.8rem;
            color: var(--text-light);
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .ad-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: center;
            min-width: 150px;
        }
        .btn-action {
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .btn-approve {
            background: var(--success);
            color: white;
        }
        .btn-approve:hover { background: #059669; }
        .btn-reject {
            background: var(--danger);
            color: white;
        }
        .btn-reject:hover { background: #dc2626; }
        .btn-view {
            background: var(--bg);
            color: var(--text);
            text-decoration: none;
        }
        .btn-view:hover { background: #e2e8f0; }

        /* Dialog / Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center; align-items: center;
            backdrop-filter: blur(4px);
        }
        .modal-card {
            background: white;
            border-radius: var(--radius);
            padding: 30px;
            width: 90%;
            max-width: 450px;
            box-shadow: var(--shadow-lg);
        }
        .modal-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--danger);
        }
        .modal-textarea {
            width: 100%;
            height: 100px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            outline: none;
            resize: none;
            margin-bottom: 20px;
            font-family: inherit;
        }
        .modal-btns {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        @media(max-width: 768px) {
            .ad-item { grid-template-columns: 1fr; }
            .ad-actions { flex-direction: row; }
            .ad-actions button, .ad-actions a { flex: 1; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="/" class="logo">Ország<span>Közepe</span>.hu<span class="logo-badge">ADMIN</span></a>
        <div class="user-info">
            <span>Üdv, <strong><?= htmlspecialchars($_SESSION['admin_nev']) ?></strong></span>
            <a href="/auth/logout.php" class="btn-logout">Kijelentkezés</a>
        </div>
    </div>
</header>

<main>
    <!-- Statisztikai kártyák -->
    <div class="stats-grid">
        <div class="stat-card"><h3>Regisztrált Tagok</h3><div class="value"><?= number_format($statok['felhasznalok']) ?></div></div>
        <div class="stat-card"><h3>Hirdetések</h3><div class="value"><?= number_format($statok['hirdetesek']) ?></div></div>
        <div class="stat-card fuggoben"><h3>Függőben</h3><div class="value" id="count-pending"><?= number_format($statok['fuggoben']) ?></div></div>
        <div class="stat-card"><h3>Aktív Hirdetés</h3><div class="value"><?= number_format($statok['aktiv']) ?></div></div>
        <div class="stat-card uzenetek"><h3>Összes Üzenet</h3><div class="value"><?= number_format($statok['uzenetek']) ?></div></div>
        <div class="stat-card"><h3>Megtekintések</h3><div class="value"><?= number_format($statok['megtekintesek']) ?></div></div>
    </div>

    <!-- Navigációs fülek -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('moderacio')">Moderáció (<?= count($pendingHirdetesek) ?>)</button>
        <button class="tab-btn" onclick="switchTab('utolso')">Legutóbbi aktív hirdetések</button>
    </div>

    <!-- Moderáció tartalom -->
    <div class="tab-content active" id="tab-moderacio">
        <h2 style="margin-bottom: 20px; color: var(--primary);">Jóváhagyásra váró hirdetések</h2>
        
        <div id="pending-container">
            <?php if (empty($pendingHirdetesek)): ?>
                <div class="table-container" style="padding: 40px; text-align: center; color: var(--text-light);">
                    🎉 Nincsenek moderálásra váró hirdetések!
                </div>
            <?php else: ?>
                <?php foreach ($pendingHirdetesek as $h): ?>
                    <div class="ad-item" id="ad-card-<?= $h['id'] ?>">
                        <div class="ad-info">
                            <div class="ad-title"><?= htmlspecialchars($h['cim']) ?></div>
                            <div class="ad-desc"><?= htmlspecialchars(mb_substr($h['leiras'], 0, 300)) ?><?= mb_strlen($h['leiras']) > 300 ? '...' : '' ?></div>
                            <div class="ad-meta">
                                <span>📁 Kategória: <strong><?= htmlspecialchars($h['fokategoria']) ?> / <?= htmlspecialchars($h['alkategoria']) ?></strong></span>
                                <span>📍 Helyszín: <strong><?= htmlspecialchars($h['varos']) ?> (<?= htmlspecialchars($h['megye']) ?>)</strong></span>
                                <span>👤 Hirdető: <strong><?= htmlspecialchars($h['felhasznalo_nev'] ?? 'Vendég') ?></strong> (<?= htmlspecialchars($h['email']) ?>)</span>
                                <span>📅 Feladva: <strong><?= date('Y.m.d H:i', strtotime($h['letrehozva'])) ?></strong></span>
                                <span>💰 Ár: <strong><?= match($h['ar_tipus']) { 'ingyen' => 'Ingyen', 'megbeszeles' => 'Megbeszélés', default => number_format($h['ar'], 0, ',', ' ') . ' Ft' } ?></strong></span>
                            </div>
                        </div>
                        <div class="ad-actions">
                            <button class="btn-action btn-approve" onclick="approveAd(<?= $h['id'] ?>)">Jóváhagyás</button>
                            <button class="btn-action btn-reject" onclick="openRejectModal(<?= $h['id'] ?>)">Elutasítás</button>
                            <a href="/hirdetes/<?= $h['id'] ?>-test" target="_blank" class="btn-action btn-view">Előnézet</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Legutóbbi aktív hirdetések tartalom -->
    <div class="tab-content" id="tab-utolso">
        <h2 style="margin-bottom: 20px; color: var(--primary);">Legutóbbi jóváhagyott hirdetések</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cím</th>
                        <th>Kategória</th>
                        <th>Hirdető</th>
                        <th>Státusz</th>
                        <th>Létrehozva</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utolsoHirdetesek as $h): ?>
                        <tr>
                            <td><?= $h['id'] ?></td>
                            <td><a href="/hirdetes/<?= $h['id'] ?>-detail" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: none;"><?= htmlspecialchars($h['cim']) ?></a></td>
                            <td><?= htmlspecialchars($h['fokategoria']) ?> / <?= htmlspecialchars($h['alkategoria']) ?></td>
                            <td><?= htmlspecialchars($h['felhasznalo_nev'] ?? 'Vendég') ?></td>
                            <td><span class="badge badge-status-aktiv">Aktív</span></td>
                            <td><?= date('Y-m-d H:i', strtotime($h['letrehozva'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Elutasítás modal -->
<div class="modal" id="rejectModal">
    <div class="modal-card">
        <div class="modal-title">Hirdetés elutasítása</div>
        <p style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 12px;">Add meg az elutasítás okát, amit a hirdető e-mailben fog megkapni:</p>
        <input type="hidden" id="reject-ad-id">
        <textarea class="modal-textarea" id="reject-reason" placeholder="Pl.: A hirdetés tiltott terméket tartalmaz..."></textarea>
        <div class="modal-btns">
            <button class="btn-action btn-view" onclick="closeRejectModal()" style="padding: 8px 16px;">Mégsem</button>
            <button class="btn-action btn-reject" onclick="submitReject()" style="padding: 8px 16px;">Elutasítás küldése</button>
        </div>
    </div>
</div>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(cont => cont.classList.remove('active'));
        
        event.target.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    async function approveAd(id) {
        if (!confirm('Biztosan jóváhagyod ezt a hirdetést?')) return;
        const formData = new FormData();
        formData.append('id', id);
        
        try {
            const res = await fetch('/admin/hirdetesek_kezelese.php?action=approve', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                removeAdCard(id);
            } else {
                alert('Hiba: ' + data.message);
            }
        } catch(e) {
            alert('Hálózati hiba történt!');
        }
    }

    function openRejectModal(id) {
        document.getElementById('reject-ad-id').value = id;
        document.getElementById('reject-reason').value = '';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    async function submitReject() {
        const id = document.getElementById('reject-ad-id').value;
        const reason = document.getElementById('reject-reason').value.trim();
        
        if (!reason) {
            alert('Kérlek add meg az elutasítás okát!');
            return;
        }

        const formData = new FormData();
        formData.append('id', id);
        formData.append('ok', reason);
        
        try {
            const res = await fetch('/admin/hirdetesek_kezelese.php?action=reject', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                closeRejectModal();
                removeAdCard(id);
            } else {
                alert('Hiba: ' + data.message);
            }
        } catch(e) {
            alert('Hálózati hiba történt!');
        }
    }

    function removeAdCard(id) {
        const card = document.getElementById('ad-card-' + id);
        if (card) {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => {
                card.remove();
                
                // Frissítsük a számlálót
                const countBadge = document.getElementById('count-pending');
                let count = parseInt(countBadge.textContent);
                if (count > 0) countBadge.textContent = count - 1;
                
                // Ha elfogytak
                const container = document.getElementById('pending-container');
                if (container.children.length === 0) {
                    container.innerHTML = `<div class="table-container" style="padding: 40px; text-align: center; color: var(--text-light);">🎉 Nincsenek moderálásra váró hirdetések!</div>`;
                }
            }, 300);
        }
    }
</script>
</body>
</html>
