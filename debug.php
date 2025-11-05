<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnostic DigiDocs</h1>";
echo "<p>Test étape par étape pour identifier l'erreur 500</p>";

// Test 1: PHP de base
echo "<h2>1. Test PHP de base</h2>";
echo "✅ PHP fonctionne - Version: " . phpversion() . "<br>";
echo "✅ Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "✅ Script actuel: " . __FILE__ . "<br>";

// Test 2: Inclusion de config
echo "<h2>2. Test de configuration</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    echo "✅ Config chargée avec succès<br>";
    echo "✅ APP_URL: " . APP_URL . "<br>";
    echo "✅ APP_NAME: " . APP_NAME . "<br>";
    echo "✅ ROOT_PATH: " . ROOT_PATH . "<br>";
} catch (Exception $e) {
    echo "❌ Erreur config: " . $e->getMessage() . "<br>";
    echo "❌ Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "<br>";
    exit;
}

// Test 3: Base de données
echo "<h2>3. Test base de données</h2>";
try {
    if (class_exists('Database')) {
        echo "✅ Classe Database trouvée<br>";
        $db = new Database();
        echo "✅ Instance Database créée<br>";
        $pdo = $db->getConnection();
        echo "✅ Connexion DB réussie<br>";
        
        // Test d'une requête simple
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "✅ Requête test réussie - Utilisateurs: " . $result['count'] . "<br>";
    } else {
        echo "❌ Classe Database non trouvée<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur DB: " . $e->getMessage() . "<br>";
    echo "❌ Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "<br>";
}

// Test 4: Classes
echo "<h2>4. Test des classes</h2>";
try {
    if (class_exists('User')) {
        echo "✅ Classe User trouvée<br>";
        $user = new User();
        echo "✅ Instance User créée<br>";
    } else {
        echo "❌ Classe User non trouvée<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur classe User: " . $e->getMessage() . "<br>";
    echo "❌ Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "<br>";
}

// Test 5: Dossiers
echo "<h2>5. Test des dossiers</h2>";
$folders = ['uploads', 'logs', 'temp', 'config', 'classes'];
foreach ($folders as $folder) {
    $path = __DIR__ . '/' . $folder;
    if (is_dir($path)) {
        if (is_readable($path)) {
            echo "✅ Dossier $folder: Accessible<br>";
        } else {
            echo "⚠️ Dossier $folder: Pas de lecture<br>";
        }
    } else {
        echo "❌ Dossier $folder: N'existe pas<br>";
    }
}

// Test 6: Fonctions
echo "<h2>6. Test des fonctions</h2>";
try {
    if (function_exists('sanitize')) {
        $test = sanitize("test<script>alert('test')</script>");
        echo "✅ Fonction sanitize: " . $test . "<br>";
    } else {
        echo "❌ Fonction sanitize non trouvée<br>";
    }
    
    if (function_exists('isLoggedIn')) {
        $logged = isLoggedIn();
        echo "✅ Fonction isLoggedIn: " . ($logged ? 'true' : 'false') . "<br>";
    } else {
        echo "❌ Fonction isLoggedIn non trouvée<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur fonctions: " . $e->getMessage() . "<br>";
}

// Test 7: Session
echo "<h2>7. Test session</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session active<br>";
    echo "✅ Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Session non active<br>";
}

// Test 8: Simulation de login.php
echo "<h2>8. Test simulation login.php</h2>";
try {
    // Simuler ce que fait login.php
    if (function_exists('isLoggedIn')) {
        $isLogged = isLoggedIn();
        echo "✅ isLoggedIn() appelé: " . ($isLogged ? 'true' : 'false') . "<br>";
    }
    
    echo "✅ Simulation login.php réussie<br>";
} catch (Exception $e) {
    echo "❌ Erreur simulation login: " . $e->getMessage() . "<br>";
    echo "❌ Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "<br>";
}

echo "<h2>✅ Diagnostic terminé</h2>";
echo "<p><strong>Si tout est vert ci-dessus, le problème pourrait être:</strong></p>";
echo "<ul>";
echo "<li>Permissions de fichiers sur le serveur</li>";
echo "<li>Configuration du serveur web</li>";
echo "<li>Modules PHP manquants</li>";
echo "<li>Limite de mémoire PHP</li>";
echo "</ul>";
echo "<p><strong>Accédez à ce fichier via:</strong> <a href='https://ged.teranganumerique.com/debug.php'>https://ged.teranganumerique.com/debug.php</a></p>";
?>
