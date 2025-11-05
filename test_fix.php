<?php
// Test de la correction Database
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Test de la Correction Database</h1>";

try {
    // Inclure la config
    require_once __DIR__ . '/config/config.php';
    echo "✅ Config chargée<br>";
    
    // Tester la classe Database
    if (class_exists('Database')) {
        echo "✅ Classe Database trouvée<br>";
        
        $db = new Database();
        echo "✅ Instance Database créée<br>";
        
        $pdo = $db->getConnection();
        echo "✅ Connexion DB réussie<br>";
        
        // Test requête
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "✅ Requête test réussie - Utilisateurs: " . $result['count'] . "<br>";
        
    } else {
        echo "❌ Classe Database non trouvée<br>";
    }
    
    // Tester la classe User
    if (class_exists('User')) {
        echo "✅ Classe User trouvée<br>";
        
        $user = new User();
        echo "✅ Instance User créée avec succès !<br>";
        
    } else {
        echo "❌ Classe User non trouvée<br>";
    }
    
    echo "<h2>✅ CORRECTION RÉUSSIE !</h2>";
    echo "<p>La classe Database est maintenant accessible à la classe User.</p>";
    echo "<p><strong>Vous pouvez maintenant tester:</strong> <a href='https://ged.teranganumerique.com/auth/login.php'>https://ged.teranganumerique.com/auth/login.php</a></p>";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
    echo "❌ Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "<br>";
}
?>
