<?php
/**
 * gyik.php - Gyakran Ismételt Kérdések (GYIK)
 */
require_once __DIR__ . '/functions.php';
$pageTitle = 'Gyakran Ismételt Kérdések (GYIK) - Ország Közepe';
include __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 800px; padding: 20px 0;">
    <div class="card" style="padding: 40px;">
        <h1 style="color: var(--primary); margin-bottom: 24px;">Gyakran Ismételt Kérdések</h1>
        
        <div style="line-height: 1.8;">
            <h3 style="margin-top: 24px; color: var(--primary);">Mennyibe kerül a hirdetés feladása?</h3>
            <p>Az Ország Közepe apróhirdetési portálon a hirdetések feladása teljesen **ingyenes** az alapcsomag választása esetén. Lehetőség van kiemelések vásárlására is (Normál vagy Prémium csomag), amelyekkel a hirdetés előrébb sorolódik a találati listákban.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">Mennyi ideig látható egy hirdetés?</h3>
            <p>A hirdetés feladásakor kiválasztható az érvényességi idő, ami 7, 14, 30 vagy 60 nap lehet. Az érvényesség lejárta előtt e-mailben értesítést küldünk, és a hirdetés meghosszabbítható.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">Hogyan szerkeszthetem vagy törölhetem a hirdetésemet?</h3>
            <p>Ha bejelentkezel a fiókodba, a **"Fiókom / Hirdetéseim"** menüpont alatt megtekintheted, szerkesztheted, inaktiválhatod vagy törölheted a korábban feladott hirdetéseidet.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">Nem kaptam meg az e-mail megerősítő linket. Mit tegyek?</h3>
            <p>Kérjük, ellenőrizd a Levélszemét (Spam) vagy a Promóciók mappát is a levelezőrendszeredben. Ha továbbra sem találod, próbálj meg belépni, és kérd a link újraküldését.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
