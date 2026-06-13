</main>

<footer>
    <div class="container">
        <div>
            <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; color: white; margin-bottom: 15px;">Ország<span>Közepe</span>.hu</h4>
            <p style="font-size:0.9rem;opacity:0.8;line-height:1.7;">Magyarország egyik leggyorsabban növekvő apróhirdetési portálja. Találd meg álmaid állását, autóját, otthonát nálunk!</p>
        </div>
        <div>
            <h4>Kategóriák</h4>
            <a href="<?= BASE_URL ?>/kategoria/allas">Állás</a>
            <a href="<?= BASE_URL ?>/kategoria/ingatlan">Ingatlan</a>
            <a href="<?= BASE_URL ?>/kategoria/jarmu">Jármű</a>
            <a href="<?= BASE_URL ?>/kategoria/muszaki">Műszaki cikk</a>
            <a href="<?= BASE_URL ?>/kategoria/haztartas">Háztartás</a>
        </div>
        <div>
            <h4>Információk</h4>
            <a href="<?= BASE_URL ?>/aszf">ÁSZF</a>
            <a href="<?= BASE_URL ?>/adatvedelem">Adatvédelmi Nyilatkozat</a>
            <a href="<?= BASE_URL ?>/gyik">GYIK</a>
            <a href="<?= BASE_URL ?>/kapcsolat">Kapcsolat</a>
        </div>
        <div>
            <h4>Fiók</h4>
            <?php if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/fiokom">Fiókom</a>
                <a href="<?= BASE_URL ?>/fiokom?tab=hirdetesek">Hirdetéseim</a>
                <a href="<?= BASE_URL ?>/fiokom?tab=kedvencek">Kedvencek</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/belepes">Belépés</a>
                <a href="<?= BASE_URL ?>/regisztracio">Regisztráció</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="bottom">
        <div class="container">© <?= date('Y') ?> Ország Közepe. Minden jog fenntartva.</div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/js/main.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
