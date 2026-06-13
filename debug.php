<?php
/**
 * debug.php - Részletes diagnosztika az alkönyvtár probléma megtalálásához
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Ország Közepe - Alkönyvtár Diagnosztika</h1>";
echo "<pre>";

echo "📌 SZERVER INFORMÁCIÓK:\n";
echo "   Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "   SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "   REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "   HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "   PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "   __DIR__: " . __DIR__ . "\n";
echo "   __FILE__: " . __FILE__ . "\n\n";

echo "📂 FÁJLOK A PROJEKTBEN:\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $isFile = is_file(__DIR__ . '/' . $file);
        echo "   " . ($isFile ? '📄' : '📁') . " {$file}\n";
    }
}
echo "\n";

echo "🔍 HIBAKERESÉS:\n\n";

// 1. Ellenőrizzük a config.php-t
$configFile = __DIR__ . '/config.php';
echo "1. config.php: ";
if (!file_exists($configFile)) {
    echo "❌ NEM TALÁLHATÓ!\n";
} else {
    echo "✅ Megtalálva\n";
    require_once $configFile;
    
    // Adatbázis kapcsolat teszt
    echo "2. Adatbázis kapcsolat: ";
    try {
        $pdo = getDB();
        echo "✅ OK\n";
        echo "3. Adatbázis név: " . DB_NAME . "\n";
        echo "4. Adatbázis host: " . DB_HOST . "\n";
        echo "5. Adatbázis user: " . DB_USER . "\n";
        
        // Táblák ellenőrzése
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "6. Táblák száma: " . count($tables) . "\n";
        if (count($tables) > 0) {
            foreach ($tables as $t) echo "   - {$t}\n";
        } else {
            echo "   ⚠️ Nincsenek táblák! Importáld a database.sql fájlt!\n";
        }
    } catch (Exception $e) {
        echo "❌ HIBA: " . $e->getMessage() . "\n";
    }
}

// 2. Ellenőrizzük az includes mappát
echo "\n7. includes/header.php: ";
echo file_exists(__DIR__ . '/includes/header.php') ? "✅" : "❌ NINCS";
echo "\n8. includes/footer.php: ";
echo file_exists(__DIR__ . '/includes/footer.php') ? "✅" : "❌ NINCS";
echo "\n9. css/style.css: ";
echo file_exists(__DIR__ . '/css/style.css') ? "✅" : "❌ NINCS";
echo "\n10. js/main.js: ";
echo file_exists(__DIR__ . '/js/main.js') ? "✅" : "❌ NINCS";

// 3. Ellenőrizzük a .htaccess-t
echo "\n\n11. .htaccess: ";
echo file_exists(__DIR__ . '/.htaccess') ? "✅" : "❌ NINCS";

// 4. Feltöltési mappa
echo "\n12. uploads/ mappa: ";
if (!file_exists(__DIR__ . '/uploads')) {
    echo "❌ NINCS - létrehozás... ";
    mkdir(__DIR__ . '/uploads', 0755, true);
    echo "✅ Létrehozva";
} else {
    echo "✅ Létezik";
    echo is_writable(__DIR__ . '/uploads') ? " (írható)" : " ❌ (NEM ÍRHATÓ!)";
}

// 5. Logs mappa
echo "\n13. logs/ mappa: ";
if (!file_exists(__DIR__ . '/logs')) {
    echo "❌ NINCS - létrehozás... ";
    mkdir(__DIR__ . '/logs', 0755, true);
    echo "✅ Létrehozva";
} else {
    echo "✅ Létezik";
}

echo "\n\n</pre>";
echo "<h2>📋 TEENDŐK:</h2>";
echo "<ol>";
echo "<li>Ha a táblák hiányoznak: <a href='/phpmyadmin' target='_blank'>phpMyAdmin</a> → importáld a database.sql fájlt</li>";
echo "<li>Ha a .htaccess hiányzik: másold be a projekt gyökerébe</li>";
echo "<li>Ha az includes fájlok hiányoznak: ellenőrizd a fájlszerkezetet</li>";
echo "</ol>";
