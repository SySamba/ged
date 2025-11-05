-- Script SQL pour créer/corriger l'utilisateur administrateur
-- Exécutez ce script dans phpMyAdmin si le script PHP ne fonctionne pas

-- Supprimer l'utilisateur existant s'il y en a un
DELETE FROM users WHERE email = 'sambasy837@gmail.com';

-- Créer l'utilisateur administrateur avec le bon hash
INSERT INTO users (nom, prenom, email, password, role, permissions, actif, date_creation) 
VALUES (
    'SAMBA',
    'SY',
    'sambasy837@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Hash pour "password"
    'admin',
    '{"documents":{"create":true,"read":true,"update":true,"delete":true},"users":{"create":true,"read":true,"update":true,"delete":true},"offres":{"create":true,"read":true,"update":true,"delete":true},"modeles":{"create":true,"read":true,"update":true,"delete":true}}',
    1,
    NOW()
);

-- Note: Ce hash correspond au mot de passe "password"
-- Vous devrez changer le mot de passe après la première connexion
