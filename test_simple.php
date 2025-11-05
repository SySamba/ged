<?php
// Test ultra simple
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Simple</title></head><body>";
echo "<h1>Test Simple DigiDocs</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Date: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Serveur: " . $_SERVER['HTTP_HOST'] . "</p>";

// Test inclusion config sans classes
echo "<h2>Test Config</h2>";
try {
    $config_file = __DIR__ . '/config/config.php';
    if (file_exists($config_file)) {
        echo "✅ Fichier config existe<br>";
        // Ne pas inclure pour éviter les erreurs de classes
        echo "✅ Path: " . $config_file . "<br>";
    } else {
        echo "❌ Fichier config manquant<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

// Test database.php
echo "<h2>Test Database Config</h2>";
try {
    $db_file = __DIR__ . '/config/database.php';
    if (file_exists($db_file)) {
        echo "✅ Fichier database.php existe<br>";
        // Inclusion pour test
        require_once $db_file;
        echo "✅ Database.php inclus<br>";
        
        if (class_exists('Database')) {
            echo "✅ Classe Database disponible<br>";
        } else {
            echo "❌ Classe Database non trouvée<br>";
        }
    } else {
        echo "❌ Fichier database.php manquant<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur database: " . $e->getMessage() . "<br>";
}

echo "</body></html>";
?>
