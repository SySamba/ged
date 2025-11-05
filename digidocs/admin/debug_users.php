<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Vérifier les permissions admin
if (!hasPermission('users', 'read')) {
    $_SESSION['error'] = 'Permission insuffisante';
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$user = new User();
$users = $user->getAll();

echo "<h2>Debug - Liste des utilisateurs</h2>";

echo "<h3>Nombre total d'utilisateurs : " . count($users) . "</h3>";

if (empty($users)) {
    echo "<p style='color: red;'>Aucun utilisateur trouvé dans la base de données !</p>";
    
    // Vérifier la connexion à la base
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<p style='color: green;'>Connexion à la base de données : OK</p>";
        
        // Vérifier si la table users existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>Table 'users' existe : OK</p>";
            
            // Compter les utilisateurs directement
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $count = $stmt->fetch()['count'];
            echo "<p><strong>Nombre d'utilisateurs en base :</strong> $count</p>";
            
            if ($count > 0) {
                // Afficher tous les utilisateurs
                $stmt = $pdo->query("SELECT * FROM users");
                $allUsers = $stmt->fetchAll();
                
                echo "<h4>Utilisateurs en base :</h4>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Date création</th></tr>";
                
                foreach ($allUsers as $u) {
                    echo "<tr>";
                    echo "<td>" . $u['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($u['nom']) . "</td>";
                    echo "<td>" . htmlspecialchars($u['prenom']) . "</td>";
                    echo "<td>" . htmlspecialchars($u['email']) . "</td>";
                    echo "<td>" . $u['role'] . "</td>";
                    echo "<td>" . $u['statut'] . "</td>";
                    echo "<td>" . $u['date_creation'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color: red;'>Table 'users' n'existe pas !</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Erreur de connexion : " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h4>Utilisateurs trouvés via la classe User :</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Date création</th></tr>";
    
    foreach ($users as $u) {
        echo "<tr>";
        echo "<td>" . $u['id'] . "</td>";
        echo "<td>" . htmlspecialchars($u['nom']) . "</td>";
        echo "<td>" . htmlspecialchars($u['prenom']) . "</td>";
        echo "<td>" . htmlspecialchars($u['email']) . "</td>";
        echo "<td>" . $u['role'] . "</td>";
        echo "<td>" . $u['statut'] . "</td>";
        echo "<td>" . $u['date_creation'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Liens de test :</h3>";
echo "<p><a href='" . APP_URL . "/profile.php'>Tester profile.php</a></p>";
echo "<p><a href='" . APP_URL . "/settings.php'>Tester settings.php</a></p>";
echo "<p><a href='" . APP_URL . "/admin/users.php'>Page admin des utilisateurs</a></p>";
?>
