<?php
// Test de connexion base de données avec différents paramètres
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Test Connexion Base de Données</h1>";

// Paramètres actuels
$configs = [
    'Config 1 (actuelle)' => [
        'host' => 'localhost',
        'dbname' => 'u588247422_geddb',
        'username' => 'u588247422_geduser',
        'password' => 'Touba2021'
    ],
    'Config 2 (127.0.0.1)' => [
        'host' => '127.0.0.1',
        'dbname' => 'u588247422_geddb',
        'username' => 'u588247422_geduser',
        'password' => 'Touba2021'
    ],
    'Config 3 (sans port)' => [
        'host' => 'localhost',
        'dbname' => 'u588247422_geddb',
        'username' => 'u588247422_geduser',
        'password' => 'Touba2021',
        'port' => null
    ]
];

foreach ($configs as $name => $config) {
    echo "<h2>$name</h2>";
    
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        if (isset($config['port']) && $config['port']) {
            $dsn .= ";port={$config['port']}";
        }
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        echo "🔄 Tentative de connexion...<br>";
        echo "DSN: $dsn<br>";
        echo "User: {$config['username']}<br>";
        
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        
        echo "✅ <strong>CONNEXION RÉUSSIE !</strong><br>";
        
        // Test d'une requête simple
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "✅ Tables trouvées: " . count($tables) . "<br>";
        if (count($tables) > 0) {
            echo "📋 Tables: " . implode(', ', $tables) . "<br>";
        }
        
        // Vérifier si la table users existe
        if (in_array('users', $tables)) {
            echo "✅ Table 'users' trouvée<br>";
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            echo "✅ Nombre d'utilisateurs: " . $result['count'] . "<br>";
        } else {
            echo "⚠️ Table 'users' non trouvée - Base de données vide ?<br>";
        }
        
        echo "<hr>";
        break; // Arrêter au premier succès
        
    } catch (PDOException $e) {
        echo "❌ Erreur: " . $e->getMessage() . "<br>";
        echo "<hr>";
    }
}

echo "<h2>🔧 Actions Recommandées</h2>";
echo "<ul>";
echo "<li><strong>Si aucune config ne fonctionne :</strong> Vérifiez vos paramètres dans le panneau d'hébergement</li>";
echo "<li><strong>Si la connexion réussit mais pas de tables :</strong> Importez votre base de données</li>";
echo "<li><strong>Si tout fonctionne :</strong> Mettez à jour config/database.php avec les bons paramètres</li>";
echo "</ul>";
?>
