-- Script de migration pour ajouter les colonnes manquantes dans la table users
-- Exécuter ce script pour corriger les problèmes de profil utilisateur

USE digidocs;

-- Ajouter les colonnes telephone et adresse si elles n'existent pas
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) NULL AFTER email,
ADD COLUMN IF NOT EXISTS adresse TEXT NULL AFTER telephone,
ADD COLUMN IF NOT EXISTS statut ENUM('actif', 'inactif') DEFAULT 'actif' AFTER actif;

-- Mettre à jour la colonne actif pour être compatible avec statut
UPDATE users SET statut = CASE WHEN actif = 1 THEN 'actif' ELSE 'inactif' END;

-- Vérifier la structure de la table
DESCRIBE users;
