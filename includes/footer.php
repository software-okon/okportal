</main>

<footer>
    <div class="container">
        <div>
            <h4>Ország Közepe</h4>
            <p style="font-size:0.9rem;opacity:0.8;">Magyarország egyik legnagyobb apróhirdetési portálja. Találd meg álmaid állását, autóját, otthonát nálunk!</p>
        </div>
        <div>
            <h4>Kategóriák</h4>
            <a href="/kategoria/allas">Állás</a>
            <a href="/kategoria/ingatlan">Ingatlan</a>
            <a href="/kategoria/jarmu">Jármű</a>
            <a href="/kategoria/muszaki">Műszaki cikk</a>
            <a href="/kategoria/haztartas">Háztartás</a>
        </div>
        <div>
            <h4>Információk</h4>
            <a href="/aszf">ÁSZF</a>
            <a href="/adatvedelem">Adatvédelem</a>
            <a href="/gyik">GYIK</a>
            <a href="/kapcsolat">Kapcsolat</a>
        </div>
        <div>
            <h4>Fiók</h4>
            <?php if (isLoggedIn()): ?>
                <a href="/fiokom">Fiókom</a>
                <a href="/fiokom?tab=hirdetesek">Hirdetéseim</a>
                <a href="/fiokom?tab=kedvencek">Kedvencek</a>
            <?php else: ?>
                <a href="/belepes">Belépés</a>
                <a href="/regisztracio">Regisztráció</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="bottom">
        <div class="container">© <?= date('Y') ?> Ország Közepe. Minden jog fenntartva.</div>
    </div>
</footer>

<script src="/js/main.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>