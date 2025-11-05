<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Test des corrections - Profil et Paramètres</h2>";

try {
    $user = new User();
    
    // Test 1: Vérifier la méthode getById
    echo "<h3>1. Test de récupération utilisateur</h3>";
    $testUser = $user->getById(1); // Supposons que l'admin a l'ID 1
    
    if ($testUser) {
        echo "<p style='color: green;'>✅ Récupération utilisateur réussie</p>";
        echo "<ul>";
        echo "<li><strong>Nom:</strong> " . ($testUser['nom'] ?? 'Non défini') . "</li>";
        echo "<li><strong>Prénom:</strong> " . ($testUser['prenom'] ?? 'Non défini') . "</li>";
        echo "<li><strong>Email:</strong> " . ($testUser['email'] ?? 'Non défini') . "</li>";
        echo "<li><strong>Téléphone:</strong> " . ($testUser['telephone'] ?? 'Non défini') . "</li>";
        echo "<li><strong>Adresse:</strong> " . ($testUser['adresse'] ?? 'Non défini') . "</li>";
        echo "<li><strong>Statut:</strong> " . ($testUser['statut'] ?? 'Non défini') . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Impossible de récupérer l'utilisateur</p>";
    }
    
    // Test 2: Vérifier la méthode getByIdWithPassword
    echo "<h3>2. Test de récupération utilisateur avec mot de passe</h3>";
    $testUserWithPassword = $user->getByIdWithPassword(1);
    
    if ($testUserWithPassword) {
        echo "<p style='color: green;'>✅ Récupération utilisateur avec mot de passe réussie</p>";
        echo "<p><strong>Mot de passe hashé présent:</strong> " . (isset($testUserWithPassword['password']) ? 'Oui' : 'Non') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Impossible de récupérer l'utilisateur avec mot de passe</p>";
    }
    
    // Test 3: Vérifier les préférences
    echo "<h3>3. Test des préférences utilisateur</h3>";
    $preferences = $user->getPreferences(1);
    
    if ($preferences) {
        echo "<p style='color: green;'>✅ Récupération des préférences réussie</p>";
        echo "<ul>";
        foreach ($preferences as $key => $value) {
            echo "<li><strong>$key:</strong> " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Impossible de récupérer les préférences</p>";
    }
    
    // Test 4: Vérifier la structure de la base de données
    echo "<h3>4. Vérification de la structure de la base de données</h3>";
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    $requiredColumns = ['telephone', 'adresse', 'statut'];
    $missingColumns = [];
    
    $existingColumns = array_column($columns, 'Field');
    
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $existingColumns)) {
            $missingColumns[] = $col;
        }
    }
    
    if (empty($missingColumns)) {
        echo "<p style='color: green;'>✅ Toutes les colonnes requises sont présentes</p>";
    } else {
        echo "<p style='color: red;'>❌ Colonnes manquantes: " . implode(', ', $missingColumns) . "</p>";
    }
    
    // Test 5: Vérifier la table user_preferences
    echo "<h3>5. Vérification de la table user_preferences</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_preferences'");
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Table user_preferences existe</p>";
        
        $stmt = $pdo->query("DESCRIBE user_preferences");
        $prefColumns = $stmt->fetchAll();
        
        echo "<p><strong>Structure de la table:</strong></p>";
        echo "<ul>";
        foreach ($prefColumns as $col) {
            echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Table user_preferences manquante</p>";
    }
    
    echo "<h3>✅ Tests terminés</h3>";
    echo "<p><strong>Pages à tester maintenant :</strong></p>";
    echo "<ul>";
    echo "<li><a href='/document/profile.php' target='_blank'>Profile.php</a></li>";
    echo "<li><a href='/document/settings.php' target='_blank'>Settings.php</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur lors des tests : " . $e->getMessage() . "</p>";
}
?>
