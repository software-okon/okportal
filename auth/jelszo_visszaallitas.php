<?php
/**
 * auth/jelszo_visszaallitas.php - Jelszó visszaállítás kérése és végrehajtása
 */
require_once __DIR__ . '/../functions.php';

$pdo = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? 'request';

// ========================
// 1. VISSZAÁLLÍTÁS KÉRÉSE (email küldés)
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'request') {
    $email = cleanInput($_POST['email'] ?? '');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Érvénytelen e-mail cím!', [], 422);
    }
    
    // Felhasználó keresése
    $stmt = $pdo->prepare("SELECT id, nev, email FROM felhasznalok WHERE email = :email AND aktiv = 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Token generálása
        $token = bin2hex(random_bytes(32));
        $lejarat = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $pdo->prepare("UPDATE felhasznalok SET jelszo_visszaallitas_token = :token, jelszo_token_lejarat = :lejarat WHERE id = :id");
        $stmt->execute([':token' => $token, ':lejarat' => $lejarat, ':id' => $user['id']]);
        
        // Email küldése
        $resetLink = BASE_URL . "/auth/jelszo_visszaallitas.php?action=reset&token={$token}";
        $subject = "Jelszó visszaállítás - Ország Közepe";
        $body = "Kedves {$user['nev']}!\n\n";
        $body .= "Kérvényezted a jelszavad visszaállítását az Ország Közepe portálon.\n\n";
        $body .= "Kattints az alábbi linkre az új jelszó megadásához:\n";
        $body .= "{$resetLink}\n\n";
        $body .= "A link 1 órán belül érvényes.\n\n";
        $body .= "Ha nem te kérvényezted a jelszó visszaállítást, hagyd figyelmen kívül ezt az emailt.\n\n";
        $body .= "Üdvözlettel,\nAz Ország Közepe csapata";
        
        sendEmail($user['email'], $subject, $body);
    }
    
    // Mindig ugyanazt a választ adjuk (biztonság)
    jsonResponse(true, 'Ha a megadott e-mail cím létezik, elküldtük a visszaállítási linket.');
}

// ========================
// 2. TOKEN ELLENŐRZÉSE (GET)
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'reset') {
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        die('Érvénytelen token!');
    }
    
    $stmt = $pdo->prepare("SELECT id, nev, email FROM felhasznalok WHERE jelszo_visszaallitas_token = :token AND jelszo_token_lejarat > NOW()");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die('A token érvénytelen vagy lejárt. Kérj új jelszó visszaállítást!');
    }
    
    // HTML űrlap megjelenítése
    ?>
    <!DOCTYPE html>
    <html lang="hu">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Új jelszó megadása - Ország Közepe</title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; background: #f7f9f8; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
            .card { background: white; padding: 32px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); width: 100%; max-width: 420px; }
            h2 { color: #2c6e49; margin-top: 0; }
            .form-group { margin-bottom: 16px; }
            label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px; }
            input[type="password"] { width: 100%; padding: 12px; border: 1.5px solid #d1d9d6; border-radius: 8px; font-size: 15px; box-sizing: border-box; }
            input[type="password"]:focus { outline: none; border-color: #2c6e49; box-shadow: 0 0 0 3px rgba(44,110,73,0.1); }
            .btn { width: 100%; padding: 14px; background: #2c6e49; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
            .btn:hover { background: #1e4d33; }
            .error { color: #c0392b; font-size: 13px; margin-top: 4px; display: none; }
            .success { color: #27ae60; font-size: 14px; text-align: center; display: none; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>Új jelszó megadása</h2>
            <p style="color:#666;margin-bottom:20px;">Add meg az új jelszavadat, <?= htmlspecialchars($user['nev']) ?>!</p>
            <form id="resetForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="action" value="update">
                <div class="form-group">
                    <label for="jelszo">Új jelszó</label>
                    <input type="password" id="jelszo" name="jelszo" required minlength="8" placeholder="Minimum 8 karakter, nagybetű, szám">
                    <div class="error" id="jelszoError"></div>
                </div>
                <div class="form-group">
                    <label for="jelszo2">Jelszó megerősítése</label>
                    <input type="password" id="jelszo2" name="jelszo2" required minlength="8" placeholder="Jelszó újra">
                    <div class="error" id="jelszo2Error"></div>
                </div>
                <button type="submit" class="btn">Jelszó módosítása</button>
                <div class="success" id="successMsg"></div>
            </form>
        </div>
        <script>
            document.getElementById('resetForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                document.querySelectorAll('.error').forEach(el => el.style.display = 'none');
                document.getElementById('successMsg').style.display = 'none';
                
                const jelszo = document.getElementById('jelszo').value;
                const jelszo2 = document.getElementById('jelszo2').value;
                let valid = true;
                
                if (jelszo.length < 8) { document.getElementById('jelszoError').textContent = 'Minimum 8 karakter!'; document.getElementById('jelszoError').style.display = 'block'; valid = false; }
                if (!/[A-Z]/.test(jelszo)) { document.getElementById('jelszoError').textContent = 'Tartalmaznia kell nagybetűt!'; document.getElementById('jelszoError').style.display = 'block'; valid = false; }
                if (!/[0-9]/.test(jelszo)) { document.getElementById('jelszoError').textContent = 'Tartalmaznia kell számot!'; document.getElementById('jelszoError').style.display = 'block'; valid = false; }
                if (jelszo !== jelszo2) { document.getElementById('jelszo2Error').textContent = 'A két jelszó nem egyezik!'; document.getElementById('jelszo2Error').style.display = 'block'; valid = false; }
                if (!valid) return;
                
                const formData = new FormData(this);
                const response = await fetch('jelszo_visszaallitas.php', { method: 'POST', body: formData });
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('successMsg').textContent = data.message + ' Átirányítás a bejelentkezéshez...';
                    document.getElementById('successMsg').style.display = 'block';
                    setTimeout(() => { window.location.href = '<?= BASE_URL ?>/belepes'; }, 2000);
                } else {
                    document.getElementById('jelszoError').textContent = data.message;
                    document.getElementById('jelszoError').style.display = 'block';
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// ========================
// 3. ÚJ JELSZÓ MENTÉSE
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $token = $_POST['token'] ?? '';
    $jelszo = $_POST['jelszo'] ?? '';
    $jelszo2 = $_POST['jelszo2'] ?? '';
    
    $errors = [];
    if (mb_strlen($jelszo) < 8) $errors[] = 'A jelszó minimum 8 karakter!';
    if (!preg_match('/[A-Z]/', $jelszo)) $errors[] = 'A jelszó tartalmazzon nagybetűt!';
    if (!preg_match('/[0-9]/', $jelszo)) $errors[] = 'A jelszó tartalmazzon számot!';
    if ($jelszo !== $jelszo2) $errors[] = 'A két jelszó nem egyezik!';
    
    if (!empty($errors)) {
        jsonResponse(false, $errors[0], [], 422);
    }
    
    $stmt = $pdo->prepare("SELECT id FROM felhasznalok WHERE jelszo_visszaallitas_token = :token AND jelszo_token_lejarat > NOW()");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(false, 'A token érvénytelen vagy lejárt!', [], 400);
    }
    
    $hashedPassword = password_hash($jelszo, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
    
    $stmt = $pdo->prepare("UPDATE felhasznalok SET jelszo = :jelszo, jelszo_visszaallitas_token = NULL, jelszo_token_lejarat = NULL WHERE id = :id");
    $stmt->execute([':jelszo' => $hashedPassword, ':id' => $user['id']]);
    
    jsonResponse(true, 'Jelszó sikeresen módosítva!');
}