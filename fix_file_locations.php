<?php
/**
 * Script pour corriger les emplacements de fichiers
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>🔧 Correction des Emplacements de Fichiers</h1>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $basePath = __DIR__;
    $corrected = 0;
    $notFound = 0;
    
    echo "<h2>Analyse et correction...</h2>";
    
    $stmt = $pdo->query("SELECT id, nom_original, chemin_fichier FROM documents");
    $documents = $stmt->fetchAll();
    
    foreach ($documents as $doc) {
        echo "Document ID {$doc['id']}: {$doc['nom_original']}<br>";
        
        // Si le fichier existe déjà au bon endroit, continuer
        if (file_exists($doc['chemin_fichier'])) {
            echo "✅ OK<br>";
            continue;
        }
        
        // Chercher le fichier dans différents emplacements
        $filename = basename($doc['chemin_fichier']);
        $possiblePaths = [
            $basePath . '/uploads/documents/' . $filename,
            $basePath . '/digidocs/uploads/documents/' . $filename,
            $basePath . '/uploads/' . $filename,
        ];
        
        $found = false;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                echo "✅ Trouvé à: $path<br>";
                
                // Déterminer le nouveau chemin correct
                $newPath = $basePath . '/uploads/documents/' . $filename;
                
                // Si le fichier n'est pas au bon endroit, le déplacer
                if ($path !== $newPath) {
                    // Créer le dossier de destination si nécessaire
                    $destDir = dirname($newPath);
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    
                    if (copy($path, $newPath)) {
                        echo "📁 Fichier déplacé vers: $newPath<br>";
                        unlink($path); // Supprimer l'ancien fichier
                    } else {
                        echo "❌ Erreur lors du déplacement<br>";
                        continue;
                    }
                }
                
                // Mettre à jour la base de données
                $updateStmt = $pdo->prepare("UPDATE documents SET chemin_fichier = ? WHERE id = ?");
                $updateStmt->execute([$newPath, $doc['id']]);
                
                echo "✅ Base de données mise à jour<br>";
                $corrected++;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo "❌ Fichier introuvable<br>";
            $notFound++;
        }
        
        echo "<hr>";
    }
    
    echo "<h2>✅ Résumé</h2>";
    echo "Documents corrigés: $corrected<br>";
    echo "Documents non trouvés: $notFound<br>";
    
    if ($corrected > 0) {
        echo "<p><strong>✅ Correction terminée ! Testez maintenant vos documents.</strong></p>";
    }
    
    if ($notFound > 0) {
        echo "<p><strong>⚠️ Certains fichiers sont perdus et devront être re-uploadés.</strong></p>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}
?>
