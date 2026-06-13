<?php
require_once __DIR__ . '/functions.php';
$pageTitle = 'Regisztráció - Ország Közepe';
if (isLoggedIn()) redirect(BASE_URL . '/fiokom');
include __DIR__ . '/includes/header.php';
?>

<div style="max-width:460px;margin:40px auto; padding: 20px 0;">
    <div class="card" style="padding: 30px;">
        <h1 style="text-align:center;margin-bottom:24px;font-family:'Outfit',sans-serif;color:var(--primary);">Regisztráció</h1>
        <div id="regError" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:var(--radius);margin-bottom:16px;display:none;font-size:0.9rem;text-align:center;"></div>
        <form id="regForm">
            <div class="form-group">
                <label>Teljes név *</label>
                <input type="text" name="nev" required placeholder="Kovács Anna">
            </div>
            <div class="form-group">
                <label>E-mail cím *</label>
                <input type="email" name="email" required placeholder="anna@email.hu">
            </div>
            <div class="form-group">
                <label>Telefon</label>
                <input type="tel" name="telefon" placeholder="+36 30 123 4567">
            </div>
            <div class="form-group">
                <label>Jelszó *</label>
                <input type="password" name="jelszo" required placeholder="Minimum 8 karakter, nagybetű, szám">
            </div>
            <div class="form-group">
                <label>Jelszó megerősítése *</label>
                <input type="password" name="jelszo2" required placeholder="Jelszó újra">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="aszf" id="aszf" required style="width:auto;margin:0;">
                <label for="aszf" style="margin:0;cursor:pointer;font-weight:500;font-size:0.9rem;">Elfogadom az <a href="<?= BASE_URL ?>/aszf" target="_blank" style="font-weight:700;">ÁSZF</a>-et *</label>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:16px;">Regisztráció</button>
        </form>
        <p style="text-align:center;margin-top:20px;font-size:0.9rem;">Van már fiókod? <a href="<?= BASE_URL ?>/belepes" style="font-weight:700;">Jelentkezz be!</a></p>
    </div>
</div>

<script>
document.getElementById('regForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const err = document.getElementById('regError'); err.style.display = 'none';
    const formData = new FormData(this);
    try {
        const res = await fetch('<?= BASE_URL ?>/auth/register.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) window.location.href = '<?= BASE_URL ?>/belepes?regisztralva=1';
        else { err.textContent = data.message; err.style.display = 'block'; }
    } catch(ex) { err.textContent = 'Hálózati hiba történt!'; err.style.display = 'block'; }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
