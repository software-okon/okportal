<?php
/**
 * submit_hirdetes.php
 * A hirdetésfeladási űrlap adatainak fogadása, validálása és mentése
 */

require_once __DIR__ . '/functions.php';

// Csak POST kéréseket fogadunk
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Csak POST metódus engedélyezett!', [], 405);
}

// CSRF token ellenőrzése (az űrlapnak küldenie kell a csrf_token mezőt)
// if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
//     jsonResponse(false, 'Érvénytelen CSRF token!', [], 403);
// }

// ========================
// 1. KÖZÖS MEZŐK FELDOLGOZÁSA
// ========================

$errors = [];

// Főkategória
$fokategoria = cleanInput($_POST['fokategoria'] ?? '');
if (!isValidFokategoria($fokategoria)) {
    $errors[] = 'Érvénytelen főkategória!';
}

// Alkategória
$alkategoria = cleanInput($_POST['alkategoria'] ?? '');
if (empty($alkategoria)) {
    $errors[] = 'Az alkategória megadása kötelező!';
}

// Hirdetés címe
$cim = cleanInput($_POST['cim'] ?? '');
if (mb_strlen($cim) < 3) {
    $errors[] = 'A hirdetés címe túl rövid (minimum 3 karakter)!';
}
if (mb_strlen($cim) > 80) {
    $errors[] = 'A hirdetés címe maximum 80 karakter lehet!';
}

// Hirdetés leírása
$leiras = trim($_POST['leiras'] ?? '');
if (mb_strlen($leiras) < 20) {
    $errors[] = 'A leírásnak legalább 20 karakterből kell állnia!';
}

// Ár
$arTipus = $_POST['arTipus'] ?? 'fix';
$ar = null;
if ($arTipus === 'fix') {
    $ar = filter_var($_POST['ar'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($ar === false || $ar === null) {
        $errors[] = 'Érvénytelen ár!';
    }
} elseif ($arTipus === 'ingyen') {
    $ar = 0;
} else {
    $ar = null; // Megbeszélés szerint
}

// Helyszín adatok
$megye = cleanInput($_POST['megye'] ?? '');
if (empty($megye)) {
    $errors[] = 'A megye megadása kötelező!';
}

$varos = cleanInput($_POST['varos'] ?? '');
if (empty($varos)) {
    $errors[] = 'A város megadása kötelező!';
}

$iranyitoszam = cleanInput($_POST['iranyitoszam'] ?? '');
if (!empty($iranyitoszam) && !preg_match('/^\d{4}$/', $iranyitoszam)) {
    $errors[] = 'Az irányítószámnak 4 számjegyből kell állnia!';
}

// Kapcsolattartási adatok
$eladoNev = cleanInput($_POST['eladoNev'] ?? '');
if (empty($eladoNev)) {
    $errors[] = 'A megjelenő név megadása kötelező!';
}

$telefon = cleanInput($_POST['telefon'] ?? '');
$phoneError = validatePhone($telefon);
if ($phoneError) {
    $errors[] = $phoneError;
}

$email = cleanInput($_POST['email'] ?? '');
$emailError = validateEmail($email);
if ($emailError) {
    $errors[] = $emailError;
}

// Kiegészítő mezők
$ervenyesseg = filter_var($_POST['ervenyesseg'] ?? 14, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 60]]);
if ($ervenyesseg === false) {
    $ervenyesseg = 14;
}

$kiemeles = in_array($_POST['csomag'] ?? 'alap', ['alap', 'normal', 'premium']) ? $_POST['csomag'] : 'alap';
$cimkek = cleanInput($_POST['cimkek'] ?? '');
$videoURL = filter_var($_POST['videoURL'] ?? '', FILTER_VALIDATE_URL) ? cleanInput($_POST['videoURL']) : null;
$alku = ($_POST['alku'] ?? 'igen') === 'igen' ? 1 : 0;

// Ha vannak validációs hibák, visszaküldjük őket
if (!empty($errors)) {
    jsonResponse(false, 'Validációs hiba!', ['errors' => $errors], 422);
}

// ========================
// 2. TRANZAKCIÓ INDÍTÁSA
// ========================
$pdo = getDB();
$pdo->beginTransaction();

try {
    // ========================
    // 3. HIRDETÉS MENTÉSE (közös mezők)
    // ========================
    $lejarat = calculateExpiryDate($ervenyesseg);
    
    $sql = "INSERT INTO hirdetesek (
                felhasznalo_id, fokategoria, alkategoria, cim, leiras, ar, ar_tipus,
                megye, varos, iranyitoszam, elado_nev, telefon, email,
                ervenyesseg, kiemeles, cimkek, video_url, alku,
                statusz, lejarat
            ) VALUES (
                :felhasznalo_id, :fokategoria, :alkategoria, :cim, :leiras, :ar, :ar_tipus,
                :megye, :varos, :iranyitoszam, :elado_nev, :telefon, :email,
                :ervenyesseg, :kiemeles, :cimkek, :video_url, :alku,
                'fuggoben', :lejarat
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':felhasznalo_id'  => getCurrentUserId(),
        ':fokategoria'     => $fokategoria,
        ':alkategoria'     => $alkategoria,
        ':cim'             => $cim,
        ':leiras'          => $leiras,
        ':ar'              => $ar,
        ':ar_tipus'        => $arTipus,
        ':megye'           => $megye,
        ':varos'           => $varos,
        ':iranyitoszam'    => !empty($iranyitoszam) ? $iranyitoszam : null,
        ':elado_nev'       => $eladoNev,
        ':telefon'         => $telefon,
        ':email'           => $email,
        ':ervenyesseg'     => $ervenyesseg,
        ':kiemeles'        => $kiemeles,
        ':cimkek'          => !empty($cimkek) ? $cimkek : null,
        ':video_url'       => $videoURL,
        ':alku'            => $alku,
        ':lejarat'         => $lejarat
    ]);
    
    $hirdetesId = (int) $pdo->lastInsertId();

    // ========================
    // 4. KATEGÓRIA-SPECIFIKUS MEZŐK MENTÉSE
    // ========================
    switch ($fokategoria) {
        case 'allas':
            saveAllasData($pdo, $hirdetesId, $_POST);
            break;
        case 'ingatlan':
            saveIngatlanData($pdo, $hirdetesId, $_POST);
            break;
        case 'jarmu':
            saveJarmuData($pdo, $hirdetesId, $_POST);
            break;
        case 'muszaki':
            saveMuszakiData($pdo, $hirdetesId, $_POST);
            break;
        case 'haztartas':
            saveHaztartasData($pdo, $hirdetesId, $_POST);
            break;
        case 'szolgaltatas':
            saveSzolgaltatasData($pdo, $hirdetesId, $_POST);
            break;
        case 'hobbi':
            saveHobbiData($pdo, $hirdetesId, $_POST);
            break;
        case 'ruhazat':
            saveRuhazatData($pdo, $hirdetesId, $_POST);
            break;
        case 'allatok':
            saveAllatokData($pdo, $hirdetesId, $_POST);
            break;
        case 'egyeb':
            saveEgyebData($pdo, $hirdetesId, $_POST);
            break;
    }

    // ========================
    // 5. KÉPEK FELDOLGOZÁSA
    // ========================
    if (!empty($_FILES['kepek']) && is_array($_FILES['kepek']['tmp_name'])) {
        $fileCount = count($_FILES['kepek']['tmp_name']);
        $uploadedCount = 0;
        
        for ($i = 0; $i < $fileCount && $uploadedCount < MAX_IMAGES; $i++) {
            $file = [
                'name'     => $_FILES['kepek']['name'][$i],
                'type'     => $_FILES['kepek']['type'][$i],
                'tmp_name' => $_FILES['kepek']['tmp_name'][$i],
                'error'    => $_FILES['kepek']['error'][$i],
                'size'     => $_FILES['kepek']['size'][$i]
            ];
            
            if ($file['error'] !== UPLOAD_ERR_OK) continue;
            
            $uploadResult = uploadImage($file);
            if ($uploadResult) {
                $sqlKep = "INSERT INTO hirdetes_kepek (hirdetes_id, fajl_nev, eredeti_nev, sorrend) 
                           VALUES (:hirdetes_id, :fajl_nev, :eredeti_nev, :sorrend)";
                $stmtKep = $pdo->prepare($sqlKep);
                $stmtKep->execute([
                    ':hirdetes_id'  => $hirdetesId,
                    ':fajl_nev'     => $uploadResult['fajl_nev'],
                    ':eredeti_nev'  => $uploadResult['eredeti_nev'],
                    ':sorrend'      => $uploadedCount
                ]);
                $uploadedCount++;
            }
        }
    }

    // ========================
    // 6. TRANZAKCIÓ VÉGLEGESÍTÉSE
    // ========================
    $pdo->commit();
    
    $hirdetesUrl = generateHirdetesUrl($hirdetesId, $cim);
    
    jsonResponse(true, 'Hirdetés sikeresen feladva!', [
        'hirdetes_id'  => $hirdetesId,
        'hirdetes_url' => $hirdetesUrl,
        'lejarat'      => $lejarat
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Hirdetés mentési hiba: " . $e->getMessage());
    jsonResponse(false, 'Hiba történt a hirdetés mentése során. Kérlek, próbáld újra!', [], 500);
}


// ========================
// 7. KATEGÓRIA-SPECIFIKUS MENTŐ FÜGGVÉNYEK
// ========================

/**
 * Állás adatok mentése
 */
function saveAllasData(PDO $pdo, int $hirdetesId, array $post): void {
    $juttatasok = isset($post['allas_juttatasok']) && is_array($post['allas_juttatasok'])
        ? implode(', ', $post['allas_juttatasok']) : null;
    $nyelvek = isset($post['allas_nyelvek']) && is_array($post['allas_nyelvek'])
        ? implode(', ', $post['allas_nyelvek']) : null;
    $szamitogep = isset($post['allas_szamitogep']) && is_array($post['allas_szamitogep'])
        ? implode(', ', $post['allas_szamitogep']) : null;
    
    $sql = "INSERT INTO hirdetes_allas (
                hirdetes_id, munkakor_tipus, foglalkoztatas_jellege,
                fizetes_also, fizetes_felso, fizetes_tipus, juttatasok,
                vegzettseg, tapasztalat, munkakor_leiras, ceg_info,
                jelentkezesi_hatarido, nyelvek, szamitogep_ismeretek
            ) VALUES (
                :hirdetes_id, :munkakor_tipus, :foglalkoztatas_jellege,
                :fizetes_also, :fizetes_felso, :fizetes_tipus, :juttatasok,
                :vegzettseg, :tapasztalat, :munkakor_leiras, :ceg_info,
                :jelentkezesi_hatarido, :nyelvek, :szamitogep_ismeretek
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'              => $hirdetesId,
        ':munkakor_tipus'           => cleanInput($post['allas_munkakorTipus'] ?? ''),
        ':foglalkoztatas_jellege'   => cleanInput($post['allas_foglalkoztatas'] ?? ''),
        ':fizetes_also'             => !empty($post['allas_fizetesAlso']) ? (int)$post['allas_fizetesAlso'] : null,
        ':fizetes_felso'            => !empty($post['allas_fizetesFelso']) ? (int)$post['allas_fizetesFelso'] : null,
        ':fizetes_tipus'            => cleanInput($post['allas_fizetesTipus'] ?? ''),
        ':juttatasok'               => $juttatasok,
        ':vegzettseg'               => cleanInput($post['allas_vegzettseg'] ?? ''),
        ':tapasztalat'              => cleanInput($post['allas_tapasztalat'] ?? ''),
        ':munkakor_leiras'          => trim($post['allas_feladatok'] ?? ''),
        ':ceg_info'                 => trim($post['allas_cegInfo'] ?? ''),
        ':jelentkezesi_hatarido'    => !empty($post['allas_jelentkezesiHatarido']) ? $post['allas_jelentkezesiHatarido'] : null,
        ':nyelvek'                  => $nyelvek,
        ':szamitogep_ismeretek'     => $szamitogep
    ]);
}

/**
 * Ingatlan adatok mentése
 */
function saveIngatlanData(PDO $pdo, int $hirdetesId, array $post): void {
    $jellemzok = isset($post['ingatlan_egyeb']) && is_array($post['ingatlan_egyeb'])
        ? implode(', ', $post['ingatlan_egyeb']) : null;
    $kozmuvek = isset($post['ingatlan_kozmu']) && is_array($post['ingatlan_kozmu'])
        ? implode(', ', $post['ingatlan_kozmu']) : null;
    
    $sql = "INSERT INTO hirdetes_ingatlan (
                hirdetes_id, ingatlan_tipus, hirdetes_tipus, meret, telek_meret,
                szobak_szama, felszobak_szama, furdoszobak_szama, epites_eve,
                allapot, futes_tipusa, emelet, jellemzok, parkolas,
                bekoltozhetoseg, rezsikoltseg, kaucio, kozmuvek, besorolas
            ) VALUES (
                :hirdetes_id, :ingatlan_tipus, :hirdetes_tipus, :meret, :telek_meret,
                :szobak_szama, :felszobak_szama, :furdoszobak_szama, :epites_eve,
                :allapot, :futes_tipusa, :emelet, :jellemzok, :parkolas,
                :bekoltozhetoseg, :rezsikoltseg, :kaucio, :kozmuvek, :besorolas
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'        => $hirdetesId,
        ':ingatlan_tipus'     => cleanInput($post['ingatlan_tipus'] ?? ''),
        ':hirdetes_tipus'     => cleanInput($post['ingatlan_hirdetesTipus'] ?? ''),
        ':meret'              => !empty($post['ingatlan_meret']) ? (int)$post['ingatlan_meret'] : null,
        ':telek_meret'        => !empty($post['ingatlan_telekMeret']) ? (int)$post['ingatlan_telekMeret'] : null,
        ':szobak_szama'       => !empty($post['ingatlan_szobak']) ? (int)$post['ingatlan_szobak'] : null,
        ':felszobak_szama'    => !empty($post['ingatlan_felszobak']) ? (int)$post['ingatlan_felszobak'] : null,
        ':furdoszobak_szama'  => !empty($post['ingatlan_furdoszobak']) ? (int)$post['ingatlan_furdoszobak'] : null,
        ':epites_eve'         => !empty($post['ingatlan_epitesEve']) ? (int)$post['ingatlan_epitesEve'] : null,
        ':allapot'            => cleanInput($post['ingatlan_allapot'] ?? ''),
        ':futes_tipusa'       => cleanInput($post['ingatlan_futes'] ?? ''),
        ':emelet'             => cleanInput($post['ingatlan_emelet'] ?? ''),
        ':jellemzok'          => $jellemzok,
        ':parkolas'           => cleanInput($post['ingatlan_parkolas'] ?? ''),
        ':bekoltozhetoseg'    => cleanInput($post['ingatlan_koltozheto'] ?? ''),
        ':rezsikoltseg'       => !empty($post['ingatlan_rezsi']) ? (int)$post['ingatlan_rezsi'] : null,
        ':kaucio'             => cleanInput($post['ingatlan_kaucio'] ?? ''),
        ':kozmuvek'           => $kozmuvek,
        ':besorolas'          => cleanInput($post['ingatlan_besorolas'] ?? '')
    ]);
}

/**
 * Jármű adatok mentése
 */
function saveJarmuData(PDO $pdo, int $hirdetesId, array $post): void {
    $okmanyok = isset($post['jarmu_okmany']) && is_array($post['jarmu_okmany'])
        ? implode(', ', $post['jarmu_okmany']) : null;
    $serules = isset($post['jarmu_serules']) && is_array($post['jarmu_serules'])
        ? implode(', ', $post['jarmu_serules']) : null;
    
    $sql = "INSERT INTO hirdetes_jarmu (
                hirdetes_id, jarmu_tipus, marka, modell, evjarat,
                uzemanyag, sebessegvalto, hengerurtartalom, teljesitmeny,
                kilometer_allas, allapot, muszaki_ervenyesseg, ajtok_szama,
                szin, okmanyok, eredeti_magyar, serules, extrák
            ) VALUES (
                :hirdetes_id, :jarmu_tipus, :marka, :modell, :evjarat,
                :uzemanyag, :sebessegvalto, :hengerurtartalom, :teljesitmeny,
                :kilometer_allas, :allapot, :muszaki_ervenyesseg, :ajtok_szama,
                :szin, :okmanyok, :eredeti_magyar, :serules, :extrak
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'           => $hirdetesId,
        ':jarmu_tipus'           => cleanInput($post['jarmu_tipus'] ?? ''),
        ':marka'                 => cleanInput($post['jarmu_marka'] ?? ''),
        ':modell'                => cleanInput($post['jarmu_modell'] ?? ''),
        ':evjarat'               => !empty($post['jarmu_evjarat']) ? (int)$post['jarmu_evjarat'] : null,
        ':uzemanyag'             => cleanInput($post['jarmu_uzemanyag'] ?? ''),
        ':sebessegvalto'         => cleanInput($post['jarmu_sebessegvalto'] ?? ''),
        ':hengerurtartalom'      => !empty($post['jarmu_hengerurtartalom']) ? (int)$post['jarmu_hengerurtartalom'] : null,
        ':teljesitmeny'          => !empty($post['jarmu_teljesitmeny']) ? (int)$post['jarmu_teljesitmeny'] : null,
        ':kilometer_allas'       => !empty($post['jarmu_km']) ? (int)$post['jarmu_km'] : null,
        ':allapot'               => cleanInput($post['jarmu_allapot'] ?? ''),
        ':muszaki_ervenyesseg'   => cleanInput($post['jarmu_muszaki'] ?? ''),
        ':ajtok_szama'           => !empty($post['jarmu_ajtok']) ? (int)$post['jarmu_ajtok'] : null,
        ':szin'                  => cleanInput($post['jarmu_szin'] ?? ''),
        ':okmanyok'              => $okmanyok,
        ':eredeti_magyar'        => $post['jarmu_magyar'] ?? 'igen',
        ':serules'               => $serules,
        ':extrak'                => trim($post['jarmu_extrak'] ?? '')
    ]);
}

/**
 * Műszaki cikk adatok mentése
 */
function saveMuszakiData(PDO $pdo, int $hirdetesId, array $post): void {
    $tartozekok = isset($post['muszaki_tartozekok']) && is_array($post['muszaki_tartozekok'])
        ? implode(', ', $post['muszaki_tartozekok']) : null;
    
    $sql = "INSERT INTO hirdetes_muszaki (
                hirdetes_id, termek_tipus, marka, modell, allapot,
                tartozekok, garancia, hibak_leiras, technikai_parameterek
            ) VALUES (
                :hirdetes_id, :termek_tipus, :marka, :modell, :allapot,
                :tartozekok, :garancia, :hibak_leiras, :technikai_parameterek
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'            => $hirdetesId,
        ':termek_tipus'           => cleanInput($post['muszaki_tipus'] ?? ''),
        ':marka'                  => cleanInput($post['muszaki_marka'] ?? ''),
        ':modell'                 => cleanInput($post['muszaki_modell'] ?? ''),
        ':allapot'                => cleanInput($post['muszaki_allapot'] ?? ''),
        ':tartozekok'             => $tartozekok,
        ':garancia'               => cleanInput($post['muszaki_garancia'] ?? ''),
        ':hibak_leiras'           => trim($post['muszaki_hibak'] ?? ''),
        ':technikai_parameterek'  => trim($post['muszaki_parameterek'] ?? '')
    ]);
}

/**
 * Háztartás, Bútor adatok mentése
 */
function saveHaztartasData(PDO $pdo, int $hirdetesId, array $post): void {
    $sql = "INSERT INTO hirdetes_haztartas (
                hirdetes_id, termek_tipus, butor_tipus, anyag, allapot,
                meretek, szin, szallithatosag
            ) VALUES (
                :hirdetes_id, :termek_tipus, :butor_tipus, :anyag, :allapot,
                :meretek, :szin, :szallithatosag
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'     => $hirdetesId,
        ':termek_tipus'    => cleanInput($post['haztartas_tipus'] ?? ''),
        ':butor_tipus'     => cleanInput($post['haztartas_butorTipus'] ?? ''),
        ':anyag'           => cleanInput($post['haztartas_anyag'] ?? ''),
        ':allapot'         => cleanInput($post['haztartas_allapot'] ?? ''),
        ':meretek'         => cleanInput($post['haztartas_meretek'] ?? ''),
        ':szin'            => cleanInput($post['haztartas_szin'] ?? ''),
        ':szallithatosag'  => cleanInput($post['haztartas_szallitas'] ?? '')
    ]);
}

/**
 * Szolgáltatás adatok mentése
 */
function saveSzolgaltatasData(PDO $pdo, int $hirdetesId, array $post): void {
    $sql = "INSERT INTO hirdetes_szolgaltatas (
                hirdetes_id, szolgaltatas_tipus, arazas_modja, vegzettseg_leiras,
                tapasztalat, elerhetoseg, referenciak, kiszallas, tavolsag, szamlakepes
            ) VALUES (
                :hirdetes_id, :szolgaltatas_tipus, :arazas_modja, :vegzettseg_leiras,
                :tapasztalat, :elerhetoseg, :referenciak, :kiszallas, :tavolsag, :szamlakepes
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'          => $hirdetesId,
        ':szolgaltatas_tipus'   => cleanInput($post['szolg_tipus'] ?? ''),
        ':arazas_modja'         => cleanInput($post['szolg_arazas'] ?? ''),
        ':vegzettseg_leiras'    => trim($post['szolg_vegzettseg'] ?? ''),
        ':tapasztalat'          => cleanInput($post['szolg_tapasztalat'] ?? ''),
        ':elerhetoseg'          => cleanInput($post['szolg_elerhetoseg'] ?? ''),
        ':referenciak'          => trim($post['szolg_referenciak'] ?? ''),
        ':kiszallas'            => $post['szolg_kiszallas'] ?? 'igen',
        ':tavolsag'             => cleanInput($post['szolg_tavolsag'] ?? ''),
        ':szamlakepes'          => $post['szolg_szamla'] ?? 'nem'
    ]);
}

/**
 * Hobbi, Sport adatok mentése
 */
function saveHobbiData(PDO $pdo, int $hirdetesId, array $post): void {
    $sql = "INSERT INTO hirdetes_hobbi (
                hirdetes_id, termek_tipus, allapot, sportag,
                konyv_mufaj, szerzo, hangszer_tipus, esemeny_datum,
                ules_tipus, kiadas_eve
            ) VALUES (
                :hirdetes_id, :termek_tipus, :allapot, :sportag,
                :konyv_mufaj, :szerzo, :hangszer_tipus, :esemeny_datum,
                :ules_tipus, :kiadas_eve
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'    => $hirdetesId,
        ':termek_tipus'   => cleanInput($post['hobbi_tipus'] ?? ''),
        ':allapot'        => cleanInput($post['hobbi_allapot'] ?? ''),
        ':sportag'        => cleanInput($post['hobbi_sportag'] ?? ''),
        ':konyv_mufaj'    => cleanInput($post['hobbi_konyvMufaj'] ?? ''),
        ':szerzo'         => cleanInput($post['hobbi_szerzo'] ?? ''),
        ':hangszer_tipus' => cleanInput($post['hobbi_hangszerTipus'] ?? ''),
        ':esemeny_datum'  => !empty($post['hobbi_esemenyDatum']) ? $post['hobbi_esemenyDatum'] : null,
        ':ules_tipus'     => cleanInput($post['hobbi_ulesTipus'] ?? ''),
        ':kiadas_eve'     => !empty($post['hobbi_kiadasEve']) ? (int)$post['hobbi_kiadasEve'] : null
    ]);
}

/**
 * Ruházat, Divat adatok mentése
 */
function saveRuhazatData(PDO $pdo, int $hirdetesId, array $post): void {
    $sql = "INSERT INTO hirdetes_ruhazat (
                hirdetes_id, termek_tipus, allapot, meret, marka,
                szin, anyagosszetetel, fazon_stilus, szezon
            ) VALUES (
                :hirdetes_id, :termek_tipus, :allapot, :meret, :marka,
                :szin, :anyagosszetetel, :fazon_stilus, :szezon
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'        => $hirdetesId,
        ':termek_tipus'       => cleanInput($post['ruhazat_tipus'] ?? ''),
        ':allapot'            => cleanInput($post['ruhazat_allapot'] ?? ''),
        ':meret'              => cleanInput($post['ruhazat_meret'] ?? ''),
        ':marka'              => cleanInput($post['ruhazat_marka'] ?? ''),
        ':szin'               => cleanInput($post['ruhazat_szin'] ?? ''),
        ':anyagosszetetel'    => cleanInput($post['ruhazat_anyag'] ?? ''),
        ':fazon_stilus'       => cleanInput($post['ruhazat_fazon'] ?? ''),
        ':szezon'             => cleanInput($post['ruhazat_szezon'] ?? '')
    ]);
}

/**
 * Állatok adatok mentése
 */
function saveAllatokData(PDO $pdo, int $hirdetesId, array $post): void {
    $infoMezok = $post['allat_info'] ?? [];
    $oltas = in_array('oltva', $infoMezok) ? 1 : 0;
    $chip = in_array('chip', $infoMezok) ? 1 : 0;
    $ferregtelenitve = in_array('ferregtelenitve', $infoMezok) ? 1 : 0;
    $ivartalanitott = in_array('ivartalanitott', $infoMezok) ? 1 : 0;
    
    $sql = "INSERT INTO hirdetes_allatok (
                hirdetes_id, allat_tipus, fajta, kor, ivar,
                oltas, chip, ferregtelenitve, ivartalanitott,
                szarmazas, szulok_lathatok, meret_kategoria, szorzet_tipus,
                felszereles_leiras, elviheto
            ) VALUES (
                :hirdetes_id, :allat_tipus, :fajta, :kor, :ivar,
                :oltas, :chip, :ferregtelenitve, :ivartalanitott,
                :szarmazas, :szulok_lathatok, :meret_kategoria, :szorzet_tipus,
                :felszereles_leiras, :elviheto
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'          => $hirdetesId,
        ':allat_tipus'          => cleanInput($post['allat_tipus'] ?? ''),
        ':fajta'                => cleanInput($post['allat_fajta'] ?? ''),
        ':kor'                  => cleanInput($post['allat_kor'] ?? ''),
        ':ivar'                 => cleanInput($post['allat_ivar'] ?? ''),
        ':oltas'                => $oltas,
        ':chip'                 => $chip,
        ':ferregtelenitve'      => $ferregtelenitve,
        ':ivartalanitott'       => $ivartalanitott,
        ':szarmazas'            => cleanInput($post['allat_szarmazas'] ?? ''),
        ':szulok_lathatok'      => $post['allat_szulok'] ?? 'nem',
        ':meret_kategoria'      => cleanInput($post['allat_meret'] ?? ''),
        ':szorzet_tipus'        => cleanInput($post['allat_szorzet'] ?? ''),
        ':felszereles_leiras'   => trim($post['allat_felszereles'] ?? ''),
        ':elviheto'             => cleanInput($post['allat_elviheto'] ?? '')
    ]);
}

/**
 * Egyéb adatok mentése
 */
function saveEgyebData(PDO $pdo, int $hirdetesId, array $post): void {
    $sql = "INSERT INTO hirdetes_egyeb (
                hirdetes_id, hirdetes_tipus, targy_kategoria,
                esemeny_idopont, esemeny_helyszin, reszletes_leiras, csere_targy
            ) VALUES (
                :hirdetes_id, :hirdetes_tipus, :targy_kategoria,
                :esemeny_idopont, :esemeny_helyszin, :reszletes_leiras, :csere_targy
            )";
    
    $esemenyIdopont = null;
    if (!empty($post['egyeb_idopont'])) {
        $esemenyIdopont = $post['egyeb_idopont'];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hirdetes_id'        => $hirdetesId,
        ':hirdetes_tipus'     => cleanInput($post['egyeb_tipus'] ?? ''),
        ':targy_kategoria'    => cleanInput($post['egyeb_kategoria'] ?? ''),
        ':esemeny_idopont'    => $esemenyIdopont,
        ':esemeny_helyszin'   => cleanInput($post['egyeb_helyszin'] ?? ''),
        ':reszletes_leiras'   => trim($post['egyeb_reszletesLeiras'] ?? ''),
        ':csere_targy'        => cleanInput($post['egyeb_csereTargy'] ?? '')
    ]);
}
