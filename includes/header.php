<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Ország Közepe Apróhirdetések' ?></title>
    <meta name="description" content="<?= $pageDescription ?? 'Ország Közepe - Apróhirdetések, állások, ingatlanok, használt autók és még sok más. Töltsd fel hirdetésed ingyen!' ?>">
    <link rel="stylesheet" href="/css/style.css">
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<header>
    <div class="container">
        <a href="/" class="logo">Ország<span>Közepe</span>.hu</a>
        <div class="nav-links">
            <a href="/hirdetesek">Összes hirdetés</a>
            <a href="/hirdetes-feladas">+ Hirdetés feladása</a>
            <?php if (isLoggedIn()): ?>
                <span style="font-size:0.85rem;color:var(--text-light);">Üdv, <?= htmlspecialchars($_SESSION['user_nev']) ?>!</span>
                <a href="/fiokom">Fiókom</a>
                <a href="/auth/logout.php">Kilépés</a>
            <?php else: ?>
                <a href="/belepes">Belépés</a>
                <a href="/regisztracio">Regisztráció</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main>