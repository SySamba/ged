-- Script SQL pour corriger la table users et créer user_preferences
-- Exécutez ce script directement dans phpMyAdmin ou MySQL

-- 1. Ajouter les colonnes manquantes à la table users
ALTER TABLE users ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) NULL AFTER email;
ALTER TABLE users ADD COLUMN IF NOT EXISTS adresse TEXT NULL AFTER telephone;
ALTER TABLE users ADD COLUMN IF NOT EXISTS statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif' AFTER permissions;
ALTER TABLE users ADD COLUMN IF NOT EXISTS derniere_connexion TIMESTAMP NULL AFTER statut;

-- 2. Créer la table user_preferences
CREATE TABLE IF NOT EXISTS user_preferences (
    user_id INT PRIMARY KEY,
    preferences JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Mettre à jour les utilisateurs existants
UPDATE users SET statut = 'actif' WHERE statut IS NULL;

-- 4. Vérifier la structure
DESCRIBE users;
DESCRIBE user_preferences;
