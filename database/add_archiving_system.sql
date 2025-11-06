-- Migration pour ajouter le système d'archivage
-- Date: 2025-11-06
-- Description: Ajout des fonctionnalités d'archivage et de gestion du cycle de vie des documents

-- 1. Ajouter la colonne statut à la table documents
ALTER TABLE documents 
ADD COLUMN statut ENUM('actif', 'archive', 'supprime') DEFAULT 'actif' AFTER description;

-- 2. Ajouter un index sur le statut pour optimiser les requêtes
ALTER TABLE documents 
ADD INDEX idx_statut (statut);

-- 3. Ajouter des colonnes pour la gestion de l'archivage
ALTER TABLE documents 
ADD COLUMN date_archivage TIMESTAMP NULL AFTER statut,
ADD COLUMN archive_par INT NULL AFTER date_archivage,
ADD COLUMN raison_archivage TEXT NULL AFTER archive_par,
ADD COLUMN date_suppression_prevue TIMESTAMP NULL AFTER raison_archivage;

-- 4. Ajouter les contraintes de clés étrangères
ALTER TABLE documents 
ADD FOREIGN KEY fk_archive_par (archive_par) REFERENCES users(id) ON DELETE SET NULL;

-- 5. Créer une table pour les règles d'archivage automatique
CREATE TABLE regles_archivage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    categorie_id INT NULL, -- Si NULL, s'applique à toutes les catégories
    duree_avant_archivage INT NOT NULL, -- En jours
    duree_avant_suppression INT NULL, -- En jours après archivage, NULL = jamais
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_categorie_actif (categorie_id, actif)
);

-- 6. Créer une table pour l'historique des actions d'archivage
CREATE TABLE historique_archivage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    document_id INT NOT NULL,
    action ENUM('archive', 'desarchive', 'supprime', 'restaure') NOT NULL,
    utilisateur_id INT NOT NULL,
    ancien_statut ENUM('actif', 'archive', 'supprime') NOT NULL,
    nouveau_statut ENUM('actif', 'archive', 'supprime') NOT NULL,
    raison TEXT,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_document (document_id),
    INDEX idx_date_action (date_action)
);

-- 7. Créer une table pour les notifications d'archivage
CREATE TABLE notifications_archivage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    document_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    type ENUM('avertissement_archivage', 'avertissement_suppression', 'archive', 'supprime') NOT NULL,
    message TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_lecture TIMESTAMP NULL,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_utilisateur_lu (utilisateur_id, lu),
    INDEX idx_date_creation (date_creation)
);

-- 8. Insérer des règles d'archivage par défaut
INSERT INTO regles_archivage (nom, description, categorie_id, duree_avant_archivage, duree_avant_suppression, actif) VALUES
('Archivage général', 'Règle par défaut pour tous les documents', NULL, 365, 2555, TRUE), -- 1 an avant archivage, 7 ans avant suppression
('Documents RH', 'Archivage spécifique pour les documents RH', (SELECT id FROM categories WHERE nom = 'RH' LIMIT 1), 1095, 3650, TRUE), -- 3 ans avant archivage, 10 ans avant suppression
('Documents comptables', 'Archivage pour les documents comptables', (SELECT id FROM categories WHERE nom = 'Comptabilité' LIMIT 1), 1095, 3650, TRUE), -- 3 ans avant archivage, 10 ans avant suppression
('Documents juridiques', 'Archivage pour les documents juridiques', (SELECT id FROM categories WHERE nom = 'Juridique' LIMIT 1), 1825, 7300, TRUE); -- 5 ans avant archivage, 20 ans avant suppression

-- 9. Mettre à jour les permissions des utilisateurs pour inclure l'archivage
-- Cette partie sera gérée par le script PHP pour éviter les erreurs de format JSON

-- 10. Créer des vues pour faciliter les requêtes
CREATE VIEW vue_documents_actifs AS
SELECT d.*, c.nom as categorie_nom, c.couleur as categorie_couleur, 
       u.nom as utilisateur_nom, u.prenom as utilisateur_prenom
FROM documents d
LEFT JOIN categories c ON d.categorie_id = c.id
LEFT JOIN users u ON d.utilisateur_id = u.id
WHERE d.statut = 'actif';

CREATE VIEW vue_documents_archives AS
SELECT d.*, c.nom as categorie_nom, c.couleur as categorie_couleur, 
       u.nom as utilisateur_nom, u.prenom as utilisateur_prenom,
       ua.nom as archive_par_nom, ua.prenom as archive_par_prenom
FROM documents d
LEFT JOIN categories c ON d.categorie_id = c.id
LEFT JOIN users u ON d.utilisateur_id = u.id
LEFT JOIN users ua ON d.archive_par = ua.id
WHERE d.statut = 'archive';

CREATE VIEW vue_documents_supprimes AS
SELECT d.*, c.nom as categorie_nom, c.couleur as categorie_couleur, 
       u.nom as utilisateur_nom, u.prenom as utilisateur_prenom,
       ua.nom as archive_par_nom, ua.prenom as archive_par_prenom
FROM documents d
LEFT JOIN categories c ON d.categorie_id = c.id
LEFT JOIN users u ON d.utilisateur_id = u.id
LEFT JOIN users ua ON d.archive_par = ua.id
WHERE d.statut = 'supprime';

-- 11. Créer des index composites pour optimiser les performances
ALTER TABLE documents ADD INDEX idx_statut_date (statut, date_upload);
ALTER TABLE documents ADD INDEX idx_statut_categorie (statut, categorie_id);
ALTER TABLE documents ADD INDEX idx_statut_utilisateur (statut, utilisateur_id);

-- 12. Ajouter des triggers pour automatiser certaines actions
DELIMITER //

-- Trigger pour enregistrer automatiquement les changements de statut
CREATE TRIGGER tr_documents_statut_change 
AFTER UPDATE ON documents
FOR EACH ROW
BEGIN
    IF OLD.statut != NEW.statut THEN
        INSERT INTO historique_archivage (document_id, action, utilisateur_id, ancien_statut, nouveau_statut, raison)
        VALUES (
            NEW.id, 
            CASE 
                WHEN NEW.statut = 'archive' THEN 'archive'
                WHEN NEW.statut = 'actif' AND OLD.statut = 'archive' THEN 'desarchive'
                WHEN NEW.statut = 'supprime' THEN 'supprime'
                WHEN NEW.statut = 'actif' AND OLD.statut = 'supprime' THEN 'restaure'
            END,
            COALESCE(NEW.archive_par, @current_user_id, 1), -- Utilise l'utilisateur courant ou admin par défaut
            OLD.statut,
            NEW.statut,
            NEW.raison_archivage
        );
    END IF;
END//

DELIMITER ;

-- 13. Commentaires pour documentation
ALTER TABLE documents 
MODIFY COLUMN statut ENUM('actif', 'archive', 'supprime') DEFAULT 'actif' 
COMMENT 'Statut du document: actif=visible, archive=archivé, supprime=marqué pour suppression';

ALTER TABLE documents 
MODIFY COLUMN date_archivage TIMESTAMP NULL 
COMMENT 'Date à laquelle le document a été archivé';

ALTER TABLE documents 
MODIFY COLUMN archive_par INT NULL 
COMMENT 'ID de l\'utilisateur qui a archivé le document';

ALTER TABLE documents 
MODIFY COLUMN raison_archivage TEXT NULL 
COMMENT 'Raison de l\'archivage du document';

ALTER TABLE documents 
MODIFY COLUMN date_suppression_prevue TIMESTAMP NULL 
COMMENT 'Date prévue pour la suppression définitive du document';

-- Migration terminée avec succès
SELECT 'Migration du système d\'archivage terminée avec succès!' as message;
