<?php
$pageTitle = 'Hirdetés feladása - Ország Közepe';
require_once __DIR__ . '/functions.php';
requireLogin();

$extraHead = '
<style>
    .dynamic-section { display: none; }
    .dynamic-section.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .conditional-row { display: none; }
    .conditional-row.show { display: flex; }
    .file-upload-area {
        border: 2px dashed var(--border);
        border-radius: var(--radius);
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        background: #f8fafc;
        transition: all 0.2s ease;
    }
    .file-upload-area:hover { border-color: var(--primary); background: var(--primary-light); }
    .file-upload-area input[type="file"] { display: none; }
    .preview-container { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 16px; }
    .preview-item {
        width: 80px; height: 80px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background-size: cover;
        background-position: center;
        position: relative;
    }
    .preview-item .remove-img {
        position: absolute; top: -6px; right: -6px;
        width: 20px; height: 20px;
        background: var(--danger);
        color: white;
        border-radius: 50%;
        border: none;
        font-size: 10px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .row { display: flex; gap: 20px; flex-wrap: wrap; }
    .row .form-group { flex: 1; min-width: 240px; }
    @media(max-width: 600px) { .row { flex-direction: column; } .row .form-group { min-width: 100%; } }
    .checkbox-group, .radio-group { display: flex; flex-wrap: wrap; gap: 12px 24px; padding-top: 6px; }
    .checkbox-group label, .radio-group label { font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .error-message { color: var(--danger); font-size: 0.8rem; margin-top: 4px; display: none; }
    .form-group.error input, .form-group.error select, .form-group.error textarea { border-color: var(--danger); }
    .form-group.error .error-message { display: block; }
    .success-message { display: none; text-align: center; padding: 60px 20px; }
    .success-message.active { display: block; }
    .success-message h2 { color: var(--success); font-family: "Outfit", sans-serif; font-size: 2rem; margin-bottom: 16px; }
</style>';

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 800px; padding: 20px 0 60px;">
    <div style="text-align:center; margin-bottom: 40px; border-bottom: 2px solid var(--border); padding-bottom: 20px;">
        <h1 style="font-family:'Outfit',sans-serif; font-size: 2.2rem; color: var(--primary); margin-bottom: 8px;">Hirdetés feladása</h1>
        <p style="color: var(--text-light); font-size: 1.05rem;">Töltsd ki az alábbi űrlapot az ajánlatod közzétételéhez.</p>
    </div>

    <form id="hirdetesForm" action="<?= BASE_URL ?>/submit_hirdetes.php" method="POST" enctype="multipart/form-data" novalidate>
        <!-- 1. Alapadatok -->
        <div class="card" style="margin-bottom: 24px;">
            <h3 style="margin-bottom: 20px; font-family:'Outfit',sans-serif; color: var(--primary); border-bottom: 1px solid var(--border); padding-bottom: 8px;">1. Alapadatok</h3>
            
            <div class="row">
                <div class="form-group">
                    <label for="fokategoria">Főkategória <span class="required">*</span></label>
                    <select id="fokategoria" name="fokategoria" required>
                        <option value="">-- Válassz --</option>
                        <option value="allas">Állás</option>
                        <option value="ingatlan">Ingatlan</option>
                        <option value="jarmu">Jármű</option>
                        <option value="muszaki">Műszaki cikk</option>
                        <option value="haztartas">Háztartás, bútor</option>
                        <option value="szolgaltatas">Szolgáltatás</option>
                        <option value="hobbi">Hobbi, sport</option>
                        <option value="ruhazat">Ruházat, divat</option>
                        <option value="allatok">Állatok</option>
                        <option value="egyeb">Egyéb</option>
                    </select>
                    <div class="error-message">A főkategória kiválasztása kötelező!</div>
                </div>
                <div class="form-group">
                    <label for="alkategoria">Alkategória <span class="required">*</span></label>
                    <select id="alkategoria" name="alkategoria" required disabled>
                        <option value="">-- Először válassz főkategóriát --</option>
                    </select>
                    <div class="error-message">Az alkategória kiválasztása kötelező!</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="cim">Hirdetés címe <span class="required">*</span></label>
                <input type="text" id="cim" name="cim" maxlength="80" required placeholder="Pl.: Eladó megkímélt Opel Corsa C">
                <div class="hint">Maximum 80 karakter. Törekedj a lényegre törő megfogalmazásra!</div>
                <div class="error-message">A cím megadása kötelező!</div>
            </div>
            
            <div class="form-group">
                <label for="leiras">Leírás <span class="required">*</span></label>
                <textarea id="leiras" name="leiras" minlength="20" required placeholder="Részletes leírás a termékről vagy szolgáltatásról..."></textarea>
                <div class="hint">Legalább 20 karakter hosszúnak kell lennie.</div>
                <div class="error-message">A leírás megadása kötelező (min. 20 karakter)!</div>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="ar">Ár <span class="required">*</span></label>
                    <input type="number" id="ar" name="ar" min="0" required placeholder="Pl.: 45000">
                    <div class="error-message">Kérjük, adj meg egy érvényes árat!</div>
                </div>
                <div class="form-group">
                    <label for="arTipus">Ár típusa</label>
                    <select id="arTipus" name="arTipus">
                        <option value="fix">Fix ár</option>
                        <option value="megbeszeles">Megbeszélés szerint</option>
                        <option value="ingyen">Ingyen elvihető</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Képek feltöltése (maximum 10 darab)</label>
                <div class="file-upload-area" id="fileDropArea">
                    <span style="font-size:2.5rem; display:block; margin-bottom:8px;">📸</span>
                    <strong>Húzd ide a fotókat</strong> vagy kattints a tallózáshoz
                    <p class="hint" style="margin-top: 6px;">Megengedett fájlok: JPG, PNG, WEBP. Maximális méret: 5 MB / kép.</p>
                    <input type="file" id="kepek" name="kepek[]" accept="image/jpeg,image/png,image/webp" multiple>
                </div>
                <div class="preview-container" id="previewContainer"></div>
            </div>
        </div>

        <!-- 2. Helyszín és Kapcsolat -->
        <div class="card" style="margin-bottom: 24px;">
            <h3 style="margin-bottom: 20px; font-family:'Outfit',sans-serif; color: var(--primary); border-bottom: 1px solid var(--border); padding-bottom: 8px;">2. Kapcsolat és Helyszín</h3>
            
            <div class="row">
                <div class="form-group">
                    <label for="megye">Megye <span class="required">*</span></label>
                    <select id="megye" name="megye" required>
                        <option value="">-- Válassz megyét --</option>
                        <option>Bács-Kiskun</option><option>Baranya</option><option>Békés</option>
                        <option>Borsod-Abaúj-Zemplén</option><option>Budapest</option><option>Csongrád-Csanád</option>
                        <option>Fejér</option><option>Győr-Moson-Sopron</option><option>Hajdú-Bihar</option>
                        <option>Heves</option><option>Jász-Nagykun-Szolnok</option><option>Komárom-Esztergom</option>
                        <option>Nógrád</option><option>Pest</option><option>Somogy</option>
                        <option>Szabolcs-Szatmár-Bereg</option><option>Tolna</option><option>Vas</option>
                        <option>Veszprém</option><option>Zala</option>
                    </select>
                    <div class="error-message">A megye megadása kötelező!</div>
                </div>
                <div class="form-group">
                    <label for="varos">Város <span class="required">*</span></label>
                    <input type="text" id="varos" name="varos" required placeholder="Pl.: Szolnok">
                    <div class="error-message">A város megadása kötelező!</div>
                </div>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="iranyitoszam">Irányítószám</label>
                    <input type="text" id="iranyitoszam" name="iranyitoszam" maxlength="4" placeholder="Pl.: 5000">
                </div>
                <div class="form-group">
                    <label for="eladoNev">Hirdető neve <span class="required">*</span></label>
                    <input type="text" id="eladoNev" name="eladoNev" required value="<?= htmlspecialchars($_SESSION['user_nev'] ?? '') ?>" placeholder="Kovács Anna">
                    <div class="error-message">A név megadása kötelező!</div>
                </div>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label for="telefon">Telefonszám <span class="required">*</span></label>
                    <input type="tel" id="telefon" name="telefon" required placeholder="+36 30 123 4567">
                    <div class="error-message">A telefonszám megadása kötelező!</div>
                </div>
                <div class="form-group">
                    <label for="email">E-mail cím <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" placeholder="anna@email.hu">
                    <div class="error-message">Kérjük, adj meg egy érvényes e-mail címet!</div>
                </div>
            </div>
        </div>

        <!-- 3. Dinamikus Szekciók -->
        <!-- Állás -->
        <div id="sec-allas" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Állás részletei</h3>
            <div class="row">
                <div class="form-group">
                    <label>Munkakör típusa</label>
                    <select name="allas_munkakorTipus">
                        <option>Alkalmazotti</option><option>Fizikai munka</option><option>Adminisztráció</option><option>IT, Fejlesztés</option><option>Vendéglátás</option><option>Diákmunka</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Foglalkoztatás jellege</label>
                    <select name="allas_foglalkoztatas">
                        <option>Teljes munkaidő</option><option>Részmunkaidő</option><option>Projektmunka</option><option>Távmunka</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label>Fizetés (Alsó határ, Ft/hó)</label>
                    <input type="number" name="allas_fizetesAlso" placeholder="Pl.: 350000">
                </div>
                <div class="form-group">
                    <label>Fizetés (Felső határ, Ft/hó)</label>
                    <input type="number" name="allas_fizetesFelso" placeholder="Pl.: 600000">
                </div>
            </div>
        </div>

        <!-- Ingatlan -->
        <div id="sec-ingatlan" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Ingatlan részletei</h3>
            <div class="row">
                <div class="form-group">
                    <label>Ingatlan típusa</label>
                    <select name="ingatlan_tipus">
                        <option>Lakás</option><option>Családi ház</option><option>Telek</option><option>Iroda</option><option>Garázs</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hirdetés típusa</label>
                    <select name="ingatlan_hirdetesTipus" id="ingatlan_hirdetesTipus">
                        <option>Eladó</option><option>Kiadó</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label>Alapterület (m²)</label>
                    <input type="number" name="ingatlan_meret" placeholder="Pl.: 65">
                </div>
                <div class="form-group">
                    <label>Szobák száma</label>
                    <input type="number" name="ingatlan_szobak" placeholder="Pl.: 3">
                </div>
            </div>
            <div class="row conditional-row" id="ingatlan_berletiMezok">
                <div class="form-group">
                    <label>Rezsiköltség (Ft/hó)</label>
                    <input type="number" name="ingatlan_rezsi" placeholder="Pl.: 35000">
                </div>
                <div class="form-group">
                    <label>Kaució (Hónapokban vagy Összeg)</label>
                    <input type="text" name="ingatlan_kaucio" placeholder="Pl.: 2 hónap">
                </div>
            </div>
        </div>

        <!-- Jármű -->
        <div id="sec-jarmu" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Jármű adatai</h3>
            <div class="row">
                <div class="form-group">
                    <label>Jármű kategória</label>
                    <select name="jarmu_tipus">
                        <option>Személyautó</option><option>Motorkerékpár</option><option>Kishaszonjármű</option><option>Lakókocsi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Gyártmány / Márka</label>
                    <input type="text" name="jarmu_marka" placeholder="Pl.: Opel">
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label>Modell</label>
                    <input type="text" name="jarmu_modell" placeholder="Pl.: Astra G">
                </div>
                <div class="form-group">
                    <label>Évjárat</label>
                    <input type="number" name="jarmu_evjarat" min="1950" max="<?= date('Y')+1 ?>" placeholder="Pl.: 2004">
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label>Kilométeróra állása (km)</label>
                    <input type="number" name="jarmu_km" placeholder="Pl.: 245000">
                </div>
                <div class="form-group">
                    <label>Állapot</label>
                    <select name="jarmu_allapot">
                        <option>Normál</option><option>Kitűnő</option><option>Sérült</option><option>Hibás, üzemképtelen</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Egyéb dinamikus kártyák (minimális mezőkkel a dizájnhoz) -->
        <div id="sec-muszaki" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Műszaki cikk adatai</h3>
            <div class="row">
                <div class="form-group"><label>Termék típusa</label><select name="muszaki_tipus"><option>Mobiltelefon</option><option>Laptop, PC</option><option>Konzol, Játék</option><option>TV, Audio</option></select></div>
                <div class="form-group"><label>Márka</label><input type="text" name="muszaki_marka" placeholder="Pl.: Samsung"></div>
            </div>
        </div>

        <div id="sec-haztartas" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Háztartási cikk adatai</h3>
            <div class="row">
                <div class="form-group"><label>Bútor/Tárgy típusa</label><select name="haztartas_tipus"><option>Konyhabútor</option><option>Szekrény</option><option>Dekoráció</option><option>Kerti bútor</option></select></div>
                <div class="form-group"><label>Állapot</label><select name="haztartas_allapot"><option>Új, csomagolt</option><option>Újszerű</option><option>Használt, jó állapotú</option></select></div>
            </div>
        </div>

        <div id="sec-szolgaltatas" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Szolgáltatás adatai</h3>
            <div class="form-group"><label>Kínált tevékenység</label><select name="szolg_tipus"><option>Festés, mázolás</option><option>Költöztetés</option><option>Magánóra, oktatás</option><option>Számítógép javítás</option></select></div>
        </div>

        <div id="sec-hobbi" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Hobbi, sport adatai</h3>
            <div class="form-group"><label>Tevékenység kategória</label><select name="hobbi_tipus"><option>Kerékpár</option><option>Horgászat</option><option>Könyv</option><option>Hangszer</option></select></div>
        </div>

        <div id="sec-ruhazat" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Ruházat adatai</h3>
            <div class="row">
                <div class="form-group"><label>Méret</label><select name="ruhazat_meret"><option>S</option><option>M</option><option>L</option><option>XL</option><option>XXL</option></select></div>
                <div class="form-group"><label>Állapot</label><select name="ruhazat_allapot"><option>Új, címkés</option><option>Újszerű</option><option>Használt</option></select></div>
            </div>
        </div>

        <div id="sec-allatok" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Állatok adatai</h3>
            <div class="row">
                <div class="form-group"><label>Fajta</label><input type="text" name="allat_fajta" placeholder="Pl.: Labrador retriever"></div>
                <div class="form-group"><label>Kor</label><input type="text" name="allat_kor" placeholder="Pl.: 3 hónapos"></div>
            </div>
        </div>

        <div id="sec-egyeb" class="dynamic-section card" style="margin-bottom:20px;">
            <h3 style="margin-bottom: 16px; font-family:'Outfit',sans-serif; color: var(--primary);">Egyéb információk</h3>
            <div class="form-group"><label>Típus</label><select name="egyeb_tipus"><option>Ajándékba elvihető</option><option>Csere ajánlat</option><option>Keresek valamit</option></select></div>
        </div>

        <!-- 4. Csomagválasztás -->
        <div class="card" style="margin-bottom: 24px;">
            <h3 style="margin-bottom: 20px; font-family:'Outfit',sans-serif; color: var(--primary); border-bottom: 1px solid var(--border); padding-bottom: 8px;">3. Kiemelési lehetőségek</h3>
            
            <div class="row">
                <div class="form-group">
                    <label>Megjelenési időtartam</label>
                    <select name="ervenyesseg">
                        <option value="7">7 nap</option>
                        <option value="14" selected>14 nap</option>
                        <option value="30">30 nap</option>
                        <option value="60">60 nap</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hirdetési csomag</label>
                    <div class="radio-group">
                        <label><input type="radio" name="csomag" value="alap" checked> Alap (Ingyenes)</label>
                        <label><input type="radio" name="csomag" value="normal"> Normál kiemelés (+500 Ft)</label>
                        <label><input type="radio" name="csomag" value="premium"> Prémium kiemelés (+1500 Ft)</label>
                    </div>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 14px;">
                <label for="cimkek">Keresőcímkék</label>
                <input type="text" id="cimkek" name="cimkek" placeholder="Pl.: sürgős, eladó, újszerű (vesszővel tagolva)">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label><input type="checkbox" id="aszfElfogadas" required> Elfogadom a platform <a href="<?= BASE_URL ?>/aszf" target="_blank" style="font-weight: 700;">Általános Szerződési Feltételeit</a> és az adatvédelmi irányelveket. *</label>
            <div class="error-message">Az ÁSZF elfogadása kötelező!</div>
        </div>
        
        <div style="text-align:center;">
            <button type="submit" class="btn btn-primary btn-lg" style="padding: 16px 50px;">🚀 Hirdetés beküldése jóváhagyásra</button>
        </div>
    </form>
    
    <div class="success-message card" id="successMessage">
        <h2>✅ Hirdetés sikeresen beküldve!</h2>
        <p style="color:var(--text-light); margin-bottom: 24px; font-size:1.1rem;">Hirdetésed rögzítésre került. Munkatársunk jóváhagyása után azonnal megjelenik a portálon.</p>
        <a href="<?= BASE_URL ?>/" class="btn btn-primary btn-lg">Vissza a főoldalra</a>
    </div>
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
    alkSel.innerHTML = '<option value="">-- Válassz alkategóriát --</option>';
    if (fok && alkategoriak[fok]) {
        alkSel.disabled = false;
        alkategoriak[fok].forEach(a => { 
            const o = document.createElement('option'); 
            o.textContent = a; 
            alkSel.appendChild(o); 
        });
    } else { 
        alkSel.disabled = true; 
    }
    
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

// Képfeltöltés és előnézet kezelése
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
        if (uploadedFiles.length >= 10) { alert('Legfeljebb 10 kép tölthető fel!'); break; }
        if (f.size > 5*1024*1024) { alert('A kép mérete nem haladhatja meg az 5 MB-ot!'); continue; }
        uploadedFiles.push(f);
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div'); 
            div.className = 'preview-item'; 
            div.style.backgroundImage = 'url(' + e.target.result + ')';
            
            const btn = document.createElement('button'); 
            btn.className = 'remove-img'; 
            btn.innerHTML = '✕'; 
            btn.type = 'button';
            btn.onclick = () => { 
                const i = Array.from(previewCont.children).indexOf(div); 
                if(i>-1) uploadedFiles.splice(i,1); 
                div.remove(); 
            };
            
            div.appendChild(btn); 
            previewCont.appendChild(div);
        };
        reader.readAsDataURL(f);
    }
}

// Űrlap beküldése AJAX-szal
document.getElementById('hirdetesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;
    document.querySelectorAll('.form-group.error').forEach(g => g.classList.remove('error'));
    
    const required = ['fokategoria','alkategoria','cim','leiras','megye','varos','eladoNev','telefon','email'];
    required.forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.value.trim()) { 
            el.closest('.form-group').classList.add('error'); 
            valid = false; 
        }
    });
    
    if (document.getElementById('leiras').value.length < 20) { 
        document.getElementById('leiras').closest('.form-group').classList.add('error'); 
        valid = false; 
    }
    
    if (!document.getElementById('aszfElfogadas').checked) { 
        document.getElementById('aszfElfogadas').closest('.form-group').classList.add('error'); 
        valid = false; 
    }
    
    if (!valid) { 
        document.querySelector('.form-group.error')?.scrollIntoView({behavior:'smooth', block:'center'}); 
        return; 
    }
    
    const formData = new FormData(this);
    uploadedFiles.forEach(f => formData.append('kepek[]', f));
    
    fetch('<?= BASE_URL ?>/submit_hirdetes.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) { 
                document.getElementById('hirdetesForm').style.display = 'none'; 
                document.getElementById('successMessage').classList.add('active'); 
                window.scrollTo(0,0); 
            } else { 
                alert('Hiba történt: ' + d.message); 
            }
        })
        .catch(() => alert('Hálózati hiba történt a hirdetés feladása során!'));
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
