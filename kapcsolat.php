<?php
/**
 * kapcsolat.php - Kapcsolati űrlap és adatok
 */
require_once __DIR__ . '/functions.php';
$pageTitle = 'Kapcsolat - Ország Közepe';
include __DIR__ . '/includes/header.php';

$sent = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nev = cleanInput($_POST['nev'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $targy = cleanInput($_POST['targy'] ?? '');
    $uzenet = cleanInput($_POST['uzenet'] ?? '');
    
    if (empty($nev) || empty($email) || empty($targy) || empty($uzenet)) {
        $error = 'Minden mező kitöltése kötelező!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Érvénytelen e-mail cím!';
    } else {
        // Mock email sending
        $sent = true;
    }
}
?>

<div class="container" style="max-width: 800px; padding: 20px 0;">
    <div class="card" style="padding: 40px;">
        <h1 style="color: var(--primary); margin-bottom: 24px;">Kapcsolatfelvétel</h1>
        <p style="color: var(--text-light); margin-bottom: 30px;">Kérdése, észrevétele van? Írjon nekünk, és munkatársunk hamarosan válaszol!</p>
        
        <?php if ($sent): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 16px; border-radius: var(--radius); margin-bottom: 24px; border-left: 4px solid #2e7d32;">
                ✅ Üzenetét sikeresen rögzítettük! Hamarosan felvesszük Önnel a kapcsolatot.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div style="background: #ffebee; color: #c62828; padding: 16px; border-radius: var(--radius); margin-bottom: 24px; border-left: 4px solid #c62828;">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <form action="/kapcsolat" method="POST">
                <div class="form-group">
                    <label for="nev">Név *</label>
                    <input type="text" id="nev" name="nev" required placeholder="Kovács Béla" value="<?= htmlspecialchars($nev ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" required placeholder="bela@email.hu" value="<?= htmlspecialchars($email ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="targy">Tárgy *</label>
                    <input type="text" id="targy" name="targy" required placeholder="Hirdetéssel kapcsolatos kérdés" value="<?= htmlspecialchars($targy ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="uzenet">Üzenet *</label>
                    <textarea id="uzenet" name="uzenet" required placeholder="Írja le a kérdését..." style="min-height: 120px;"><?= htmlspecialchars($uzenet ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Küldés</button>
            </form>
            
            <div style="background: var(--bg); padding: 24px; border-radius: var(--radius); height: fit-content;">
                <h3 style="color: var(--primary); margin-bottom: 16px;">Ügyfélszolgálat</h3>
                <p style="margin-bottom: 12px;"><strong>E-mail:</strong> info@orszagkozepe.hu</p>
                <p style="margin-bottom: 12px;"><strong>Telefon:</strong> +36 30 123 4567</p>
                <p style="margin-bottom: 20px;"><strong>Nyitvatartás:</strong> H-P: 8:00 - 16:00</p>
                
                <h3 style="color: var(--primary); margin-bottom: 12px;">Cégadatok</h3>
                <p style="font-size: 0.9rem; color: var(--text-light); line-height: 1.6;">
                    Ország Közepe Kft.<br>
                    Székhely: 5000 Szolnok, Kossuth Lajos út 1.<br>
                    Cégjegyzékszám: 01-09-123456<br>
                    Adószám: 12345678-2-41
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
