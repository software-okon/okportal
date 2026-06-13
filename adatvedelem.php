<?php
/**
 * adatvedelem.php - Adatvédelmi Tájékoztató
 */
require_once __DIR__ . '/functions.php';
$pageTitle = 'Adatvédelmi Nyilatkozat - Ország Közepe';
$pageDescription = 'Adatvédelmi Nyilatkozat - Ország Közepe';
include __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 800px; padding: 20px 0;">
    <div class="card" style="padding: 40px;">
        <h1 style="color: var(--primary); margin-bottom: 24px;">Adatvédelmi Tájékoztató</h1>
        <p style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 24px;">Utoljára frissítve: <?= date('Y.m.d') ?></p>
        
        <div style="line-height: 1.8;">
            <h3 style="margin-top: 24px; color: var(--primary);">1. Milyen adatokat gyűjtünk?</h3>
            <p>A regisztráció során megadott adatokat (név, e-mail cím, telefonszám, város, irányítószám), valamint hirdetésfeladáskor a hirdetések részleteit és a kapcsolódó fotókat.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">2. Mire használjuk fel az adatokat?</h3>
            <p>Az adatokat a szolgáltatás működtetéséhez, a hirdetések közzétételéhez, a kapcsolatfelvétel biztosításához és statisztikák készítéséhez használjuk.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">3. Adatmegőrzési idő</h3>
            <p>A felhasználói adatokat a fiók törléséig, a hirdetési adatokat a hirdetés törléséig vagy a lejárati határidő leteltéig őrizzük meg.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">4. Az Ön jogai</h3>
            <p>Bármikor kérheti személyes adatai módosítását vagy törlését fiókjába belépve, vagy ügyfélszolgálatunkon keresztül.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
