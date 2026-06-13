<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Ország Közepe Apróhirdetések' ?></title>
    <meta name="description" content="<?= $pageDescription ?? 'Ország Közepe - Apróhirdetések, állások, ingatlanok, használt autók és még sok más. Töltsd fel hirdetésed ingyen!' ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <?php if (isset($extraHead)) echo $extraHead; ?>
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>

<header>
    <div class="container">
        <a href="<?= BASE_URL ?>/" class="logo">Ország<span>Közepe</span>.hu</a>
        <div class="nav-links">
            <a href="<?= BASE_URL ?>/hirdetesek">Összes hirdetés</a>
            <a href="<?= BASE_URL ?>/hirdetes-feladas" class="btn btn-accent btn-sm">+ Hirdetés feladása</a>
            <?php if (isLoggedIn()): ?>
                <span style="font-size:0.85rem;color:var(--text-light);font-weight:500;">Üdv, <?= htmlspecialchars($_SESSION['user_nev']) ?>!</span>
                <a href="<?= BASE_URL ?>/fiokom">Fiókom</a>
                <a href="<?= BASE_URL ?>/auth/logout.php">Kilépés</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/belepes">Belépés</a>
                <a href="<?= BASE_URL ?>/regisztracio">Regisztráció</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main>
