<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Classe Job - Gestion des offres d'emploi et candidatures
 */
class Job {
    private $pdo;
    
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Créer une nouvelle offre d'emploi
     */
    public function createOffre($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO offres_emploi (titre, description, entreprise, lieu, type_contrat, 
                                         salaire, competences_requises, date_limite, utilisateur_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['titre'],
                $data['description'],
                $data['entreprise'],
                $data['lieu'] ?? null,
                $data['type_contrat'],
                $data['salaire'] ?? null,
                $data['competences_requises'] ?? null,
                $data['date_limite'] ?? null,
                $_SESSION['user_id']
            ]);
            
            $offreId = $this->pdo->lastInsertId();
            
            logActivity('creation_offre', 'offres_emploi', $offreId, [
                'titre' => $data['titre'],
                'entreprise' => $data['entreprise']
            ]);
            
            return [
                'success' => true,
                'offre_id' => $offreId,
                'message' => 'Offre d\'emploi créée avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtenir toutes les offres d'emploi
     */
    public function getOffres($filters = []) {
        try {
            $sql = "
                SELECT o.*, u.nom as createur_nom, u.prenom as createur_prenom,
                       COUNT(c.id) as nb_candidatures
                FROM offres_emploi o
                LEFT JOIN users u ON o.utilisateur_id = u.id
                LEFT JOIN candidatures c ON o.id = c.offre_id
                WHERE 1=1
            ";
            $params = [];
            
            // Filtre par statut
            if (!empty($filters['statut'])) {
                $sql .= " AND o.statut = ?";
                $params[] = $filters['statut'];
            } else {
                $sql .= " AND o.statut = 'active'"; // Par défaut, ne montrer que les offres actives
            }
            
            // Filtre par type de contrat
            if (!empty($filters['type_contrat'])) {
                $sql .= " AND o.type_contrat = ?";
                $params[] = $filters['type_contrat'];
            }
            
            // Filtre par lieu
            if (!empty($filters['lieu'])) {
                $sql .= " AND o.lieu LIKE ?";
                $params[] = '%' . $filters['lieu'] . '%';
            }
            
            // Recherche par mot-clé
            if (!empty($filters['search'])) {
                $sql .= " AND (o.titre LIKE ? OR o.description LIKE ? OR o.entreprise LIKE ? OR o.competences_requises LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Si pas admin, ne voir que ses propres offres pour la gestion
            if (isset($filters['mes_offres']) && $filters['mes_offres']) {
                $sql .= " AND o.utilisateur_id = ?";
                $params[] = $_SESSION['user_id'];
            }
            
            $sql .= " GROUP BY o.id ORDER BY o.date_creation DESC";
            
            // Pagination
            if (isset($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
                
                if (isset($filters['offset'])) {
                    $sql .= " OFFSET ?";
                    $params[] = (int)$filters['offset'];
                }
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtenir une offre par ID
     */
    public function getOffreById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT o.*, u.nom as createur_nom, u.prenom as createur_prenom
                FROM offres_emploi o
                LEFT JOIN users u ON o.utilisateur_id = u.id
                WHERE o.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Mettre à jour une offre d'emploi
     */
    public function updateOffre($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            $allowedFields = ['titre', 'description', 'entreprise', 'lieu', 'type_contrat', 
                            'salaire', 'competences_requises', 'date_limite', 'statut'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return [
                    'success' => false,
                    'message' => 'Aucune donnée à mettre à jour'
                ];
            }
            
            $params[] = $id;
            
            $sql = "UPDATE offres_emploi SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            logActivity('modification_offre', 'offres_emploi', $id, $data);
            
            return [
                'success' => true,
                'message' => 'Offre mise à jour avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer une offre d'emploi
     */
    public function deleteOffre($id) {
        try {
            // Vérifier les permissions
            $offre = $this->getOffreById($id);
            if (!$offre) {
                return [
                    'success' => false,
                    'message' => 'Offre introuvable'
                ];
            }
            
            if (!hasPermission('offres', 'delete') && $offre['utilisateur_id'] != $_SESSION['user_id']) {
                return [
                    'success' => false,
                    'message' => 'Permission insuffisante'
                ];
            }
            
            // Supprimer les candidatures associées
            $stmt = $this->pdo->prepare("DELETE FROM candidatures WHERE offre_id = ?");
            $stmt->execute([$id]);
            
            // Supprimer l'offre
            $stmt = $this->pdo->prepare("DELETE FROM offres_emploi WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity('suppression_offre', 'offres_emploi', $id, [
                'titre' => $offre['titre']
            ]);
            
            return [
                'success' => true,
                'message' => 'Offre supprimée avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Postuler à une offre d'emploi
     */
    public function postuler($offreId, $data, $cvFile = null) {
        try {
            // Vérifier si l'offre existe et est active
            $offre = $this->getOffreById($offreId);
            if (!$offre || $offre['statut'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Cette offre n\'est plus disponible'
                ];
            }
            
            // Vérifier si la date limite n'est pas dépassée
            if ($offre['date_limite'] && strtotime($offre['date_limite']) < time()) {
                return [
                    'success' => false,
                    'message' => 'La date limite de candidature est dépassée'
                ];
            }
            
            // Vérifier si la personne n'a pas déjà postulé
            $stmt = $this->pdo->prepare("
                SELECT id FROM candidatures 
                WHERE offre_id = ? AND email = ?
            ");
            $stmt->execute([$offreId, $data['email']]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Vous avez déjà postulé à cette offre'
                ];
            }
            
            // Traiter l'upload du CV
            $cvPath = null;
            if ($cvFile && $cvFile['error'] === UPLOAD_ERR_OK) {
                $cvValidation = $this->validateCV($cvFile);
                if (!$cvValidation['success']) {
                    return $cvValidation;
                }
                
                $extension = strtolower(pathinfo($cvFile['name'], PATHINFO_EXTENSION));
                $filename = uniqid() . '_cv_' . time() . '.' . $extension;
                $cvPath = UPLOAD_PATH . '/cv/' . $filename;
                
                if (!move_uploaded_file($cvFile['tmp_name'], $cvPath)) {
                    return [
                        'success' => false,
                        'message' => 'Erreur lors de l\'upload du CV'
                    ];
                }
            }
            
            // Enregistrer la candidature
            $stmt = $this->pdo->prepare("
                INSERT INTO candidatures (offre_id, nom, prenom, email, telephone, 
                                        cv_chemin, lettre_motivation)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $offreId,
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $data['telephone'] ?? null,
                $cvPath,
                $data['lettre_motivation'] ?? null
            ]);
            
            $candidatureId = $this->pdo->lastInsertId();
            
            logActivity('candidature', 'candidatures', $candidatureId, [
                'offre_id' => $offreId,
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email']
            ]);
            
            return [
                'success' => true,
                'candidature_id' => $candidatureId,
                'message' => 'Votre candidature a été envoyée avec succès'
            ];
            
        } catch (Exception $e) {
            // Supprimer le CV en cas d'erreur
            if (isset($cvPath) && file_exists($cvPath)) {
                unlink($cvPath);
            }
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la candidature : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtenir les candidatures pour une offre
     */
    public function getCandidatures($offreId = null, $filters = []) {
        try {
            $sql = "
                SELECT c.*, o.titre as offre_titre, o.entreprise
                FROM candidatures c
                JOIN offres_emploi o ON c.offre_id = o.id
                WHERE 1=1
            ";
            $params = [];
            
            if ($offreId) {
                $sql .= " AND c.offre_id = ?";
                $params[] = $offreId;
            }
            
            // Si pas admin, ne voir que les candidatures de ses offres
            if (!hasPermission('offres', 'read')) {
                $sql .= " AND o.utilisateur_id = ?";
                $params[] = $_SESSION['user_id'];
            }
            
            // Filtre par statut
            if (!empty($filters['statut'])) {
                $sql .= " AND c.statut = ?";
                $params[] = $filters['statut'];
            }
            
            $sql .= " ORDER BY c.date_candidature DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Mettre à jour le statut d'une candidature
     */
    public function updateCandidatureStatut($id, $statut, $commentaire = null) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE candidatures 
                SET statut = ?, date_reponse = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$statut, $id]);
            
            logActivity('modification_candidature', 'candidatures', $id, [
                'nouveau_statut' => $statut,
                'commentaire' => $commentaire
            ]);
            
            return [
                'success' => true,
                'message' => 'Statut de la candidature mis à jour'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Valider un fichier CV
     */
    private function validateCV($file) {
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'upload du CV'
            ];
        }
        
        // Vérifier la taille (5MB max pour les CV)
        if ($file['size'] > 5 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => 'Le CV est trop volumineux (max: 5MB)'
            ];
        }
        
        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => 'Format de CV non autorisé. Utilisez PDF, DOC ou DOCX'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Obtenir les statistiques des offres d'emploi
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total des offres
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM offres_emploi");
            $stats['total_offres'] = $stmt->fetch()['total'];
            
            // Offres par statut
            $stmt = $this->pdo->query("
                SELECT statut, COUNT(*) as count 
                FROM offres_emploi 
                GROUP BY statut
            ");
            $stats['by_status'] = $stmt->fetchAll();
            
            // Total des candidatures
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM candidatures");
            $stats['total_candidatures'] = $stmt->fetch()['total'];
            
            // Candidatures par statut
            $stmt = $this->pdo->query("
                SELECT statut, COUNT(*) as count 
                FROM candidatures 
                GROUP BY statut
            ");
            $stats['candidatures_by_status'] = $stmt->fetchAll();
            
            // Offres récentes
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM offres_emploi 
                WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stats['recent_offres'] = $stmt->fetch()['count'];
            
            return $stats;
            
        } catch (Exception $e) {
            return [];
        }
    }
}
