<?php
$pageTitle = 'Regisztráció - Ország Közepe';
if (isLoggedIn()) redirect('/fiokom');
include __DIR__ . '/includes/header.php';
?>

<div style="max-width:460px;margin:40px auto;">
    <div class="card">
        <div style="text-align:center;margin-bottom:20px;">
            <a href="/" style="font-size:1.4rem;font-weight:800;color:var(--primary);text-decoration:none;">Ország<span style="color:var(--accent);">Közepe</span>.hu</a>
        </div>
        <h1 style="text-align:center;margin-bottom:24px;">Regisztráció</h1>
        <div id="regError" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:var(--radius);margin-bottom:16px;display:none;"></div>
        <form id="regForm">
            <div class="form-group"><label>Teljes név *</label><input type="text" name="nev" required placeholder="Kovács Anna"></div>
            <div class="form-group"><label>E-mail *</label><input type="email" name="email" required placeholder="anna@email.hu"></div>
            <div class="form-group"><label>Telefon</label><input type="tel" name="telefon" placeholder="+36 30 123 4567"></div>
            <div class="form-group"><label>Jelszó *</label><input type="password" name="jelszo" required placeholder="Minimum 8 karakter, nagybetű, szám"></div>
            <div class="form-group"><label>Jelszó megerősítése *</label><input type="password" name="jelszo2" required placeholder="Jelszó újra"></div>
            <div class="form-group"><label><input type="checkbox" name="aszf" required> Elfogadom az <a href="/aszf">ÁSZF</a>-et *</label></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Regisztráció</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:0.9rem;">Van már fiókod? <a href="/belepes">Jelentkezz be!</a></p>
    </div>
</div>

<script>
document.getElementById('regForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const err = document.getElementById('regError'); err.style.display = 'none';
    const formData = new FormData(this);
    try {
        const res = await fetch('/auth/register.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) window.location.href = '/belepes?regisztralva=1';
        else { err.textContent = data.message; err.style.display = 'block'; }
    } catch(ex) { err.textContent = 'Hálózati hiba!'; err.style.display = 'block'; }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>