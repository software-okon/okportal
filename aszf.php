<?php
/**
 * aszf.php - Általános Szerződési Feltételek (ÁSZF)
 */
require_once __DIR__ . '/functions.php';
$pageTitle = 'Általános Szerződési Feltételek (ÁSZF) - Ország Közepe';
include __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 800px; padding: 20px 0;">
    <div class="card" style="padding: 40px;">
        <h1 style="color: var(--primary); margin-bottom: 24px;">Általános Szerződési Feltételek</h1>
        <p style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 24px;">Utoljára frissítve: <?= date('Y.m.d') ?></p>
        
        <div style="line-height: 1.8;">
            <h3 style="margin-top: 24px; color: var(--primary);">1. Bevezetés</h3>
            <p>Üdvözöljük az Ország Közepe apróhirdetési portálon. A honlap használatával Ön elfogadja a jelen Általános Szerződési Feltételeket. Kérjük, figyelmesen olvassa el őket.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">2. Szolgáltatás leírása</h3>
            <p>Az Ország Közepe egy online apróhirdetési platform, ahol regisztrált és regisztráció nélküli felhasználók apróhirdetéseket tehetnek közzé, kereshetnek a meglévők között, és kapcsolatba léphetnek a hirdetőkkel.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">3. Regisztráció és fiók biztonsága</h3>
            <p>Bizonyos szolgáltatások használatához regisztráció szükséges. A felhasználó felelős a fiókjához tartozó jelszó biztonságáért és a fiókon keresztül végzett minden tevékenységért.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">4. Hirdetési szabályzat</h3>
            <p>A hirdetések tartalmáért kizárólag a hirdetés feladója vállal felelősséget. Tilos jogszabályba ütköző, sértő, félrevezető, vagy harmadik fél jogait sértő tartalmak elhelyezése.</p>
            
            <h3 style="margin-top: 24px; color: var(--primary);">5. Felelősség korlátozása</h3>
            <p>Az üzemeltető nem vállal felelősséget a platformon közzétett hirdetések valódiságáért, minőségéért, valamint a felhasználók közötti tranzakciókból eredő esetleges károkért.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
