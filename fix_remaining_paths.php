<?php
/**
 * Script pour corriger les chemins restants (version sécurisée)
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>🔧 Vérification Finale des Chemins</h1>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Vérifier la structure de la table documents_generes
    echo "<h2>1. Vérification de la table documents_generes</h2>";
    $stmt = $pdo->query("DESCRIBE documents_generes");
    $columns = $stmt->fetchAll();
    
    $hasCheminFichier = false;
    echo "Colonnes dans documents_generes:<br>";
    foreach ($columns as $col) {
        echo "- {$col['Field']}<br>";
        if ($col['Field'] === 'chemin_fichier') {
            $hasCheminFichier = true;
        }
    }
    
    if ($hasCheminFichier) {
        echo "✅ Colonne chemin_fichier trouvée<br>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM documents_generes WHERE chemin_fichier LIKE '%digidocs%'");
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            echo "⚠️ {$result['count']} entrées à corriger dans documents_generes<br>";
            // Correction ici si nécessaire
        } else {
            echo "✅ Aucune correction nécessaire dans documents_generes<br>";
        }
    } else {
        echo "ℹ️ Pas de colonne chemin_fichier dans documents_generes (normal)<br>";
    }
    
    // Test final des documents principaux
    echo "<h2>2. Test d'accès aux documents corrigés</h2>";
    $stmt = $pdo->query("SELECT id, nom_original, chemin_fichier FROM documents ORDER BY id LIMIT 5");
    $docs = $stmt->fetchAll();
    
    foreach ($docs as $doc) {
        $exists = file_exists($doc['chemin_fichier']);
        $status = $exists ? "✅" : "❌";
        echo "$status ID {$doc['id']}: {$doc['nom_original']}<br>";
        if (!$exists) {
            echo "   Chemin: {$doc['chemin_fichier']}<br>";
        }
    }
    
    echo "<h2>✅ Vérification Terminée</h2>";
    echo "<p><strong>Testez maintenant vos documents :</strong></p>";
    echo "<ul>";
    foreach ($docs as $doc) {
        echo "<li><a href='https://ged.teranganumerique.com/documents/simple_viewer.php?id={$doc['id']}' target='_blank'>{$doc['nom_original']}</a></li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}
?>
