<?php
/**
 * Script pour corriger les chemins de fichiers après migration
 * Remplace les anciens chemins avec /digidocs/ par les nouveaux chemins
 */

require_once __DIR__ . '/config/config.php';

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Correction des Chemins de Fichiers</h1>";
echo "<p>Mise à jour des chemins après suppression du dossier digidocs</p>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // 1. Vérifier les chemins actuels dans la table documents
    echo "<h2>1. Analyse des chemins actuels</h2>";
    $stmt = $pdo->query("SELECT id, nom_original, chemin_fichier FROM documents LIMIT 10");
    $documents = $stmt->fetchAll();
    
    $needsUpdate = 0;
    foreach ($documents as $doc) {
        if (strpos($doc['chemin_fichier'], 'digidocs') !== false) {
            $needsUpdate++;
            echo "❌ ID {$doc['id']}: {$doc['chemin_fichier']}<br>";
        } else {
            echo "✅ ID {$doc['id']}: {$doc['chemin_fichier']}<br>";
        }
    }
    
    echo "<p><strong>Documents nécessitant une correction: $needsUpdate</strong></p>";
    
    if ($needsUpdate > 0) {
        echo "<h2>2. Correction des chemins</h2>";
        
        // Patterns à remplacer
        $patterns = [
            'C:\\xampp\\htdocs\\document\\digidocs\\' => 'C:\\xampp\\htdocs\\document\\',
            'C:\\xampp\\htdocs\\document\\digidocs/' => 'C:\\xampp\\htdocs\\document/',
            '/home/u588247422/domains/ged.teranganumerique.com/public_html/digidocs/' => '/home/u588247422/domains/ged.teranganumerique.com/public_html/',
            'digidocs/uploads/' => 'uploads/',
            'digidocs\\uploads\\' => 'uploads\\',
        ];
        
        foreach ($patterns as $oldPattern => $newPattern) {
            $stmt = $pdo->prepare("
                UPDATE documents 
                SET chemin_fichier = REPLACE(chemin_fichier, ?, ?)
                WHERE chemin_fichier LIKE ?
            ");
            
            $likePattern = '%' . $oldPattern . '%';
            $stmt->execute([$oldPattern, $newPattern, $likePattern]);
            $affected = $stmt->rowCount();
            
            if ($affected > 0) {
                echo "✅ Remplacé '$oldPattern' → '$newPattern' dans $affected documents<br>";
            }
        }
        
        echo "<h2>3. Vérification après correction</h2>";
        $stmt = $pdo->query("SELECT id, nom_original, chemin_fichier FROM documents WHERE chemin_fichier LIKE '%digidocs%'");
        $remaining = $stmt->fetchAll();
        
        if (count($remaining) == 0) {
            echo "✅ <strong>Tous les chemins ont été corrigés !</strong><br>";
        } else {
            echo "⚠️ Chemins restants à corriger:<br>";
            foreach ($remaining as $doc) {
                echo "- ID {$doc['id']}: {$doc['chemin_fichier']}<br>";
            }
        }
    }
    
    // 4. Vérifier aussi les autres tables qui pourraient contenir des chemins
    echo "<h2>4. Vérification des autres tables</h2>";
    
    // Table documents_generes
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM documents_generes WHERE chemin_fichier LIKE '%digidocs%'");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        echo "⚠️ Table documents_generes: {$result['count']} entrées à corriger<br>";
        
        // Corriger documents_generes
        foreach ($patterns as $oldPattern => $newPattern) {
            $stmt = $pdo->prepare("
                UPDATE documents_generes 
                SET chemin_fichier = REPLACE(chemin_fichier, ?, ?)
                WHERE chemin_fichier LIKE ?
            ");
            
            $likePattern = '%' . $oldPattern . '%';
            $stmt->execute([$oldPattern, $newPattern, $likePattern]);
        }
        echo "✅ Table documents_generes corrigée<br>";
    } else {
        echo "✅ Table documents_generes: OK<br>";
    }
    
    echo "<h2>5. Test d'accès aux fichiers</h2>";
    
    // Tester quelques fichiers
    $stmt = $pdo->query("SELECT id, nom_original, chemin_fichier FROM documents LIMIT 5");
    $testDocs = $stmt->fetchAll();
    
    foreach ($testDocs as $doc) {
        if (file_exists($doc['chemin_fichier'])) {
            echo "✅ Fichier accessible: {$doc['nom_original']}<br>";
        } else {
            echo "❌ Fichier non trouvé: {$doc['nom_original']} → {$doc['chemin_fichier']}<br>";
        }
    }
    
    echo "<h2>✅ Correction Terminée</h2>";
    echo "<p><strong>Vous pouvez maintenant tester:</strong></p>";
    echo "<ul>";
    echo "<li><a href='https://ged.teranganumerique.com/documents/list.php'>Liste des documents</a></li>";
    echo "<li><a href='https://ged.teranganumerique.com/documents/simple_viewer.php?id=3'>Viewer document ID 3</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
    echo "Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "<br>";
}
?>
