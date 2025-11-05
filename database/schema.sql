-- Base de données DigiDocs - Gestion électronique des documents pour PME
-- Création de la base de données
DROP DATABASE IF EXISTS digidocs;
CREATE DATABASE digidocs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE digidocs;

-- Table des utilisateurs
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employe') DEFAULT 'employe',
    permissions JSON DEFAULT NULL, -- Permissions spécifiques (lecture, écriture, suppression)
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL,
    actif BOOLEAN DEFAULT TRUE
);

-- Table des catégories de documents
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    couleur VARCHAR(7) DEFAULT '#007bff', -- Code couleur hex
    icone VARCHAR(50) DEFAULT 'file',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des documents
CREATE TABLE documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom_original VARCHAR(255) NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL, -- Nom sécurisé du fichier
    chemin_fichier VARCHAR(500) NOT NULL,
    type_mime VARCHAR(100) NOT NULL,
    taille_fichier BIGINT NOT NULL, -- Taille en octets
    categorie_id INT,
    utilisateur_id INT NOT NULL, -- Qui a uploadé le document
    mots_cles TEXT, -- Mots-clés pour la recherche
    description TEXT,
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_categorie (categorie_id),
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_date_upload (date_upload),
    FULLTEXT INDEX idx_recherche (nom_original, mots_cles, description)
);

-- Table des modèles Canva
CREATE TABLE modeles_canva (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    type ENUM('contrat', 'facture', 'bon_commande') NOT NULL,
    template_html LONGTEXT NOT NULL, -- Template HTML du modèle
    champs_variables JSON NOT NULL, -- Définition des champs variables
    description TEXT,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des documents générés à partir des modèles
CREATE TABLE documents_generes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    modele_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    nom_document VARCHAR(255) NOT NULL,
    donnees_remplies JSON NOT NULL, -- Données saisies par l'utilisateur
    chemin_pdf VARCHAR(500), -- Chemin du PDF généré
    date_generation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modele_id) REFERENCES modeles_canva(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des offres d'emploi
CREATE TABLE offres_emploi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    entreprise VARCHAR(255) NOT NULL,
    lieu VARCHAR(255),
    type_contrat ENUM('CDI', 'CDD', 'Stage', 'Freelance') NOT NULL,
    salaire VARCHAR(100),
    competences_requises TEXT,
    date_limite DATE,
    statut ENUM('active', 'fermee', 'pourvue') DEFAULT 'active',
    utilisateur_id INT NOT NULL, -- Qui a créé l'offre
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_statut (statut),
    INDEX idx_date_limite (date_limite)
);

-- Table des candidatures
CREATE TABLE candidatures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    offre_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    cv_chemin VARCHAR(500), -- Chemin du CV uploadé
    lettre_motivation LONGTEXT,
    statut ENUM('nouvelle', 'vue', 'retenue', 'rejetee') DEFAULT 'nouvelle',
    date_candidature TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_reponse TIMESTAMP NULL,
    FOREIGN KEY (offre_id) REFERENCES offres_emploi(id) ON DELETE CASCADE,
    INDEX idx_offre (offre_id),
    INDEX idx_statut (statut),
    INDEX idx_date_candidature (date_candidature)
);

-- Table des logs d'activité
CREATE TABLE logs_activite (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT,
    action VARCHAR(100) NOT NULL,
    table_concernee VARCHAR(50),
    id_element INT,
    details JSON,
    adresse_ip VARCHAR(45),
    user_agent TEXT,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_date_action (date_action),
    INDEX idx_action (action)
);

-- Table des paramètres système
CREATE TABLE parametres_systeme (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cle VARCHAR(100) UNIQUE NOT NULL,
    valeur TEXT,
    description TEXT,
    type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion des catégories par défaut
INSERT INTO categories (nom, description, couleur, icone) VALUES
('Factures', 'Documents de facturation', '#28a745', 'receipt'),
('Contrats', 'Contrats et accords', '#007bff', 'file-contract'),
('Bons de commande', 'Bons de commande et achats', '#ffc107', 'shopping-cart'),
('RH', 'Documents ressources humaines', '#dc3545', 'users'),
('Comptabilité', 'Documents comptables', '#6f42c1', 'calculator'),
('Juridique', 'Documents juridiques', '#fd7e14', 'balance-scale'),
('Autres', 'Autres documents', '#6c757d', 'folder');

-- Insertion des modèles Canva par défaut
INSERT INTO modeles_canva (nom, type, template_html, champs_variables, description) VALUES
('Contrat de travail standard', 'contrat', 
'<div class="contrat-template">
    <h1>CONTRAT DE TRAVAIL</h1>
    <p><strong>Entre :</strong></p>
    <p>{{nom_entreprise}}, située à {{adresse_entreprise}}, représentée par {{representant_entreprise}}</p>
    <p><strong>Et :</strong></p>
    <p>{{nom_employe}} {{prenom_employe}}, demeurant à {{adresse_employe}}</p>
    <h2>Article 1 - Objet</h2>
    <p>Le présent contrat a pour objet l\'engagement de {{nom_employe}} {{prenom_employe}} en qualité de {{poste}} à compter du {{date_debut}}.</p>
    <h2>Article 2 - Rémunération</h2>
    <p>La rémunération mensuelle brute est fixée à {{salaire}} FCFA.</p>
    <h2>Article 3 - Durée</h2>
    <p>Le présent contrat est conclu pour une durée {{type_contrat}}.</p>
    <p>Fait à {{lieu}}, le {{date_signature}}</p>
    <div class="signatures">
        <div>L\'Employeur</div>
        <div>L\'Employé</div>
    </div>
</div>',
'{"nom_entreprise": "text", "adresse_entreprise": "text", "representant_entreprise": "text", "nom_employe": "text", "prenom_employe": "text", "adresse_employe": "text", "poste": "text", "date_debut": "date", "salaire": "number", "type_contrat": "select", "lieu": "text", "date_signature": "date"}',
'Modèle standard de contrat de travail'),

('Facture commerciale', 'facture',
'<div class="facture-template">
    <div class="header">
        <h1>FACTURE N° {{numero_facture}}</h1>
        <div class="date">Date : {{date_facture}}</div>
    </div>
    <div class="parties">
        <div class="vendeur">
            <h3>Vendeur :</h3>
            <p>{{nom_entreprise}}</p>
            <p>{{adresse_entreprise}}</p>
            <p>Tel : {{telephone_entreprise}}</p>
        </div>
        <div class="client">
            <h3>Client :</h3>
            <p>{{nom_client}}</p>
            <p>{{adresse_client}}</p>
        </div>
    </div>
    <table class="items">
        <thead>
            <tr><th>Désignation</th><th>Qté</th><th>Prix unitaire</th><th>Total</th></tr>
        </thead>
        <tbody>
            {{items}}
        </tbody>
    </table>
    <div class="totaux">
        <p>Sous-total : {{sous_total}} FCFA</p>
        <p>TVA ({{taux_tva}}%) : {{montant_tva}} FCFA</p>
        <p><strong>Total TTC : {{total_ttc}} FCFA</strong></p>
    </div>
</div>',
'{"numero_facture": "text", "date_facture": "date", "nom_entreprise": "text", "adresse_entreprise": "text", "telephone_entreprise": "text", "nom_client": "text", "adresse_client": "text", "items": "array", "sous_total": "number", "taux_tva": "number", "montant_tva": "number", "total_ttc": "number"}',
'Modèle de facture commerciale avec calcul automatique'),

('Bon de commande', 'bon_commande',
'<div class="bon-commande-template">
    <h1>BON DE COMMANDE N° {{numero_bon}}</h1>
    <div class="date">Date : {{date_commande}}</div>
    <div class="parties">
        <div class="acheteur">
            <h3>Acheteur :</h3>
            <p>{{nom_acheteur}}</p>
            <p>{{adresse_acheteur}}</p>
        </div>
        <div class="fournisseur">
            <h3>Fournisseur :</h3>
            <p>{{nom_fournisseur}}</p>
            <p>{{adresse_fournisseur}}</p>
        </div>
    </div>
    <table class="items">
        <thead>
            <tr><th>Article</th><th>Référence</th><th>Qté</th><th>Prix unitaire</th><th>Total</th></tr>
        </thead>
        <tbody>
            {{items}}
        </tbody>
    </table>
    <div class="conditions">
        <p><strong>Conditions de livraison :</strong> {{conditions_livraison}}</p>
        <p><strong>Délai de livraison :</strong> {{delai_livraison}}</p>
        <p><strong>Mode de paiement :</strong> {{mode_paiement}}</p>
    </div>
    <div class="total">
        <p><strong>Total : {{total}} FCFA</strong></p>
    </div>
</div>',
'{"numero_bon": "text", "date_commande": "date", "nom_acheteur": "text", "adresse_acheteur": "text", "nom_fournisseur": "text", "adresse_fournisseur": "text", "items": "array", "conditions_livraison": "text", "delai_livraison": "text", "mode_paiement": "text", "total": "number"}',
'Modèle de bon de commande standard');

-- Insertion des paramètres système par défaut
INSERT INTO parametres_systeme (cle, valeur, description, type) VALUES
('taille_max_upload', '10485760', 'Taille maximale d\'upload en octets (10MB)', 'integer'),
('types_fichiers_autorises', '["pdf","doc","docx","xls","xlsx","jpg","jpeg","png","gif"]', 'Types de fichiers autorisés', 'json'),
('sauvegarde_auto', 'true', 'Sauvegarde automatique activée', 'boolean'),
('retention_logs', '90', 'Durée de rétention des logs en jours', 'integer'),
('email_notifications', 'true', 'Notifications email activées', 'boolean');

-- Table des préférences utilisateur
CREATE TABLE user_preferences (
    user_id INT PRIMARY KEY,
    preferences JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Création de l'utilisateur admin par défaut
-- Le mot de passe sera mis à jour par le script d'installation
INSERT INTO users (nom, prenom, email, password, role, permissions) VALUES
('Admin', 'Système', 'sambasy837@gmail.com', 'temp_password', 'admin', '{"documents": {"create": true, "read": true, "update": true, "delete": true}, "users": {"create": true, "read": true, "update": true, "delete": true}, "offres": {"create": true, "read": true, "update": true, "delete": true}}');
