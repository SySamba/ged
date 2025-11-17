<?php
/**
 * Test simple du module d'achat
 */

echo "<h2>Test du module d'achat</h2>";

try {
    // Test 1: Configuration principale
    echo "<h3>1. Test de la configuration principale</h3>";
    require_once __DIR__ . '/../config/config.php';
    echo "✅ Configuration principale chargée<br>";
    echo "APP_NAME: " . APP_NAME . "<br>";
    echo "APP_URL: " . APP_URL . "<br>";
    
    // Test 2: Base de données
    echo "<h3>2. Test de la base de données</h3>";
    $database = new Database();
    $pdo = $database->getConnection();
    echo "✅ Connexion à la base de données réussie<br>";
    
    // Test 3: Tables du module d'achat
    echo "<h3>3. Test des tables du module d'achat</h3>";
    $tables = [
        'suppliers',
        'purchase_categories', 
        'purchase_requests',
        'purchase_orders'
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $stmt->execute();
            $count = $stmt->fetchColumn();
            echo "✅ Table $table: $count enregistrements<br>";
        } catch (Exception $e) {
            echo "❌ Table $table: " . $e->getMessage() . "<br>";
        }
    }
    
    // Test 4: Classes du module d'achat
    echo "<h3>4. Test des classes du module d'achat</h3>";
    $classes = ['Supplier', 'PurchaseRequest', 'PurchaseOrder'];
    
    foreach ($classes as $class) {
        $class_file = __DIR__ . '/../classes/' . $class . '.php';
        if (file_exists($class_file)) {
            require_once $class_file;
            if (class_exists($class)) {
                echo "✅ Classe $class: disponible<br>";
            } else {
                echo "❌ Classe $class: non définie<br>";
            }
        } else {
            echo "❌ Classe $class: fichier non trouvé<br>";
        }
    }
    
    // Test 5: Session
    echo "<h3>5. Test de la session</h3>";
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "✅ Session active<br>";
        if (isset($_SESSION['user_id'])) {
            echo "✅ Utilisateur connecté: ID " . $_SESSION['user_id'] . "<br>";
        } else {
            echo "⚠️ Aucun utilisateur connecté<br>";
        }
    } else {
        echo "❌ Session non active<br>";
    }
    
    echo "<h3>✅ Tests terminés</h3>";
    echo "<p><a href='index.php'>Accéder au module d'achat</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Erreur lors des tests</h3>";
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
    echo "<p>Trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}
?>
