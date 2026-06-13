<?php
$pageTitle = 'Hirdetés feladása - Ország Közepe';
require_once __DIR__ . '/functions.php';
$extraHead = '<style>.dynamic-section{display:none;}.dynamic-section.active{display:block;animation:fadeIn 0.3s ease;}@keyframes fadeIn{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}.conditional-row,.conditional-field{display:none;}.conditional-row.show{display:flex;}.conditional-field.show{display:block;}.file-upload-area{border:2px dashed var(--border);border-radius:var(--radius);padding:28px 20px;text-align:center;cursor:pointer;background:#fafbfb;}.file-upload-area:hover{border-color:var(--primary);background:rgba(44,110,73,0.03);}.file-upload-area input[type="file"]{display:none;}.preview-container{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px;}.preview-item{width:80px;height:80px;border-radius:6px;border:1px solid var(--border);background-size:cover;background-position:center;position:relative;}.preview-item .remove-img{position:absolute;top:-8px;right:-8px;width:22px;height:22px;background:var(--danger);color:white;border-radius:50%;border:none;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;}.row{display:flex;gap:16px;flex-wrap:wrap;}.row .form-group{flex:1;min-width:200px;}@media(max-width:500px){.row{flex-direction:column;}.row .form-group{min-width:100%;}}.checkbox-group,.radio-group{display:flex;flex-wrap:wrap;gap:10px 18px;padding-top:4px;}.checkbox-group label,.radio-group label{font-weight:400;font-size:0.9rem;display:flex;align-items:center;gap:6px;cursor:pointer;}.error-message{color:var(--danger);font-size:0.82rem;margin-top:4px;display:none;}.form-group.error input,.form-group.error select,.form-group.error textarea{border-color:var(--danger);}.form-group.error .error-message{display:block;}.success-message{display:none;text-align:center;padding:40px 20px;}.success-message.active{display:block;}.success-message h2{color:var(--success);}</style>';

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:800px;">
    <div style="text-align:center;margin-bottom:32px;padding-bottom:20px;border-bottom:2px solid var(--border);">
        <h1 style="color:var(--primary);">Hirdetés feladása</h1>
        <p style="color:var(--text-light);">Töltsd ki az űrlapot, és hirdetésed perceken belül megjelenik!</p>
    </div>

    <form id="hirdetesForm" action="/submit_hirdetes.php" method="POST" enctype="multipart/form-data" novalidate>
        <div class="card" style="margin-bottom:20px;">
            <h3 style="margin-bottom:16px;color:var(--primary);">Alapadatok</h3>
            
            <div class="row">
                <div class="form-group">
                    <label for="fokategoria">Főkategória <span class="required">*</span></label>
                    <select id="fokategoria" name="fokategoria" required>
                        <option value="">-- Válassz --</option>
                        <option value="allas">Állás</option><option value="ingatlan">Ingatlan</option><option value="jarmu">Jármű</option><option value="muszaki">Műszaki cikk</option><option value="haztartas">Háztartás, bútor</option><option value="szolgaltatas">Szolgáltatás</option><option value="hobbi">Hobbi, sport</option><option value="ruhazat">Ruházat, divat</option><option value="allatok">Állatok</option><option value="egyeb">Egyéb</option>
                    </select>
                    <div class="error-message">Válassz főkategóriát!</div>
                </div>
                <div class="form-group">
                    <label for="alkategoria">Alkategória <span class="required">*</span></label>
                    <select id="alkategoria" name="alkategoria" required disabled><option value="">-- Először válassz főkategóriát --</option></select>
                    <div class="error-message">Válassz alkategóriát!</div>
                </div>
            </div>
            
            <div class="form-group"><label for="cim">Hirdetés címe <span class="required">*</span></label><input type="text" id="cim" name="cim" maxlength="80" required placeholder="Pl.: Eladó használt mosógép"><div class="hint">Maximum 80 karakter.</div><div class="error-message">Adj meg egy címet!</div></div>
            <div class="form-group"><label for="leiras">Leírás <span class="required">*</span></label><textarea id="leiras" name="leiras" minlength="20" required placeholder="Részletes leírás..."></textarea><div class="hint">Minimum 20 karakter.</div><div class="error-message">Minimum 20 karakter!</div></div>
            
            <div class="row">
                <div class="form-group"><label for="ar">Ár <span class="required">*</span></label><input type="number" id="ar" name="ar" min="0" required placeholder="Pl.: 25000"><div class="error-message">Adj meg érvényes árat!</div></div>
                <div class="form-group"><label for="arTipus">Ár típusa</label><select id="arTipus" name="arTipus"><option value="fix">Fix ár</option><option value="megbeszeles">Megbeszélés szerint</option><option value="ingyen">Ingyen elvihető</option></select></div>
            </div>
            
            <div class="form-group"><label>Képek (max. 10 db)</label><div class="file-upload-area" id="fileDropArea"><span style="font-size:2rem;">🖼️</span><p>Kattints ide vagy húzd be a képeket</p><p class="hint">Max. 10 kép, max. 5 MB (jpg, png, webp)</p><input type="file" id="kepek" name="kepek[]" accept="image/jpeg,image/png,image/webp" multiple></div><div class="preview-container" id="previewContainer"></div></div>
            
            <div class="row">
                <div class="form-group"><label for="megye">Megye <span class="required">*</span></label><select id="megye" name="megye" required><option value="">-- Válassz --</option><option>Bács-Kiskun</option><option>Baranya</option><option>Békés</option><option>Borsod-Abaúj-Zemplén</option><option>Budapest</option><option>Csongrád-Csanád</option><option>Fejér</option><option>Győr-Moson-Sopron</option><option>Hajdú-Bihar</option><option>Heves</option><option>Jász-Nagykun-Szolnok</option><option>Komárom-Esztergom</option><option>Nógrád</option><option>Pest</option><option>Somogy</option><option>Szabolcs-Szatmár-Bereg</option><option>Tolna</option><option>Vas</option><option>Veszprém</option><option>Zala</option></select><div class="error-message">Válassz megyét!</div></div>
                <div class="form-group"><label for="varos">Város <span class="required">*</span></label><input type="text" id="varos" name="varos" required placeholder="Pl.: Szolnok"><div class="error-message">Add meg a várost!</div></div>
            </div>
            <div class="form-group"><label for="iranyitoszam">Irányítószám</label><input type="text" id="iranyitoszam" name="iranyitoszam" maxlength="4" placeholder="Pl.: 5000"></div>
            
            <h4 style="margin-top:16px;margin-bottom:12px;">Kapcsolattartás</h4>
            <div class="row">
                <div class="form-group"><label for="eladoNev">Megjelenő név <span class="required">*</span></label><input type="text" id="eladoNev" name="eladoNev" required placeholder="Pl.: Kovács Anna" value="<?= htmlspecialchars($_SESSION['user_nev'] ?? '') ?>"><div class="error-message">Add meg a nevet!</div></div>
                <div class="form-group"><label for="telefon">Telefon <span class="required">*</span></label><input type="tel" id="telefon" name="telefon" required placeholder="+36 30 123 4567"><div class="error-message">Adj meg telefonszámot!</div></div>
            </div>
            <div class="form-group"><label for="email">E-mail <span class="required">*</span></label><input type="email" id="email" name="email" required placeholder="pelda@email.hu" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>"><div class="error-message">Adj meg e-mail címet!</div></div>
        </div>

        <!-- Dinamikus kategória szekciók (röviden) -->
        <div id="sec-allas" class="dynamic-section card" style="margin-bottom:20px;"><h3>Állás részletei</h3><div class="row"><div class="form-group"><label>Munkakör típusa</label><select name="allas_munkakorTipus"><option>Alkalmazotti</option><option>Fizikai</option><option>Részmunkaidő</option><option>Távmunka</option><option>Külföldi</option><option>Vállalkozói</option></select></div><div class="form-group"><label>Foglalkoztatás jellege</label><select name="allas_foglalkoztatas"><option>Teljes munkaidő</option><option>Részmunkaidő</option><option>Diákmunka</option><option>Alkalmi</option></select></div></div><div class="row"><div class="form-group"><label>Fizetés alsó határ</label><input type="number" name="allas_fizetesAlso" placeholder="Ft/hó"></div><div class="form-group"><label>Fizetés felső határ</label><input type="number" name="allas_fizetesFelso" placeholder="Ft/hó"></div></div><div class="form-group"><label>Munkakör leírása</label><textarea name="allas_feladatok"></textarea></div></div>

        <div id="sec-ingatlan" class="dynamic-section card" style="margin-bottom:20px;"><h3>Ingatlan részletei</h3><div class="row"><div class="form-group"><label>Ingatlan típusa</label><select name="ingatlan_tipus"><option>Lakás</option><option>Családi ház</option><option>Telek</option><option>Üzlethelyiség</option></select></div><div class="form-group"><label>Hirdetés típusa</label><select name="ingatlan_hirdetesTipus" id="ingatlan_hirdetesTipus"><option>Eladó</option><option>Kiadó</option></select></div></div><div class="row"><div class="form-group"><label>Méret (m²)</label><input type="number" name="ingatlan_meret"></div><div class="form-group"><label>Szobák száma</label><input type="number" name="ingatlan_szobak"></div></div><div class="row conditional-row" id="ingatlan_berletiMezok"><div class="form-group"><label>Rezsiköltség</label><input type="number" name="ingatlan_rezsi"></div><div class="form-group"><label>Kaució</label><input type="text" name="ingatlan_kaucio"></div></div></div>

        <div id="sec-jarmu" class="dynamic-section card" style="margin-bottom:20px;"><h3>Jármű részletei</h3><div class="row"><div class="form-group"><label>Típus</label><select name="jarmu_tipus" id="jarmu_tipus"><option>Személyautó</option><option>Motor</option><option>Haszongépjármű</option></select></div><div class="form-group"><label>Márka</label><input type="text" name="jarmu_marka"></div></div><div class="row"><div class="form-group"><label>Modell</label><input type="text" name="jarmu_modell"></div><div class="form-group"><label>Évjárat</label><input type="number" name="jarmu_evjarat"></div></div><div class="row"><div class="form-group"><label>Kilométer</label><input type="number" name="jarmu_km"></div><div class="form-group"><label>Állapot</label><select name="jarmu_allapot"><option>Kitűnő</option><option>Jó</option><option>Közepes</option></select></div></div></div>

        <div id="sec-muszaki" class="dynamic-section card" style="margin-bottom:20px;"><h3>Műszaki cikk</h3><div class="row"><div class="form-group"><label>Típus</label><select name="muszaki_tipus"><option>Mobiltelefon</option><option>Laptop</option><option>TV</option><option>Háztartási gép</option></select></div><div class="form-group"><label>Márka</label><input type="text" name="muszaki_marka"></div></div><div class="form-group"><label>Modell</label><input type="text" name="muszaki_modell"></div></div>

        <div id="sec-haztartas" class="dynamic-section card" style="margin-bottom:20px;"><h3>Háztartás</h3><div class="form-group"><label>Típus</label><select name="haztartas_tipus"><option>Bútor</option><option>Konyhai eszköz</option><option>Szerszám</option><option>Növény</option></select></div><div class="form-group"><label>Állapot</label><select name="haztartas_allapot"><option>Új</option><option>Jó</option><option>Közepes</option></select></div></div>

        <div id="sec-szolgaltatas" class="dynamic-section card" style="margin-bottom:20px;"><h3>Szolgáltatás</h3><div class="form-group"><label>Típus</label><select name="szolg_tipus"><option>Lakásfelújítás</option><option>Oktatás</option><option>Szépségápolás</option><option>Informatika</option></select></div></div>

        <div id="sec-hobbi" class="dynamic-section card" style="margin-bottom:20px;"><h3>Hobbi, sport</h3><div class="form-group"><label>Típus</label><select name="hobbi_tipus"><option>Sportfelszerelés</option><option>Könyv</option><option>Társasjáték</option><option>Hangszer</option></select></div></div>

        <div id="sec-ruhazat" class="dynamic-section card" style="margin-bottom:20px;"><h3>Ruházat</h3><div class="row"><div class="form-group"><label>Típus</label><select name="ruhazat_tipus"><option>Női</option><option>Férfi</option><option>Gyerek</option></select></div><div class="form-group"><label>Méret</label><select name="ruhazat_meret"><option>XS</option><option>S</option><option>M</option><option>L</option><option>XL</option></select></div></div></div>

        <div id="sec-allatok" class="dynamic-section card" style="margin-bottom:20px;"><h3>Állatok</h3><div class="row"><div class="form-group"><label>Típus</label><select name="allat_tipus"><option>Kutya</option><option>Macska</option><option>Egyéb</option></select></div><div class="form-group"><label>Fajta</label><input type="text" name="allat_fajta"></div></div></div>

        <div id="sec-egyeb" class="dynamic-section card" style="margin-bottom:20px;"><h3>Egyéb</h3><div class="form-group"><label>Típus</label><select name="egyeb_tipus"><option>Ingyen elvihető</option><option>Csere</option><option>Talált tárgy</option></select></div></div>

        <div class="card" style="margin-bottom:20px;">
            <h3>Kiegészítő beállítások</h3>
            <div class="row">
                <div class="form-group"><label>Érvényesség</label><select name="ervenyesseg"><option value="7">7 nap</option><option value="14" selected>14 nap</option><option value="30">30 nap</option><option value="60">60 nap</option></select></div>
                <div class="form-group"><label>Kiemelés</label><div class="radio-group"><label><input type="radio" name="csomag" value="alap" checked> Alap</label><label><input type="radio" name="csomag" value="normal"> Normál (+500 Ft)</label><label><input type="radio" name="csomag" value="premium"> Prémium (+1500 Ft)</label></div></div>
            </div>
            <div class="form-group"><label>Címkék</label><input type="text" name="cimkek" placeholder="Pl.: sürgős, költözés (vesszővel tagolva)"></div>
        </div>

        <div class="form-group"><label><input type="checkbox" id="aszfElfogadas" required> Elfogadom a <a href="/aszf">felhasználási feltételeket</a> <span class="required">*</span></label><div class="error-message">El kell fogadnod!</div></div>
        <div style="text-align:center;"><button type="submit" class="btn btn-primary btn-lg">Hirdetés feladása</button></div>
    </form>
    <div class="success-message" id="successMessage"><h2>✅ Hirdetés sikeresen feladva!</h2><p>Hirdetésed jóváhagyás után megjelenik a portálon.</p><a href="/" class="btn btn-primary">Vissza a főoldalra</a></div>
</div>

<script>
const alkategoriak = {
    allas: ['IT, programozás','Értékesítés','Adminisztráció','Vendéglátás','Pénzügy','Marketing','Egészségügy','Oktatás','Fizikai munka','Részmunkaidő','Távmunka','Külföldi munka','Vállalkozói'],
    ingatlan: ['Eladó lakás','Eladó ház','Eladó telek','Kiadó lakás','Kiadó ház','Kiadó szoba','Rövid távú kiadás'],
    jarmu: ['Személyautó','Motor','Haszongépjármű','Alkatrész','Mezőgazdasági gép'],
    muszaki: ['Mobiltelefon','Laptop','TV','Háztartási gép','Fényképezőgép','Okoseszköz'],
    haztartas: ['Bútor','Konyhai eszköz','Lakástextília','Szerszám','Növény'],
    szolgaltatas: ['Lakásfelújítás','Takarítás','Oktatás','Szépségápolás','Informatika'],
    hobbi: ['Sportfelszerelés','Könyv','Társasjáték','Hangszer','Gyűjtemény','Jegy'],
    ruhazat: ['Női ruha','Férfi ruha','Gyerekruha','Táska','Ékszer'],
    allatok: ['Kutya','Macska','Hobbiállat','Ló'],
    egyeb: ['Ingyen elvihető','Csere','Talált tárgy','Elveszett tárgy']
};

document.getElementById('fokategoria').addEventListener('change', function() {
    const fok = this.value;
    document.querySelectorAll('.dynamic-section').forEach(s => s.classList.remove('active'));
    const sec = document.getElementById('sec-' + fok);
    if (sec) sec.classList.add('active');
    
    const alkSel = document.getElementById('alkategoria');
    alkSel.innerHTML = '<option value="">-- Válassz --</option>';
    if (fok && alkategoriak[fok]) {
        alkSel.disabled = false;
        alkategoriak[fok].forEach(a => { const o = document.createElement('option'); o.textContent = a; alkSel.appendChild(o); });
    } else { alkSel.disabled = true; }
    
    if (fok === 'ingatlan') updateIngatlanMezok();
});

document.getElementById('arTipus').addEventListener('change', function() {
    const ar = document.getElementById('ar');
    if (this.value === 'ingyen') { ar.value = 0; ar.disabled = true; }
    else if (this.value === 'megbeszeles') { ar.value = ''; ar.disabled = true; }
    else { ar.disabled = false; ar.value = ''; }
});

document.getElementById('ingatlan_hirdetesTipus')?.addEventListener('change', updateIngatlanMezok);
function updateIngatlanMezok() {
    const tipus = document.getElementById('ingatlan_hirdetesTipus')?.value;
    const berleti = document.getElementById('ingatlan_berletiMezok');
    if (berleti) berleti.style.display = tipus === 'Kiadó' ? 'flex' : 'none';
}

// Képfeltöltés
const fileDrop = document.getElementById('fileDropArea');
const fileInput = document.getElementById('kepek');
const previewCont = document.getElementById('previewContainer');
let uploadedFiles = [];

fileDrop.addEventListener('click', e => { if (e.target !== fileInput) fileInput.click(); });
fileInput.addEventListener('change', () => handleFiles(fileInput.files));
fileDrop.addEventListener('dragover', e => { e.preventDefault(); fileDrop.style.borderColor = 'var(--primary)'; });
fileDrop.addEventListener('dragleave', () => fileDrop.style.borderColor = 'var(--border)');
fileDrop.addEventListener('drop', e => { e.preventDefault(); fileDrop.style.borderColor = 'var(--border)'; handleFiles(e.dataTransfer.files); });

function handleFiles(files) {
    for (let f of files) {
        if (uploadedFiles.length >= 10) { alert('Max. 10 kép!'); break; }
        if (f.size > 5*1024*1024) { alert('Max. 5 MB!'); continue; }
        uploadedFiles.push(f);
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div'); div.className = 'preview-item'; div.style.backgroundImage = 'url(' + e.target.result + ')';
            const btn = document.createElement('button'); btn.className = 'remove-img'; btn.innerHTML = '✕'; btn.type = 'button';
            btn.onclick = () => { const i = Array.from(previewCont.children).indexOf(div); if(i>-1) uploadedFiles.splice(i,1); div.remove(); };
            div.appendChild(btn); previewCont.appendChild(div);
        };
        reader.readAsDataURL(f);
    }
}

// Validáció
document.getElementById('hirdetesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;
    document.querySelectorAll('.form-group.error').forEach(g => g.classList.remove('error'));
    
    const required = ['fokategoria','alkategoria','cim','leiras','megye','varos','eladoNev','telefon','email'];
    required.forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.value.trim()) { el.closest('.form-group').classList.add('error'); valid = false; }
    });
    if (document.getElementById('leiras').value.length < 20) { document.getElementById('leiras').closest('.form-group').classList.add('error'); valid = false; }
    if (!document.getElementById('aszfElfogadas').checked) { document.getElementById('aszfElfogadas').closest('.form-group').classList.add('error'); valid = false; }
    
    if (!valid) { document.querySelector('.form-group.error')?.scrollIntoView({behavior:'smooth',block:'center'}); return; }
    
    const formData = new FormData(this);
    uploadedFiles.forEach(f => formData.append('kepek[]', f));
    
    fetch('/submit_hirdetes.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) { document.getElementById('hirdetesForm').style.display = 'none'; document.getElementById('successMessage').classList.add('active'); window.scrollTo(0,0); }
            else alert(d.message);
        })
        .catch(() => alert('Hiba!'));
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>