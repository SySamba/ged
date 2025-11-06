-- Migration pour ajouter le système d'archivage
-- Date: 2025-11-06
-- Description: Ajoute la gestion des statuts et de l'archivage des documents

USE digidocs;

-- Ajouter la colonne statut à la table documents
ALTER TABLE documents 
ADD COLUMN statut ENUM('actif', 'archive', 'supprime') DEFAULT 'actif' AFTER description;

-- Ajouter un index sur le statut pour optimiser les requêtes
ALTER TABLE documents 
ADD INDEX idx_statut (statut);

-- Ajouter la date d'archivage
ALTER TABLE documents 
ADD COLUMN date_archivage TIMESTAMP NULL AFTER date_modification;

-- Ajouter le motif d'archivage
ALTER TABLE documents 
ADD COLUMN motif_archivage VARCHAR(255) NULL AFTER date_archivage;

-- Ajouter l'utilisateur qui a archivé
ALTER TABLE documents 
ADD COLUMN archive_par INT NULL AFTER motif_archivage,
ADD FOREIGN KEY fk_archive_par (archive_par) REFERENCES users(id) ON DELETE SET NULL;

-- Table pour les règles d'archivage automatique
CREATE TABLE regles_archivage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    categorie_id INT NULL, -- NULL = toutes les catégories
    duree_mois INT NOT NULL, -- Durée en mois avant archivage
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_categorie_actif (categorie_id, actif)
);

-- Table pour l'historique des archivages
CREATE TABLE historique_archivage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    document_id INT NOT NULL,
    action ENUM('archive', 'desarchive', 'supprime', 'restaure') NOT NULL,
    utilisateur_id INT NOT NULL,
    motif VARCHAR(255),
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_document (document_id),
    INDEX idx_date_action (date_action)
);

-- Insertion de règles d'archivage par défaut
INSERT INTO regles_archivage (nom, description, categorie_id, duree_mois) VALUES
('Archivage général', 'Archivage automatique après 24 mois pour tous les documents', NULL, 24),
('Documents RH', 'Archivage des documents RH après 36 mois', 1, 36),
('Documents comptables', 'Archivage des documents comptables après 84 mois (7 ans)', 2, 84),
('Documents juridiques', 'Archivage des documents juridiques après 120 mois (10 ans)', 3, 120);

-- Mettre à jour tous les documents existants avec le statut 'actif'
UPDATE documents SET statut = 'actif' WHERE statut IS NULL;

-- Ajouter des permissions pour l'archivage dans les rôles existants
-- Note: Ceci devra être fait via l'interface d'administration pour les utilisateurs existants

COMMIT;
