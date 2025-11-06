<?php
/**
 * Script de migration pour ajouter le système d'archivage
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>🗃️ Migration du Système d'Archivage</h1>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "<h2>Vérification de l'état actuel...</h2>";
    
    // Vérifier si la colonne statut existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM documents LIKE 'statut'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='alert alert-warning'>⚠️ Le système d'archivage semble déjà installé.</div>";
        
        // Afficher les statistiques actuelles
        $stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM documents GROUP BY statut");
        $stats = $stmt->fetchAll();
        
        echo "<h3>Statistiques actuelles :</h3>";
        echo "<ul>";
        foreach ($stats as $stat) {
            echo "<li><strong>" . ucfirst($stat['statut']) . "</strong> : " . $stat['count'] . " documents</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<div class='alert alert-info'>ℹ️ Installation du système d'archivage...</div>";
        
        // Lire et exécuter le script SQL
        $sqlFile = __DIR__ . '/database/add_archiving_system.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("Fichier SQL de migration introuvable : $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Supprimer les commentaires et diviser en requêtes
        $sql = preg_replace('/--.*$/m', '', $sql);
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        $successCount = 0;
        $errorCount = 0;
        
        echo "<h3>Exécution des requêtes...</h3>";
        echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f8f9fa;'>";
        
        foreach ($queries as $query) {
            if (empty($query) || strtoupper(trim($query)) === 'USE DIGIDOCS' || strtoupper(trim($query)) === 'COMMIT') {
                continue;
            }
            
            try {
                $pdo->exec($query);
                echo "<div style='color: green;'>✅ " . substr($query, 0, 80) . "...</div>";
                $successCount++;
            } catch (Exception $e) {
                echo "<div style='color: red;'>❌ Erreur : " . $e->getMessage() . "</div>";
                echo "<div style='color: #666; font-size: 0.9em; margin-left: 20px;'>" . substr($query, 0, 100) . "...</div>";
                $errorCount++;
            }
        }
        
        echo "</div>";
        
        echo "<h3>Résultats de la migration :</h3>";
        echo "<ul>";
        echo "<li><span style='color: green;'>✅ Requêtes réussies : $successCount</span></li>";
        echo "<li><span style='color: red;'>❌ Erreurs : $errorCount</span></li>";
        echo "</ul>";
        
        if ($errorCount === 0) {
            echo "<div class='alert alert-success'>🎉 <strong>Migration réussie !</strong> Le système d'archivage est maintenant disponible.</div>";
            
            // Vérifier l'installation
            echo "<h3>Vérification de l'installation :</h3>";
            
            // Vérifier les nouvelles colonnes
            $stmt = $pdo->query("SHOW COLUMNS FROM documents WHERE Field IN ('statut', 'date_archivage', 'motif_archivage', 'archive_par')");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<ul>";
            foreach (['statut', 'date_archivage', 'motif_archivage', 'archive_par'] as $expectedColumn) {
                if (in_array($expectedColumn, $columns)) {
                    echo "<li>✅ Colonne '$expectedColumn' ajoutée</li>";
                } else {
                    echo "<li>❌ Colonne '$expectedColumn' manquante</li>";
                }
            }
            echo "</ul>";
            
            // Vérifier les nouvelles tables
            $stmt = $pdo->query("SHOW TABLES LIKE 'regles_archivage'");
            if ($stmt->rowCount() > 0) {
                echo "<p>✅ Table 'regles_archivage' créée</p>";
                
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM regles_archivage");
                $count = $stmt->fetch()['count'];
                echo "<p>📋 $count règles d'archivage par défaut installées</p>";
            }
            
            $stmt = $pdo->query("SHOW TABLES LIKE 'historique_archivage'");
            if ($stmt->rowCount() > 0) {
                echo "<p>✅ Table 'historique_archivage' créée</p>";
            }
            
            // Statistiques des documents
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM documents WHERE statut = 'actif'");
            $total = $stmt->fetch()['total'];
            echo "<p>📊 $total documents marqués comme 'actif'</p>";
            
        } else {
            echo "<div class='alert alert-danger'>❌ <strong>Migration échouée</strong> avec $errorCount erreurs. Veuillez corriger les problèmes et relancer.</div>";
        }
    }
    
    echo "<h2>Prochaines étapes :</h2>";
    echo "<ol>";
    echo "<li>✅ Mettre à jour les permissions utilisateurs pour inclure l'archivage</li>";
    echo "<li>✅ Tester les nouvelles fonctionnalités d'archivage</li>";
    echo "<li>✅ Configurer les règles d'archivage automatique selon vos besoins</li>";
    echo "<li>✅ Former les utilisateurs aux nouvelles fonctionnalités</li>";
    echo "</ol>";
    
    echo "<div style='margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 5px;'>";
    echo "<h3>🔗 Liens utiles :</h3>";
    echo "<ul>";
    echo "<li><a href='" . APP_URL . "/documents/list.php'>📁 Liste des documents</a></li>";
    echo "<li><a href='" . APP_URL . "/documents/archives.php'>🗃️ Documents archivés</a> (à créer)</li>";
    echo "<li><a href='" . APP_URL . "/admin/archiving_rules.php'>⚙️ Gestion des règles d'archivage</a> (à créer)</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ <strong>Erreur critique :</strong> " . $e->getMessage() . "</div>";
    echo "<p>Veuillez vérifier :</p>";
    echo "<ul>";
    echo "<li>La connexion à la base de données</li>";
    echo "<li>Les permissions sur la base de données</li>";
    echo "<li>La présence du fichier SQL de migration</li>";
    echo "</ul>";
}
?>

<style>
.alert {
    padding: 15px;
    margin: 20px 0;
    border-radius: 5px;
}
.alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
.alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
.alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
</style>
