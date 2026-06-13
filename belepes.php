<?php
require_once __DIR__ . '/functions.php';
$pageTitle = 'Bejelentkezés - Ország Közepe';
if (isLoggedIn()) redirect(BASE_URL . '/fiokom');
include __DIR__ . '/includes/header.php';
?>

<div style="max-width:420px;margin:40px auto; padding: 20px 0;">
    <div class="card" style="padding: 30px;">
        <h1 style="text-align:center;margin-bottom:24px;font-family:'Outfit',sans-serif;color:var(--primary);">Bejelentkezés</h1>
        <?php if (isset($_GET['regisztralva'])): ?><div style="background:#d4edda;color:#155724;padding:10px;border-radius:var(--radius);margin-bottom:16px;font-size:0.9rem;font-weight:600;text-align:center;">✅ Sikeres regisztráció! Kérjük jelentkezz be.</div><?php endif; ?>
        <div id="loginError" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:var(--radius);margin-bottom:16px;display:none;font-size:0.9rem;text-align:center;"></div>
        <form id="loginForm">
            <div class="form-group">
                <label>E-mail cím</label>
                <input type="email" name="email" required placeholder="pelda@email.hu">
            </div>
            <div class="form-group">
                <label>Jelszó</label>
                <input type="password" name="jelszo" required placeholder="Jelszó">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="emlekezzen" id="emlekezzen" style="width:auto;margin:0;">
                <label for="emlekezzen" style="margin:0;cursor:pointer;font-weight:500;font-size:0.9rem;">Emlékezzen rám</label>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">Bejelentkezés</button>
        </form>
        <p style="text-align:center;margin-top:20px;font-size:0.9rem;line-height:1.6;">
            <a href="<?= BASE_URL ?>/auth/jelszo_visszaallitas.php?action=request">Elfelejtett jelszó?</a><br>
            Nincs még fiókod? <a href="<?= BASE_URL ?>/regisztracio" style="font-weight:700;">Regisztrálj most!</a>
        </p>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const err = document.getElementById('loginError'); err.style.display = 'none';
    const formData = new FormData(this);
    try {
        const res = await fetch('<?= BASE_URL ?>/auth/login.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) window.location.href = data.data?.redirect || '<?= BASE_URL ?>/fiokom';
        else { err.textContent = data.message; err.style.display = 'block'; }
    } catch(ex) { err.textContent = 'Hálózati hiba történt!'; err.style.display = 'block'; }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
