<?php
/**
 * Script pour corriger le mot de passe administrateur
 */

require_once __DIR__ . '/config/config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = 'sambasy837@gmail.com'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Utilisateur trouvé : " . $user['email'] . "<br>";
        echo "🔑 Hash actuel : " . substr($user['password'], 0, 20) . "...<br><br>";
        
        // Tester le mot de passe actuel
        if (password_verify('Touba2021@', $user['password'])) {
            echo "✅ Le mot de passe 'Touba2021@' fonctionne déjà !<br>";
        } else {
            echo "❌ Le mot de passe ne fonctionne pas. Correction en cours...<br>";
            
            // Corriger le mot de passe
            $newHash = password_hash('Touba2021@', PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'sambasy837@gmail.com'");
            $result = $updateStmt->execute([$newHash]);
            
            if ($result) {
                echo "✅ Mot de passe corrigé avec succès !<br>";
                echo "🔑 Nouveau hash : " . substr($newHash, 0, 20) . "...<br>";
            } else {
                echo "❌ Erreur lors de la correction.<br>";
            }
        }
    } else {
        echo "❌ Utilisateur non trouvé. Création en cours...<br>";
        
        // Créer l'utilisateur admin
        $hashedPassword = password_hash('Touba2021@', PASSWORD_DEFAULT);
        $permissions = json_encode([
            'documents' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
            'users' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
            'offres' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
            'modeles' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true]
        ]);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (nom, prenom, email, password, role, permissions, actif) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            'SAMBA',
            'SY', 
            'sambasy837@gmail.com',
            $hashedPassword,
            'admin',
            $permissions,
            1
        ]);
        
        if ($result) {
            echo "✅ Utilisateur administrateur créé avec succès !<br>";
        } else {
            echo "❌ Erreur lors de la création de l'utilisateur.<br>";
        }
    }
    
    echo "<br>🔗 <a href='auth/login.php'>Tester la connexion</a><br>";
    echo "<br>📧 Email : sambasy837@gmail.com<br>";
    echo "🔑 Mot de passe : Touba2021@<br>";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction mot de passe - DigiDocs</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 600px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔧 Correction du mot de passe administrateur</h2>
        <hr>
    </div>
</body>
</html>
