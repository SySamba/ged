<?php
/**
 * Script de migration pour le système d'archivage
 * Exécute la migration SQL et met à jour les permissions utilisateurs
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>🗄️ Migration du Système d'Archivage</h1>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "<h2>📋 Vérification des prérequis...</h2>";
    
    // Vérifier si la migration a déjà été effectuée
    $stmt = $pdo->query("SHOW COLUMNS FROM documents LIKE 'statut'");
    if ($stmt->fetch()) {
        echo "<div style='color: orange;'>⚠️ La migration semble déjà avoir été effectuée.</div>";
        echo "<p>Voulez-vous continuer quand même ? <a href='?force=1'>Forcer la migration</a></p>";
        
        if (!isset($_GET['force'])) {
            exit;
        }
    }
    
    echo "✅ Prérequis validés<br>";
    
    echo "<h2>🔄 Exécution de la migration SQL...</h2>";
    
    // Lire et exécuter le fichier SQL
    $sqlFile = __DIR__ . '/database/add_archiving_system.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Fichier SQL de migration introuvable: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Diviser le SQL en requêtes individuelles
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($queries as $query) {
        if (empty($query) || strpos($query, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($query);
            $successCount++;
            echo "✅ Requête exécutée avec succès<br>";
        } catch (Exception $e) {
            $errorCount++;
            echo "<div style='color: red;'>❌ Erreur: " . $e->getMessage() . "</div>";
            echo "<div style='color: gray; font-size: 0.9em;'>Requête: " . substr($query, 0, 100) . "...</div>";
        }
    }
    
    echo "<h2>👥 Mise à jour des permissions utilisateurs...</h2>";
    
    // Mettre à jour les permissions pour inclure l'archivage
    $stmt = $pdo->query("SELECT id, permissions FROM users WHERE permissions IS NOT NULL");
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $permissions = json_decode($user['permissions'], true);
        
        if (isset($permissions['documents'])) {
            // Ajouter les permissions d'archivage
            $permissions['documents']['archive'] = $permissions['documents']['delete'] ?? false;
            $permissions['documents']['unarchive'] = $permissions['documents']['update'] ?? false;
            
            $newPermissions = json_encode($permissions);
            
            $updateStmt = $pdo->prepare("UPDATE users SET permissions = ? WHERE id = ?");
            $updateStmt->execute([$newPermissions, $user['id']]);
            
            echo "✅ Permissions mises à jour pour l'utilisateur ID {$user['id']}<br>";
        }
    }
    
    echo "<h2>📊 Résumé de la migration</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>✅ Migration terminée avec succès !</strong><br>";
    echo "Requêtes réussies: $successCount<br>";
    echo "Erreurs: $errorCount<br>";
    echo "</div>";
    
    echo "<h2>🎯 Nouvelles fonctionnalités disponibles</h2>";
    echo "<ul>";
    echo "<li>✅ <strong>Statuts de documents</strong> : Actif, Archivé, Supprimé</li>";
    echo "<li>✅ <strong>Archivage manuel</strong> avec raison</li>";
    echo "<li>✅ <strong>Règles d'archivage automatique</strong> par catégorie</li>";
    echo "<li>✅ <strong>Historique des actions</strong> d'archivage</li>";
    echo "<li>✅ <strong>Notifications</strong> d'archivage</li>";
    echo "<li>✅ <strong>Vues optimisées</strong> pour les différents statuts</li>";
    echo "<li>✅ <strong>Triggers automatiques</strong> pour l'historique</li>";
    echo "</ul>";
    
    echo "<h2>📋 Règles d'archivage par défaut créées</h2>";
    $stmt = $pdo->query("SELECT * FROM regles_archivage ORDER BY id");
    $regles = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='padding: 8px;'>Nom</th>";
    echo "<th style='padding: 8px;'>Catégorie</th>";
    echo "<th style='padding: 8px;'>Archivage après</th>";
    echo "<th style='padding: 8px;'>Suppression après</th>";
    echo "<th style='padding: 8px;'>Statut</th>";
    echo "</tr>";
    
    foreach ($regles as $regle) {
        $categorie = $regle['categorie_id'] ? 
            $pdo->query("SELECT nom FROM categories WHERE id = {$regle['categorie_id']}")->fetchColumn() : 
            'Toutes';
        
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$regle['nom']}</td>";
        echo "<td style='padding: 8px;'>$categorie</td>";
        echo "<td style='padding: 8px;'>{$regle['duree_avant_archivage']} jours</td>";
        echo "<td style='padding: 8px;'>" . ($regle['duree_avant_suppression'] ? $regle['duree_avant_suppression'] . ' jours' : 'Jamais') . "</td>";
        echo "<td style='padding: 8px;'>" . ($regle['actif'] ? '✅ Actif' : '❌ Inactif') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🚀 Prochaines étapes</h2>";
    echo "<ol>";
    echo "<li>Tester les nouvelles fonctionnalités d'archivage</li>";
    echo "<li>Configurer les règles d'archivage selon vos besoins</li>";
    echo "<li>Former les utilisateurs aux nouvelles fonctionnalités</li>";
    echo "<li>Planifier l'archivage automatique (cron job)</li>";
    echo "</ol>";
    
    echo "<div style='background: #cce5ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>💡 Conseil :</strong> Vous pouvez maintenant accéder aux nouvelles fonctionnalités d'archivage ";
    echo "dans la section Documents de votre interface d'administration.";
    echo "</div>";
    
    echo "<p><a href='" . APP_URL . "/documents/list.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Aller aux Documents</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ Erreur lors de la migration :</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
    
    echo "<h3>🔧 Actions de dépannage :</h3>";
    echo "<ol>";
    echo "<li>Vérifiez que la base de données est accessible</li>";
    echo "<li>Vérifiez que l'utilisateur a les droits ALTER TABLE</li>";
    echo "<li>Consultez les logs d'erreur du serveur</li>";
    echo "<li>Contactez l'administrateur système si nécessaire</li>";
    echo "</ol>";
}
?>
