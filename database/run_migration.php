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
        echo "Exécution de la migration...\n";
        
        // Exécuter la migration
        $migrationFile = __DIR__ . '/migrations/add_source_generation_column.sql';
        if (file_exists($migrationFile)) {
            $db->executeSqlFile($migrationFile);
            echo "✓ Migration exécutée avec succès.\n";
        } else {
            echo "✗ Fichier de migration introuvable.\n";
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
