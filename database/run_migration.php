<?php
/**
 * Script pour exécuter les migrations de base de données
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Vérification de la structure de la table documents...\n";
    
    // Vérifier si les colonnes existent déjà
    $stmt = $pdo->query("DESCRIBE documents");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['source_generation', 'statut', 'date_archivage', 'archive_par', 'raison_archivage', 'date_suppression_prevue'];
    $missingColumns = [];
    
    foreach ($requiredColumns as $column) {
        if (!in_array($column, $columns)) {
            $missingColumns[] = $column;
        }
    }
    
    if (empty($missingColumns)) {
        echo "✓ Toutes les colonnes requises sont présentes dans la table documents.\n";
    } else {
        echo "⚠ Colonnes manquantes: " . implode(', ', $missingColumns) . "\n";
        echo "Ajout des colonnes manquantes une par une...\n";
        
        // Ajouter chaque colonne manquante individuellement
        foreach ($missingColumns as $column) {
            try {
                switch ($column) {
                    case 'source_generation':
                        $pdo->exec("ALTER TABLE documents ADD COLUMN source_generation VARCHAR(50) NULL AFTER description");
                        echo "✓ Colonne source_generation ajoutée.\n";
                        break;
                        
                    case 'statut':
                        $pdo->exec("ALTER TABLE documents ADD COLUMN statut ENUM('actif','archive','supprime') DEFAULT 'actif' AFTER source_generation");
                        echo "✓ Colonne statut ajoutée.\n";
                        break;
                        
                    case 'date_archivage':
                        $pdo->exec("ALTER TABLE documents ADD COLUMN date_archivage TIMESTAMP NULL AFTER statut");
                        echo "✓ Colonne date_archivage ajoutée.\n";
                        break;
                        
                    case 'archive_par':
                        $pdo->exec("ALTER TABLE documents ADD COLUMN archive_par INT NULL AFTER date_archivage");
                        echo "✓ Colonne archive_par ajoutée.\n";
                        break;
                        
                    case 'raison_archivage':
                        $pdo->exec("ALTER TABLE documents ADD COLUMN raison_archivage TEXT NULL AFTER archive_par");
                        echo "✓ Colonne raison_archivage ajoutée.\n";
                        break;
                        
                    case 'date_suppression_prevue':
                        $pdo->exec("ALTER TABLE documents ADD COLUMN date_suppression_prevue TIMESTAMP NULL AFTER raison_archivage");
                        echo "✓ Colonne date_suppression_prevue ajoutée.\n";
                        break;
                }
            } catch (Exception $e) {
                echo "⚠ Erreur lors de l'ajout de la colonne $column: " . $e->getMessage() . "\n";
            }
        }
        
        // Mettre à jour les documents existants avec le statut par défaut
        try {
            $pdo->exec("UPDATE documents SET statut = 'actif' WHERE statut IS NULL");
            echo "✓ Documents existants mis à jour avec le statut 'actif'.\n";
        } catch (Exception $e) {
            echo "⚠ Erreur lors de la mise à jour des statuts: " . $e->getMessage() . "\n";
        }
        
        // Ajouter les index si nécessaire
        try {
            $pdo->exec("ALTER TABLE documents ADD INDEX idx_source_generation (source_generation)");
            echo "✓ Index idx_source_generation ajouté.\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                echo "⚠ Erreur lors de l'ajout de l'index source_generation: " . $e->getMessage() . "\n";
            }
        }
        
        try {
            $pdo->exec("ALTER TABLE documents ADD INDEX idx_statut (statut)");
            echo "✓ Index idx_statut ajouté.\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                echo "⚠ Erreur lors de l'ajout de l'index statut: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Vérifier la structure finale
    echo "\nStructure actuelle de la table documents:\n";
    $stmt = $pdo->query("DESCRIBE documents");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) " . 
             ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column['Default'] !== null ? " DEFAULT '{$column['Default']}'" : '') . "\n";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
