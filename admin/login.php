<?php
/**
 * admin/login.php - Adminisztrátori bejelentkezés
 */
require_once __DIR__ . '/../functions.php';

if (isAdmin()) {
    redirect(BASE_URL . '/admin');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['jelszo'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Az e-mail és a jelszó megadása kötelező!';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM adminok WHERE email = :email AND aktiv = 1");
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['jelszo'])) {
            // Update last login
            $pdo->prepare("UPDATE adminok SET utolso_belepes = NOW() WHERE id = :id")->execute([':id' => $admin['id']]);
            
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_nev'] = $admin['nev'];
            $_SESSION['admin_szerep'] = $admin['szerep'];
            
            redirect(BASE_URL . '/admin');
        } else {
            $error = 'Hibás e-mail cím vagy jelszó!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Bejelentkezés - Ország Közepe</title>
    <style>
        :root {
            --primary: #2c6e49;
            --accent: #f9a03f;
            --bg: #f5f7f6;
            --white: #ffffff;
            --text: #2c3e50;
            --border: #d1d9d6;
            --radius: 12px;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            display: block;
            margin-bottom: 24px;
        }
        .logo span { color: var(--accent); }
        h1 {
            font-size: 1.3rem;
            text-align: center;
            margin-bottom: 24px;
            color: var(--primary);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            font-size: 0.95rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            outline: none;
            transition: all 0.2s;
        }
        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(44,110,73,0.1);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #1e4d33;
        }
        .error-msg {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }
        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.85rem;
        }
        .footer-link a {
            color: var(--primary);
            text-decoration: none;
        }
        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <a href="/" class="logo">Ország<span>Közepe</span>.hu</a>
        <h1>Adminisztrátori belépés</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label for="email">E-mail cím</label>
                <input type="email" id="email" name="email" required placeholder="admin@orszagkozepe.hu">
            </div>
            <div class="form-group">
                <label for="jelszo">Jelszó</label>
                <input type="password" id="jelszo" name="jelszo" required placeholder="Jelszó">
            </div>
            <button type="submit" class="btn">Belépés</button>
        </form>
        
        <div class="footer-link">
            <a href="/">← Vissza a főoldalra</a>
        </div>
    </div>
</body>
</html>
