<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Test de la Classe Job</h2>";

try {
    // Test 1: Vérifier que la classe Job existe
    echo "<h3>1. Test d'existence de la classe Job</h3>";
    if (class_exists('Job')) {
        echo "<p style='color: green;'>✅ Classe Job trouvée</p>";
        
        // Test 2: Créer une instance
        $job = new Job();
        echo "<p style='color: green;'>✅ Instance Job créée avec succès</p>";
        
        // Test 3: Vérifier les méthodes disponibles
        echo "<h3>2. Méthodes disponibles dans la classe Job</h3>";
        $methods = get_class_methods($job);
        echo "<ul>";
        foreach ($methods as $method) {
            echo "<li><strong>$method</strong></li>";
        }
        echo "</ul>";
        
        // Test 4: Vérifier la méthode createOffre
        if (method_exists($job, 'createOffre')) {
            echo "<p style='color: green;'>✅ Méthode createOffre trouvée</p>";
        } else {
            echo "<p style='color: red;'>❌ Méthode createOffre non trouvée</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Classe Job non trouvée</p>";
    }
    
    // Test 5: Vérifier la structure de la base de données
    echo "<h3>3. Vérification de la table offres_emploi</h3>";
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'offres_emploi'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Table offres_emploi existe</p>";
        
        // Afficher la structure
        $stmt = $pdo->query("DESCRIBE offres_emploi");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Table offres_emploi non trouvée</p>";
    }
    
    echo "<h3>✅ Tests terminés</h3>";
    echo "<p><strong>La page jobs/create.php devrait maintenant fonctionner :</strong></p>";
    echo "<p><a href='/document/jobs/create.php' target='_blank'>Tester jobs/create.php</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur lors des tests : " . $e->getMessage() . "</p>";
}
?>
