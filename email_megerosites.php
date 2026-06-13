<?php
/**
 * email_megerosites.php - Felhasználó e-mail címének megerősítése
 */
require_once __DIR__ . '/functions.php';

$token = cleanInput($_GET['token'] ?? '');
$success = false;
$message = '';

if (empty($token)) {
    $message = 'Érvénytelen vagy hiányzó megerősítési kulcs (token)!';
} else {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, nev FROM felhasznalok WHERE email_token = :token");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Megerősítés
        $stmtUpdate = $pdo->prepare("UPDATE felhasznalok SET email_ellenorizve = 1, email_token = NULL WHERE id = :id");
        $stmtUpdate->execute([':id' => $user['id']]);
        $success = true;
        $message = "Kedves {$user['nev']}, az e-mail címed megerősítése sikeresen megtörtént!";
    } else {
        $message = 'A megadott megerősítési kulcs érvénytelen vagy már felhasználták!';
    }
}

$pageTitle = 'E-mail megerősítés - Ország Közepe';
include __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 600px; margin: 40px auto;">
    <div class="card" style="text-align: center; padding: 40px;">
        <?php if ($success): ?>
            <div style="font-size: 3rem; margin-bottom: 20px;">🎉</div>
            <h1 style="color: var(--success); margin-bottom: 16px;">Sikeres megerősítés!</h1>
            <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 24px;"><?= htmlspecialchars($message) ?></p>
            <a href="/belepes" class="btn btn-primary btn-lg" style="width: 100%;">Bejelentkezés</a>
        <?php else: ?>
            <div style="font-size: 3rem; margin-bottom: 20px;">⚠️</div>
            <h1 style="color: var(--danger); margin-bottom: 16px;">Sikertelen megerősítés</h1>
            <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 24px;"><?= htmlspecialchars($message) ?></p>
            <a href="/" class="btn btn-outline" style="width: 100%;">Vissza a főoldalra</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
