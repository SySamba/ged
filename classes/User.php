<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Classe User - Gestion des utilisateurs
 */
class User {
    private $pdo;
    
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Authentifier un utilisateur
     */
    public function authenticate($email, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, password, role, permissions, actif 
                FROM users 
                WHERE email = ? AND actif = 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Mettre à jour la dernière connexion
                $this->updateLastLogin($user['id']);
                
                // Créer la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_permissions'] = $user['permissions'];
                $_SESSION['login_time'] = time();
                
                logActivity('connexion', 'users', $user['id']);
                
                return [
                    'success' => true,
                    'user' => $user,
                    'message' => 'Connexion réussie'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'authentification : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function create($data) {
        try {
            // Vérifier si l'email existe déjà
            if ($this->emailExists($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Cette adresse email est déjà utilisée'
                ];
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (nom, prenom, email, password, role, permissions)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $hashedPassword = password_hash($data['password'], HASH_ALGO);
            $permissions = json_encode($data['permissions'] ?? $this->getDefaultPermissions($data['role']));
            
            $stmt->execute([
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $hashedPassword,
                $data['role'] ?? 'employe',
                $permissions
            ]);
            
            $userId = $this->pdo->lastInsertId();
            
            logActivity('creation_utilisateur', 'users', $userId, [
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'role' => $data['role'] ?? 'employe'
            ]);
            
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Utilisateur créé avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtenir un utilisateur par ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, telephone, adresse, role, permissions, date_creation, derniere_connexion, actif, 
                       CASE WHEN actif = 1 THEN 'actif' ELSE 'inactif' END as statut
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtenir un utilisateur par ID avec mot de passe (pour vérification)
     */
    public function getByIdWithPassword($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, telephone, adresse, password, role, permissions, date_creation, derniere_connexion, actif, 
                       CASE WHEN actif = 1 THEN 'actif' ELSE 'inactif' END as statut
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtenir tous les utilisateurs
     */
    public function getAll($filters = []) {
        try {
            $sql = "
                SELECT id, nom, prenom, email, role, date_creation, derniere_connexion, actif
                FROM users 
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($filters['role'])) {
                $sql .= " AND role = ?";
                $params[] = $filters['role'];
            }
            
            if (isset($filters['actif'])) {
                $sql .= " AND actif = ?";
                $params[] = $filters['actif'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY nom, prenom";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            if (isset($data['nom'])) {
                $fields[] = "nom = ?";
                $params[] = $data['nom'];
            }
            
            if (isset($data['prenom'])) {
                $fields[] = "prenom = ?";
                $params[] = $data['prenom'];
            }
            
            if (isset($data['email'])) {
                // Vérifier si le nouvel email n'est pas déjà utilisé
                if ($this->emailExists($data['email'], $id)) {
                    return [
                        'success' => false,
                        'message' => 'Cette adresse email est déjà utilisée'
                    ];
                }
                $fields[] = "email = ?";
                $params[] = $data['email'];
            }
            
            if (isset($data['password']) && !empty($data['password'])) {
                $fields[] = "password = ?";
                $params[] = password_hash($data['password'], HASH_ALGO);
            }
            
            if (isset($data['role'])) {
                $fields[] = "role = ?";
                $params[] = $data['role'];
            }
            
            if (isset($data['permissions'])) {
                $fields[] = "permissions = ?";
                $params[] = json_encode($data['permissions']);
            }
            
            if (isset($data['actif'])) {
                $fields[] = "actif = ?";
                $params[] = $data['actif'];
            }
            
            if (empty($fields)) {
                return [
                    'success' => false,
                    'message' => 'Aucune donnée à mettre à jour'
                ];
            }
            
            $params[] = $id;
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            logActivity('modification_utilisateur', 'users', $id, $data);
            
            return [
                'success' => true,
                'message' => 'Utilisateur mis à jour avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function delete($id) {
        try {
            // Ne pas supprimer l'utilisateur admin principal
            $user = $this->getById($id);
            if ($user && $user['email'] === 'sambasy837@gmail.com') {
                return [
                    'success' => false,
                    'message' => 'Impossible de supprimer l\'administrateur principal'
                ];
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity('suppression_utilisateur', 'users', $id, ['utilisateur_supprime' => $user]);
            
            return [
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Déconnecter l'utilisateur
     */
    public function logout() {
        logActivity('deconnexion', 'users', $_SESSION['user_id'] ?? null);
        
        session_destroy();
        return [
            'success' => true,
            'message' => 'Déconnexion réussie'
        ];
    }
    
    /**
     * Vérifier si un email existe déjà
     */
    private function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Mettre à jour la dernière connexion
     */
    private function updateLastLogin($userId) {
        $stmt = $this->pdo->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    /**
     * Obtenir les permissions par défaut selon le rôle
     */
    private function getDefaultPermissions($role) {
        if ($role === 'admin') {
            return [
                'documents' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
                'users' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
                'offres' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
                'modeles' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true]
            ];
        } else {
            return [
                'documents' => ['create' => true, 'read' => true, 'update' => false, 'delete' => false],
                'users' => ['create' => false, 'read' => false, 'update' => false, 'delete' => false],
                'offres' => ['create' => false, 'read' => true, 'update' => false, 'delete' => false],
                'modeles' => ['create' => true, 'read' => true, 'update' => false, 'delete' => false]
            ];
        }
    }
    
    /**
     * Obtenir les statistiques des utilisateurs
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total des utilisateurs
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users WHERE actif = 1");
            $stats['total_users'] = $stmt->fetch()['total'];
            
            // Utilisateurs par rôle
            $stmt = $this->pdo->query("
                SELECT role, COUNT(*) as count 
                FROM users 
                WHERE actif = 1 
                GROUP BY role
            ");
            $stats['by_role'] = $stmt->fetchAll();
            
            // Connexions récentes (dernières 24h)
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM users 
                WHERE derniere_connexion >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stats['recent_logins'] = $stmt->fetch()['count'];
            
            return $stats;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Mettre à jour le profil utilisateur
     */
    public function updateProfile($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            if (isset($data['nom']) && !empty($data['nom'])) {
                $fields[] = "nom = ?";
                $params[] = $data['nom'];
            }
            
            if (isset($data['prenom']) && !empty($data['prenom'])) {
                $fields[] = "prenom = ?";
                $params[] = $data['prenom'];
            }
            
            if (isset($data['email']) && !empty($data['email'])) {
                // Vérifier que l'email n'est pas déjà utilisé par un autre utilisateur
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$data['email'], $id]);
                if ($stmt->fetch()) {
                    return [
                        'success' => false,
                        'message' => 'Cette adresse email est déjà utilisée'
                    ];
                }
                
                $fields[] = "email = ?";
                $params[] = $data['email'];
            }
            
            if (isset($data['telephone'])) {
                $fields[] = "telephone = ?";
                $params[] = $data['telephone'];
            }
            
            if (isset($data['adresse'])) {
                $fields[] = "adresse = ?";
                $params[] = $data['adresse'];
            }
            
            if (empty($fields)) {
                return [
                    'success' => false,
                    'message' => 'Aucune donnée à mettre à jour'
                ];
            }
            
            $params[] = $id;
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            logActivity('modification_profil', 'users', $id, $data);
            
            return [
                'success' => true,
                'message' => 'Profil mis à jour avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Changer le mot de passe
     */
    public function changePassword($id, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, HASH_ALGO);
            
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $id]);
            
            logActivity('changement_mot_de_passe', 'users', $id);
            
            return [
                'success' => true,
                'message' => 'Mot de passe modifié avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du changement de mot de passe : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mettre à jour les préférences utilisateur
     */
    public function updatePreferences($id, $preferences) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_preferences (user_id, preferences) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE preferences = VALUES(preferences)
            ");
            
            $stmt->execute([$id, json_encode($preferences)]);
            
            logActivity('modification_preferences', 'users', $id, $preferences);
            
            return [
                'success' => true,
                'message' => 'Préférences sauvegardées avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupérer les préférences utilisateur
     */
    public function getPreferences($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT preferences FROM user_preferences WHERE user_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result) {
                return json_decode($result['preferences'], true);
            }
            
            // Préférences par défaut
            return [
                'theme' => 'light',
                'langue' => 'fr',
                'notifications_email' => 1,
                'documents_per_page' => 10
            ];
            
        } catch (Exception $e) {
            return [
                'theme' => 'light',
                'langue' => 'fr',
                'notifications_email' => 1,
                'documents_per_page' => 10
            ];
        }
    }
}
