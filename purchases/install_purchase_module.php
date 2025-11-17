<?php
/**
 * Script d'installation du module de gestion des achats
 */

require_once '../config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<h2>Installation du module de gestion des achats</h2>";
    echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px;'>";
    
    // Lire et exécuter le fichier SQL
    $sql_file = '../database/purchase_cycle_schema.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("Fichier SQL introuvable: " . $sql_file);
    }
    
    echo "<p><strong>Lecture du fichier SQL...</strong></p>";
    $sql_content = file_get_contents($sql_file);
    
    // Diviser les requêtes SQL
    $statements = explode(';', $sql_content);
    
    echo "<p><strong>Exécution des requêtes SQL...</strong></p>";
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
                $executed++;
                echo "<span style='color: green;'>✓</span> Requête exécutée avec succès<br>";
            } catch (PDOException $e) {
                $errors++;
                echo "<span style='color: red;'>✗</span> Erreur: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Résumé de l'installation</h3>";
    echo "<p><strong>Requêtes exécutées:</strong> $executed</p>";
    echo "<p><strong>Erreurs:</strong> $errors</p>";
    
    if ($errors === 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>✅ Installation réussie !</h4>";
        echo "<p>Le module de gestion des achats a été installé avec succès.</p>";
        echo "<p><strong>Fonctionnalités disponibles:</strong></p>";
        echo "<ul>";
        echo "<li>Gestion des fournisseurs</li>";
        echo "<li>Demandes d'achat</li>";
        echo "<li>Bons de commande</li>";
        echo "<li>Réceptions de marchandises</li>";
        echo "<li>Gestion des factures</li>";
        echo "<li>Écritures comptables</li>";
        echo "<li>Workflow d'approbation</li>";
        echo "<li>Pièces jointes</li>";
        echo "</ul>";
        echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Accéder au module</a></p>";
        echo "</div>";
        
        // Créer un utilisateur admin pour les achats si nécessaire
        try {
            $check_admin = "SELECT COUNT(*) as count FROM users WHERE role = 'admin'";
            $stmt = $pdo->prepare($check_admin);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h4>⚠️ Attention</h4>";
                echo "<p>Aucun utilisateur administrateur détecté. Assurez-vous d'avoir un compte administrateur pour gérer les approbations.</p>";
                echo "</div>";
            }
        } catch (Exception $e) {
            // Ignorer les erreurs de vérification d'admin
        }
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>❌ Installation partiellement réussie</h4>";
        echo "<p>Certaines erreurs ont été rencontrées. Vérifiez les messages ci-dessus.</p>";
        echo "<p>Le module peut fonctionner partiellement. Contactez votre administrateur système si nécessaire.</p>";
        echo "</div>";
    }
    
    // Afficher la structure des tables créées
    echo "<h3>Tables créées</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    
    $tables = [
        'suppliers' => 'Fournisseurs',
        'purchase_categories' => 'Catégories d\'achat',
        'purchase_requests' => 'Demandes d\'achat',
        'purchase_request_items' => 'Articles de demande',
        'purchase_orders' => 'Bons de commande',
        'purchase_order_items' => 'Articles de commande',
        'purchase_receipts' => 'Réceptions',
        'purchase_receipt_items' => 'Articles reçus',
        'purchase_invoices' => 'Factures',
        'purchase_invoice_items' => 'Articles de facture',
        'accounting_entries' => 'Écritures comptables',
        'accounting_entry_lines' => 'Lignes d\'écriture',
        'approval_workflows' => 'Workflows d\'approbation',
        'purchase_attachments' => 'Pièces jointes'
    ];
    
    foreach ($tables as $table => $description) {
        try {
            $check_table = "SHOW TABLES LIKE '$table'";
            $stmt = $pdo->prepare($check_table);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                echo "<span style='color: green;'>✓</span> $table ($description)<br>";
            } else {
                echo "<span style='color: red;'>✗</span> $table ($description) - Non créée<br>";
            }
        } catch (Exception $e) {
            echo "<span style='color: orange;'>?</span> $table ($description) - Vérification impossible<br>";
        }
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Erreur d'installation</h4>";
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez la configuration de votre base de données et réessayez.</p>";
    echo "</div>";
}

echo "</div>";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation du module d'achat - DigiDocs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Le contenu PHP est affiché ici -->
    </div>
</body>
</html>
