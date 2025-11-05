<?php
// Inclusion directe des constantes de base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'digidocs');
define('DB_USER', 'root');
define('DB_PASS', '');

echo "<h2>Migration - Ajout des colonnes et tables pour le profil utilisateur</h2>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie</p>";
    
    // 1. Vérifier et ajouter les colonnes manquantes dans la table users
    echo "<h3>1. Vérification de la table users</h3>";
    
    // Vérifier si la colonne telephone existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'telephone'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN telephone VARCHAR(20) NULL AFTER email");
        echo "<p style='color: blue;'>✅ Colonne 'telephone' ajoutée</p>";
    } else {
        echo "<p style='color: gray;'>ℹ️ Colonne 'telephone' existe déjà</p>";
    }
    
    // Vérifier si la colonne adresse existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'adresse'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN adresse TEXT NULL AFTER telephone");
        echo "<p style='color: blue;'>✅ Colonne 'adresse' ajoutée</p>";
    } else {
        echo "<p style='color: gray;'>ℹ️ Colonne 'adresse' existe déjà</p>";
    }
    
    // Vérifier si la colonne statut existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'statut'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif' AFTER permissions");
        echo "<p style='color: blue;'>✅ Colonne 'statut' ajoutée</p>";
    } else {
        echo "<p style='color: gray;'>ℹ️ Colonne 'statut' existe déjà</p>";
    }
    
    // Vérifier si la colonne derniere_connexion existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'derniere_connexion'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN derniere_connexion TIMESTAMP NULL AFTER statut");
        echo "<p style='color: blue;'>✅ Colonne 'derniere_connexion' ajoutée</p>";
    } else {
        echo "<p style='color: gray;'>ℹ️ Colonne 'derniere_connexion' existe déjà</p>";
    }
    
    // 2. Créer la table user_preferences si elle n'existe pas
    echo "<h3>2. Vérification de la table user_preferences</h3>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_preferences'");
    if ($stmt->rowCount() == 0) {
        $sql = "
        CREATE TABLE user_preferences (
            user_id INT PRIMARY KEY,
            preferences JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $pdo->exec($sql);
        echo "<p style='color: blue;'>✅ Table 'user_preferences' créée</p>";
    } else {
        echo "<p style='color: gray;'>ℹ️ Table 'user_preferences' existe déjà</p>";
    }
    
    // 3. Afficher la structure finale de la table users
    echo "<h3>3. Structure finale de la table users</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "<td>" . $col['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Mettre à jour les utilisateurs existants avec des valeurs par défaut
    echo "<h3>4. Mise à jour des utilisateurs existants</h3>";
    
    $stmt = $pdo->prepare("UPDATE users SET statut = 'actif' WHERE statut IS NULL");
    $stmt->execute();
    $updated = $stmt->rowCount();
    
    if ($updated > 0) {
        echo "<p style='color: blue;'>✅ $updated utilisateur(s) mis à jour avec le statut 'actif'</p>";
    } else {
        echo "<p style='color: gray;'>ℹ️ Aucun utilisateur à mettre à jour</p>";
    }
    
    echo "<h3>✅ Migration terminée avec succès !</h3>";
    echo "<p><strong>Vous pouvez maintenant tester :</strong></p>";
    echo "<ul>";
    echo "<li><a href='/document/digidocs/profile.php'>Profile.php</a></li>";
    echo "<li><a href='/document/digidocs/settings.php'>Settings.php</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
