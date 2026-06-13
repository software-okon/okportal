CREATE DATABASE IF NOT EXISTS orszagkozepe_hirdetesek CHARACTER SET utf8mb4 COLLATE utf8mb4_hungarian_ci;
USE orszagkozepe_hirdetesek;

CREATE TABLE IF NOT EXISTS adminok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    jelszo VARCHAR(255) NOT NULL,
    nev VARCHAR(150) NOT NULL,
    szerep ENUM('superadmin','admin','moderator') DEFAULT 'moderator',
    utolso_belepes DATETIME DEFAULT NULL,
    letrehozva DATETIME DEFAULT CURRENT_TIMESTAMP,
    aktiv TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO adminok (email, jelszo, nev, szerep) VALUES 
('admin@orszagkozepe.hu', '$2y$12$LJ3m4ys3GZfHdHRQUKzXiuVvN5pGdRqQJm5kMEqOvY8tB1ZVq7POW', 'Admin', 'superadmin');
-- jelszó: Admin123!

CREATE TABLE IF NOT EXISTS felhasznalok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    jelszo VARCHAR(255) NOT NULL,
    nev VARCHAR(150) NOT NULL,
    telefon VARCHAR(20),
    megye VARCHAR(50),
    varos VARCHAR(100),
    iranyitoszam VARCHAR(4),
    profil_kep VARCHAR(255) DEFAULT NULL,
    email_ellenorizve TINYINT(1) DEFAULT 0,
    email_token VARCHAR(100) DEFAULT NULL,
    jelszo_token VARCHAR(100) DEFAULT NULL,
    jelszo_token_lejarat DATETIME DEFAULT NULL,
    utolso_belepes DATETIME DEFAULT NULL,
    regisztracio_datum DATETIME DEFAULT CURRENT_TIMESTAMP,
    aktiv TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetesek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    felhasznalo_id INT DEFAULT NULL,
    fokategoria VARCHAR(50) NOT NULL,
    alkategoria VARCHAR(100) NOT NULL,
    cim VARCHAR(80) NOT NULL,
    slug VARCHAR(100) DEFAULT NULL,
    leiras TEXT NOT NULL,
    ar INT DEFAULT NULL,
    ar_tipus ENUM('fix','megbeszeles','ingyen') DEFAULT 'fix',
    megye VARCHAR(50) NOT NULL,
    varos VARCHAR(100) NOT NULL,
    iranyitoszam VARCHAR(4) DEFAULT NULL,
    elado_nev VARCHAR(150) NOT NULL,
    telefon VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    ervenyesseg INT DEFAULT 14,
    kiemeles ENUM('alap','normal','premium') DEFAULT 'alap',
    cimkek VARCHAR(255) DEFAULT NULL,
    video_url VARCHAR(500) DEFAULT NULL,
    alku TINYINT(1) DEFAULT 1,
    statusz ENUM('aktiv','inaktiv','torolt','fuggoben') DEFAULT 'fuggoben',
    letrehozva DATETIME DEFAULT CURRENT_TIMESTAMP,
    modositva DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    lejarat DATETIME DEFAULT NULL,
    megtekintesek INT DEFAULT 0,
    INDEX idx_fokategoria (fokategoria),
    INDEX idx_statusz (statusz),
    INDEX idx_megye (megye),
    INDEX idx_ar (ar),
    INDEX idx_letrehozva (letrehozva),
    FULLTEXT INDEX ft_kereses (cim, leiras),
    FOREIGN KEY (felhasznalo_id) REFERENCES felhasznalok(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_kepek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL,
    fajl_nev VARCHAR(255) NOT NULL,
    eredeti_nev VARCHAR(255) NOT NULL,
    sorrend INT DEFAULT 0,
    feltoltve DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kedvencek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    felhasznalo_id INT NOT NULL,
    hirdetes_id INT NOT NULL,
    mentve DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_kedvenc (felhasznalo_id, hirdetes_id),
    FOREIGN KEY (felhasznalo_id) REFERENCES felhasznalok(id) ON DELETE CASCADE,
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS uzenetek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL,
    kuldo_id INT DEFAULT NULL,
    cimzett_email VARCHAR(255) NOT NULL,
    kuldo_nev VARCHAR(150) NOT NULL,
    kuldo_email VARCHAR(255) NOT NULL,
    kuldo_telefon VARCHAR(20) DEFAULT NULL,
    targy VARCHAR(255) NOT NULL,
    uzenet TEXT NOT NULL,
    olvasva TINYINT(1) DEFAULT 0,
    letrehozva DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS megtekintes_naplo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL,
    ip_cim VARCHAR(45),
    session_id VARCHAR(100),
    datum DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hirdetes_naplo (hirdetes_id, datum)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS statisztikak (
    id INT AUTO_INCREMENT PRIMARY KEY,
    datum DATE NOT NULL UNIQUE,
    uj_hirdetesek INT DEFAULT 0,
    aktiv_hirdetesek INT DEFAULT 0,
    uj_felhasznalok INT DEFAULT 0,
    osszes_megtekintes INT DEFAULT 0,
    osszes_uzenet INT DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_allas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    munkakor_tipus VARCHAR(50),
    foglalkoztatas_jellege VARCHAR(50),
    fizetes_also INT DEFAULT NULL,
    fizetes_felso INT DEFAULT NULL,
    fizetes_tipus VARCHAR(30),
    juttatasok TEXT,
    vegzettseg VARCHAR(50),
    tapasztalat VARCHAR(30),
    munkakor_leiras TEXT,
    ceg_info TEXT,
    jelentkezesi_hatarido DATE DEFAULT NULL,
    nyelvek TEXT,
    szamitogep_ismeretek TEXT,
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_ingatlan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    ingatlan_tipus VARCHAR(50),
    hirdetes_tipus VARCHAR(50),
    meret INT DEFAULT NULL,
    telek_meret INT DEFAULT NULL,
    szobak_szama INT DEFAULT NULL,
    felszobak_szama INT DEFAULT NULL,
    furdoszobak_szama INT DEFAULT NULL,
    epites_eve INT DEFAULT NULL,
    allapot VARCHAR(30),
    futes_tipusa VARCHAR(50),
    emelet VARCHAR(30),
    jellemzok TEXT,
    parkolas VARCHAR(30),
    bekoltozhetoseg VARCHAR(50),
    rezsikoltseg INT DEFAULT NULL,
    kaucio VARCHAR(100),
    kozmuvek TEXT,
    besorolas VARCHAR(50),
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_jarmu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    jarmu_tipus VARCHAR(50),
    marka VARCHAR(100),
    modell VARCHAR(100),
    evjarat INT DEFAULT NULL,
    uzemanyag VARCHAR(30),
    sebessegvalto VARCHAR(30),
    hengerurtartalom INT DEFAULT NULL,
    teljesitmeny INT DEFAULT NULL,
    kilometer_allas INT DEFAULT NULL,
    allapot VARCHAR(30),
    muszaki_ervenyesseg VARCHAR(100),
    ajtok_szama INT DEFAULT NULL,
    szin VARCHAR(50),
    okmanyok TEXT,
    eredeti_magyar VARCHAR(10),
    serules TEXT,
    extrák TEXT,
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_muszaki (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    termek_tipus VARCHAR(50),
    marka VARCHAR(100),
    modell VARCHAR(100),
    allapot VARCHAR(50),
    tartozekok TEXT,
    garancia VARCHAR(50),
    hibak_leiras TEXT,
    technikai_parameterek TEXT,
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_haztartas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    termek_tipus VARCHAR(50),
    butor_tipus VARCHAR(50),
    anyag VARCHAR(50),
    allapot VARCHAR(30),
    meretek VARCHAR(100),
    szin VARCHAR(50),
    szallithatosag VARCHAR(50),
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_szolgaltatas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    szolgaltatas_tipus VARCHAR(50),
    arazas_modja VARCHAR(30),
    vegzettseg_leiras TEXT,
    tapasztalat VARCHAR(20),
    elerhetoseg VARCHAR(30),
    referenciak TEXT,
    kiszallas VARCHAR(10),
    tavolsag VARCHAR(100),
    szamlakepes VARCHAR(10),
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_hobbi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    termek_tipus VARCHAR(50),
    allapot VARCHAR(30),
    sportag VARCHAR(50),
    konyv_mufaj VARCHAR(50),
    szerzo VARCHAR(150),
    hangszer_tipus VARCHAR(50),
    esemeny_datum DATE DEFAULT NULL,
    ules_tipus VARCHAR(30),
    kiadas_eve INT DEFAULT NULL,
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_ruhazat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    termek_tipus VARCHAR(50),
    allapot VARCHAR(30),
    meret VARCHAR(20),
    marka VARCHAR(100),
    szin VARCHAR(50),
    anyagosszetetel VARCHAR(150),
    fazon_stilus VARCHAR(100),
    szezon VARCHAR(30),
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_allatok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    allat_tipus VARCHAR(50),
    fajta VARCHAR(100),
    kor VARCHAR(100),
    ivar VARCHAR(20),
    oltas TINYINT(1) DEFAULT 0,
    chip TINYINT(1) DEFAULT 0,
    ferregtelenitve TINYINT(1) DEFAULT 0,
    ivartalanitott TINYINT(1) DEFAULT 0,
    szarmazas VARCHAR(30),
    szulok_lathatok VARCHAR(10),
    meret_kategoria VARCHAR(30),
    szorzet_tipus VARCHAR(30),
    felszereles_leiras TEXT,
    elviheto VARCHAR(50),
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hirdetes_egyeb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirdetes_id INT NOT NULL UNIQUE,
    hirdetes_tipus VARCHAR(50),
    targy_kategoria VARCHAR(50),
    esemeny_idopont DATETIME DEFAULT NULL,
    esemeny_helyszin VARCHAR(255),
    reszletes_leiras TEXT,
    csere_targy VARCHAR(255),
    FOREIGN KEY (hirdetes_id) REFERENCES hirdetesek(id) ON DELETE CASCADE
) ENGINE=InnoDB;