-- Migration pour ajouter la colonne source_generation à la table documents
-- Cette colonne permet d'identifier les documents générés automatiquement

ALTER TABLE documents 
ADD COLUMN source_generation VARCHAR(50) NULL AFTER description,
ADD COLUMN statut ENUM('actif', 'archive', 'supprime') DEFAULT 'actif' AFTER source_generation,
ADD COLUMN date_archivage TIMESTAMP NULL AFTER statut,
ADD COLUMN archive_par INT NULL AFTER date_archivage,
ADD COLUMN raison_archivage TEXT NULL AFTER archive_par,
ADD COLUMN date_suppression_prevue TIMESTAMP NULL AFTER raison_archivage;

-- Ajouter les contraintes de clés étrangères
ALTER TABLE documents 
ADD CONSTRAINT fk_documents_archive_par 
FOREIGN KEY (archive_par) REFERENCES users(id) ON DELETE SET NULL;

-- Ajouter des index pour améliorer les performances
ALTER TABLE documents 
ADD INDEX idx_source_generation (source_generation),
ADD INDEX idx_statut (statut),
ADD INDEX idx_date_archivage (date_archivage);
