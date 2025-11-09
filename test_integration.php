<?php
/**
 * Script de test pour vérifier l'intégration des documents générés
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Simuler une session utilisateur pour les tests
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // ID utilisateur de test
    $_SESSION['user_role'] = 'admin';
}

try {
    echo "<h1>Test d'intégration - Documents générés</h1>\n";
    
    $template = new Template();
    $document = new Document();
    
    // Test 1: Vérifier que les modèles existent
    echo "<h2>1. Vérification des modèles disponibles</h2>\n";
    $contrats = $template->getAll('contrat');
    $factures = $template->getAll('facture');
    $bonsCommande = $template->getAll('bon_commande');
    
    echo "Contrats disponibles: " . count($contrats) . "<br>\n";
    echo "Factures disponibles: " . count($factures) . "<br>\n";
    echo "Bons de commande disponibles: " . count($bonsCommande) . "<br>\n";
    
    // Test 2: Simuler la génération d'un document
    if (!empty($contrats)) {
        echo "<h2>2. Test de génération d'un contrat</h2>\n";
        
        $testData = [
            'nom_document' => 'Contrat_Test_' . date('Y-m-d_H-i-s'),
            'nom_employe' => 'Jean Dupont',
            'prenom_employe' => 'Jean',
            'poste' => 'Développeur',
            'salaire' => '500000',
            'date_debut' => date('Y-m-d'),
            'type_contrat' => 'indéterminée',
            'generate_pdf' => false
        ];
        
        $result = $template->generateDocument($contrats[0]['id'], $testData);
        
        if ($result['success']) {
            echo "✓ Document généré avec succès<br>\n";
            echo "ID du document généré: " . $result['document_id'] . "<br>\n";
            echo "ID dans la table documents: " . $result['ged_document_id'] . "<br>\n";
            echo "Chemin du fichier: " . $result['file_path'] . "<br>\n";
            
            // Test 3: Vérifier que le document apparaît dans la liste des documents
            echo "<h2>3. Vérification dans la liste des documents</h2>\n";
            
            if ($result['ged_document_id']) {
                $generatedDoc = $document->getById($result['ged_document_id']);
                if ($generatedDoc) {
                    echo "✓ Document trouvé dans la table documents<br>\n";
                    echo "Nom original: " . $generatedDoc['nom_original'] . "<br>\n";
                    echo "Catégorie: " . ($generatedDoc['categorie_nom'] ?? 'Aucune') . "<br>\n";
                    echo "Mots-clés: " . $generatedDoc['mots_cles'] . "<br>\n";
                    echo "Source: " . $generatedDoc['source_generation'] . "<br>\n";
                    echo "Statut: " . $generatedDoc['statut'] . "<br>\n";
                } else {
                    echo "✗ Document non trouvé dans la table documents<br>\n";
                }
            }
            
            // Test 4: Vérifier que le fichier physique existe
            echo "<h2>4. Vérification du fichier physique</h2>\n";
            if (file_exists($result['file_path'])) {
                echo "✓ Fichier physique existe<br>\n";
                echo "Taille: " . formatFileSize(filesize($result['file_path'])) . "<br>\n";
            } else {
                echo "✗ Fichier physique introuvable<br>\n";
            }
            
        } else {
            echo "✗ Erreur lors de la génération: " . $result['message'] . "<br>\n";
        }
    } else {
        echo "Aucun modèle de contrat disponible pour les tests<br>\n";
    }
    
    // Test 5: Vérifier les catégories créées automatiquement
    echo "<h2>5. Vérification des catégories automatiques</h2>\n";
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("SELECT nom, couleur, icone FROM categories WHERE nom IN ('Contrats', 'Factures', 'Bons de commande')");
    $categories = $stmt->fetchAll();
    
    foreach ($categories as $cat) {
        echo "✓ Catégorie: " . $cat['nom'] . " (couleur: " . $cat['couleur'] . ", icône: " . $cat['icone'] . ")<br>\n";
    }
    
    echo "<h2>6. Résumé des documents dans le système</h2>\n";
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN source_generation IS NOT NULL THEN 1 END) as generes,
            COUNT(CASE WHEN source_generation IS NULL THEN 1 END) as uploades
        FROM documents 
        WHERE statut = 'actif'
    ");
    $stats = $stmt->fetch();
    
    echo "Total des documents actifs: " . $stats['total'] . "<br>\n";
    echo "Documents générés: " . $stats['generes'] . "<br>\n";
    echo "Documents uploadés: " . $stats['uploades'] . "<br>\n";
    
    echo "<h2>✓ Test d'intégration terminé</h2>\n";
    echo "<p><a href='/documents/list.php'>Voir la liste des documents</a></p>\n";
    
} catch (Exception $e) {
    echo "<h2>✗ Erreur lors du test</h2>\n";
    echo "Message: " . $e->getMessage() . "<br>\n";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>\n";
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>
