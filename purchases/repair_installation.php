<?php
/**
 * Script de réparation de l'installation du module de gestion des achats
 */

require_once '../config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<h2>Réparation de l'installation du module de gestion des achats</h2>";
    echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px;'>";
    
    // Étape 1: Supprimer les index existants pour éviter les conflits
    echo "<h3>Étape 1: Nettoyage des index existants</h3>";
    
    $indexes_to_drop = [
        'idx_purchase_requests_status',
        'idx_purchase_orders_status', 
        'idx_purchase_invoices_status',
        'idx_purchase_requests_requester',
        'idx_purchase_orders_supplier',
        'idx_approval_workflows_document'
    ];
    
    foreach ($indexes_to_drop as $index) {
        try {
            // Vérifier si l'index existe avant de le supprimer
            $check_query = "SHOW INDEX FROM purchase_requests WHERE Key_name = '$index'";
            if (strpos($index, 'purchase_orders') !== false) {
                $check_query = "SHOW INDEX FROM purchase_orders WHERE Key_name = '$index'";
            } elseif (strpos($index, 'purchase_invoices') !== false) {
                $check_query = "SHOW INDEX FROM purchase_invoices WHERE Key_name = '$index'";
            } elseif (strpos($index, 'approval_workflows') !== false) {
                $check_query = "SHOW INDEX FROM approval_workflows WHERE Key_name = '$index'";
            }
            
            $stmt = $pdo->prepare($check_query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $table = 'purchase_requests';
                if (strpos($index, 'purchase_orders') !== false) {
                    $table = 'purchase_orders';
                } elseif (strpos($index, 'purchase_invoices') !== false) {
                    $table = 'purchase_invoices';
                } elseif (strpos($index, 'approval_workflows') !== false) {
                    $table = 'approval_workflows';
                }
                
                $drop_query = "DROP INDEX $index ON $table";
                $pdo->exec($drop_query);
                echo "<span style='color: orange;'>🔧</span> Index $index supprimé<br>";
            } else {
                echo "<span style='color: green;'>✓</span> Index $index n'existe pas<br>";
            }
        } catch (Exception $e) {
            echo "<span style='color: blue;'>ℹ</span> Index $index: " . $e->getMessage() . "<br>";
        }
    }
    
    // Étape 2: Réexécuter le script SQL corrigé
    echo "<h3>Étape 2: Réinstallation des index</h3>";
    
    $sql_file = '../database/purchase_cycle_schema.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("Fichier SQL introuvable: " . $sql_file);
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Extraire seulement les commandes CREATE INDEX
    $lines = explode("\n", $sql_content);
    $index_commands = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'CREATE INDEX IF NOT EXISTS') === 0) {
            $index_commands[] = $line;
        }
    }
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($index_commands as $command) {
        try {
            $pdo->exec($command);
            $success_count++;
            echo "<span style='color: green;'>✓</span> Index créé avec succès<br>";
        } catch (Exception $e) {
            $error_count++;
            echo "<span style='color: red;'>✗</span> Erreur: " . $e->getMessage() . "<br>";
        }
    }
    
    // Étape 3: Vérifier les catégories par défaut
    echo "<h3>Étape 3: Vérification des catégories par défaut</h3>";
    
    $categories_query = "SELECT COUNT(*) as count FROM purchase_categories";
    $stmt = $pdo->prepare($categories_query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "<span style='color: orange;'>⚠</span> Aucune catégorie trouvée, insertion des catégories par défaut...<br>";
        
        $insert_categories = "INSERT IGNORE INTO purchase_categories (name, description, budget_code) VALUES
            ('Fournitures de bureau', 'Papeterie, consommables bureau', 'FB001'),
            ('Matériel informatique', 'Ordinateurs, périphériques, logiciels', 'IT001'),
            ('Mobilier', 'Bureaux, chaises, mobilier de bureau', 'MOB001'),
            ('Services', 'Prestations de service, consulting', 'SRV001'),
            ('Maintenance', 'Maintenance équipements, réparations', 'MNT001'),
            ('Formation', 'Formations professionnelles', 'FOR001'),
            ('Déplacements', 'Frais de mission, transport', 'DEP001'),
            ('Télécommunications', 'Téléphonie, internet, communications', 'TEL001')";
        
        try {
            $pdo->exec($insert_categories);
            echo "<span style='color: green;'>✓</span> Catégories par défaut insérées avec succès<br>";
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗</span> Erreur lors de l'insertion des catégories: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "<span style='color: green;'>✓</span> {$result['count']} catégories trouvées<br>";
    }
    
    // Résumé final
    echo "<hr>";
    echo "<h3>Résumé de la réparation</h3>";
    echo "<p><strong>Index créés avec succès:</strong> $success_count</p>";
    echo "<p><strong>Erreurs:</strong> $error_count</p>";
    
    if ($error_count === 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>✅ Réparation réussie !</h4>";
        echo "<p>Le module de gestion des achats a été réparé avec succès.</p>";
        echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Accéder au module</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>⚠️ Réparation partiellement réussie</h4>";
        echo "<p>Certaines erreurs persistent. Le module devrait néanmoins fonctionner correctement.</p>";
        echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Tester le module</a></p>";
        echo "</div>";
    }
    
    // Test de connectivité des tables
    echo "<h3>Test de connectivité des tables</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    
    $tables_to_test = [
        'suppliers' => 'SELECT COUNT(*) as count FROM suppliers',
        'purchase_categories' => 'SELECT COUNT(*) as count FROM purchase_categories',
        'purchase_requests' => 'SELECT COUNT(*) as count FROM purchase_requests',
        'purchase_orders' => 'SELECT COUNT(*) as count FROM purchase_orders'
    ];
    
    foreach ($tables_to_test as $table => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<span style='color: green;'>✓</span> $table: {$result['count']} enregistrements<br>";
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗</span> $table: Erreur - " . $e->getMessage() . "<br>";
        }
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Erreur de réparation</h4>";
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réparation du module d'achat - DigiDocs</title>
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
