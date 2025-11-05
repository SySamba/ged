<?php
/**
 * Script de réinitialisation du mot de passe administrateur
 * À utiliser une seule fois pour corriger le problème de connexion
 */

require_once __DIR__ . '/config/config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Hacher correctement le mot de passe "Touba2021@"
    $hashedPassword = password_hash('Touba2021@', PASSWORD_DEFAULT);
    
    // Mettre à jour le mot de passe de l'admin
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'sambasy837@gmail.com'");
    $result = $stmt->execute([$hashedPassword]);
    
    if ($result) {
        echo "✅ Mot de passe administrateur mis à jour avec succès !<br>";
        echo "📧 Email : sambasy837@gmail.com<br>";
        echo "🔑 Mot de passe : Touba2021@<br><br>";
        echo "🔗 <a href='" . APP_URL . "/auth/login.php'>Se connecter maintenant</a><br><br>";
        echo "⚠️ <strong>Important :</strong> Supprimez ce fichier après utilisation pour des raisons de sécurité.";
    } else {
        echo "❌ Erreur lors de la mise à jour du mot de passe.";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation mot de passe - DigiDocs</title>
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
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔧 Réinitialisation du mot de passe administrateur</h2>
        <p>Ce script corrige le problème de connexion en mettant à jour le hash du mot de passe.</p>
        <hr>
        <!-- Le résultat PHP s'affiche ici -->
    </div>
</body>
</html>
