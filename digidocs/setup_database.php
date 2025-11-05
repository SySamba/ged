<?php
/**
 * Script d'installation complète de DigiDocs
 * Crée la base de données et configure le système
 */

// Configuration de la base de données
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'digidocs';

echo "🚀 Installation de DigiDocs - Démarrage...\n\n";

try {
    // 1. Connexion à MySQL (sans base de données spécifique)
    echo "📡 Connexion à MySQL...\n";
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✅ Connexion MySQL réussie\n\n";

    // 2. Supprimer et recréer la base de données
    echo "🗄️ Création de la base de données '$dbname'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS $dbname");
    $pdo->exec("CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE $dbname");
    echo "✅ Base de données '$dbname' créée\n\n";

    // 3. Lire et exécuter le fichier SQL
    echo "📋 Exécution du script SQL...\n";
    $sqlFile = __DIR__ . '/database/schema.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Fichier schema.sql introuvable dans le dossier database/");
    }

    $sql = file_get_contents($sqlFile);
    
    // Supprimer les lignes de création/utilisation de base car on l'a déjà fait
    $sql = preg_replace('/DROP DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE.*?;/i', '', $sql);
    
    // Diviser en requêtes individuelles
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $pdo->exec($statement);
        }
    }
    echo "✅ Tables créées avec succès\n\n";

    // 4. Créer l'utilisateur admin avec le bon mot de passe
    echo "👤 Configuration du compte administrateur...\n";
    $hashedPassword = password_hash('Touba2021@', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (nom, prenom, email, password, role, permissions) 
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE password = VALUES(password)
    ");
    
    $permissions = json_encode([
        "documents" => ["create" => true, "read" => true, "update" => true, "delete" => true],
        "users" => ["create" => true, "read" => true, "update" => true, "delete" => true],
        "offres" => ["create" => true, "read" => true, "update" => true, "delete" => true],
        "modeles" => ["create" => true, "read" => true, "update" => true, "delete" => true]
    ]);
    
    $stmt->execute([
        'Admin',
        'Système', 
        'sambasy837@gmail.com',
        $hashedPassword,
        'admin',
        $permissions
    ]);
    echo "✅ Compte administrateur configuré\n\n";

    // 5. Créer les dossiers nécessaires
    echo "📁 Création des dossiers de stockage...\n";
    $directories = [
        __DIR__ . '/uploads',
        __DIR__ . '/uploads/documents',
        __DIR__ . '/uploads/cv',
        __DIR__ . '/uploads/generated',
        __DIR__ . '/uploads/temp',
        __DIR__ . '/logs'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "  ✅ Créé: " . basename($dir) . "/\n";
        } else {
            echo "  ℹ️ Existe déjà: " . basename($dir) . "/\n";
        }
    }
    
    // 6. Créer le fichier .htaccess pour sécuriser les uploads
    echo "\n🔒 Configuration de la sécurité...\n";
    $htaccessContent = "# Sécurité DigiDocs\nOptions -Indexes\nDeny from all\n<Files *.php>\nAllow from all\n</Files>";
    file_put_contents(__DIR__ . '/uploads/.htaccess', $htaccessContent);
    echo "✅ Fichier .htaccess créé\n\n";

    // 7. Résumé final
    echo "🎉 INSTALLATION TERMINÉE AVEC SUCCÈS !\n\n";
    echo "📋 Informations de connexion :\n";
    echo "   🌐 URL : http://localhost/document/digidocs/\n";
    echo "   📧 Email : sambasy837@gmail.com\n";
    echo "   🔑 Mot de passe : Touba2021@\n\n";
    echo "🔗 Liens utiles :\n";
    echo "   • Connexion : http://localhost/document/digidocs/auth/login.php\n";
    echo "   • Dashboard : http://localhost/document/digidocs/dashboard.php\n\n";
    echo "⚠️ N'oubliez pas de supprimer ce fichier après installation !\n";

} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
    echo "📞 Vérifiez que :\n";
    echo "   • MySQL est démarré\n";
    echo "   • Les paramètres de connexion sont corrects\n";
    echo "   • L'utilisateur MySQL a les droits CREATE DATABASE\n";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation DigiDocs</title>
    <style>
        body { 
            font-family: 'Courier New', monospace; 
            background: #1e1e1e; 
            color: #00ff00; 
            padding: 20px; 
            line-height: 1.6;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: #00ff00; }
        .error { color: #ff4444; }
        .info { color: #4488ff; }
        .warning { color: #ffaa00; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Installation DigiDocs</h1>
        <pre><?php 
        // Le script PHP s'exécute et affiche le résultat ici
        ?></pre>
    </div>
</body>
</html>
