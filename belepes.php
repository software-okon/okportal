<?php
$pageTitle = 'Bejelentkezés - Ország Közepe';
if (isLoggedIn()) redirect('/fiokom');
include __DIR__ . '/includes/header.php';
?>

<div style="max-width:420px;margin:40px auto;">
    <div class="card">
        <div style="text-align:center;margin-bottom:20px;">
            <a href="/" style="font-size:1.4rem;font-weight:800;color:var(--primary);text-decoration:none;">Ország<span style="color:var(--accent);">Közepe</span>.hu</a>
        </div>
        <h1 style="text-align:center;margin-bottom:24px;">Bejelentkezés</h1>
        <?php if (isset($_GET['regisztralva'])): ?><div style="background:#d4edda;color:#155724;padding:10px;border-radius:var(--radius);margin-bottom:16px;">✅ Sikeres regisztráció! Jelentkezz be.</div><?php endif; ?>
        <div id="loginError" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:var(--radius);margin-bottom:16px;display:none;"></div>
        <form id="loginForm">
            <div class="form-group"><label>E-mail</label><input type="email" name="email" required placeholder="anna@email.hu"></div>
            <div class="form-group"><label>Jelszó</label><input type="password" name="jelszo" required placeholder="Jelszó"></div>
            <div class="form-group"><label><input type="checkbox" name="emlekezzen"> Emlékezzen rám</label></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Bejelentkezés</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:0.9rem;">
            <a href="/auth/jelszo_visszaallitas.php?action=request">Elfelejtett jelszó?</a><br>
            Nincs fiókod? <a href="/regisztracio">Regisztrálj!</a>
        </p>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const err = document.getElementById('loginError'); err.style.display = 'none';
    const formData = new FormData(this);
    try {
        const res = await fetch('/auth/login.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) window.location.href = data.data?.redirect || '/fiokom';
        else { err.textContent = data.message; err.style.display = 'block'; }
    } catch(ex) { err.textContent = 'Hálózati hiba!'; err.style.display = 'block'; }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>