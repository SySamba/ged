<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Classe Document - Gestion des documents
 */
class Document {
    private $pdo;
    
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Uploader un document
     */
    public function upload($file, $data) {
        try {
            // Vérifications de sécurité
            $validation = $this->validateFile($file);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Générer un nom de fichier sécurisé
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = UPLOAD_PATH . '/documents/' . $filename;
            
            // Déplacer le fichier
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    'success' => false,
                    'message' => 'Erreur lors du téléchargement du fichier'
                ];
            }
            
            // Enregistrer en base de données
            $stmt = $this->pdo->prepare("
                INSERT INTO documents (nom_original, nom_fichier, chemin_fichier, type_mime, taille_fichier, 
                                     categorie_id, utilisateur_id, mots_cles, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $file['name'],
                $filename,
                $filepath,
                $file['type'],
                $file['size'],
                $data['categorie_id'] ?? null,
                $_SESSION['user_id'],
                $data['mots_cles'] ?? '',
                $data['description'] ?? ''
            ]);
            
            $documentId = $this->pdo->lastInsertId();
            
            logActivity('upload_document', 'documents', $documentId, [
                'nom_original' => $file['name'],
                'taille' => $file['size'],
                'categorie_id' => $data['categorie_id'] ?? null
            ]);
            
            return [
                'success' => true,
                'document_id' => $documentId,
                'message' => 'Document uploadé avec succès'
            ];
            
        } catch (Exception $e) {
            // Supprimer le fichier en cas d'erreur
            if (isset($filepath) && file_exists($filepath)) {
                unlink($filepath);
            }
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'upload : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtenir un document par ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.*, c.nom as categorie_nom, c.couleur as categorie_couleur,
                       u.nom as utilisateur_nom, u.prenom as utilisateur_prenom
                FROM documents d
                LEFT JOIN categories c ON d.categorie_id = c.id
                LEFT JOIN users u ON d.utilisateur_id = u.id
                WHERE d.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Rechercher des documents
     */
    public function search($filters = []) {
        try {
            $sql = "
                SELECT d.*, c.nom as categorie_nom, c.couleur as categorie_couleur, c.icone as categorie_icone,
                       u.nom as utilisateur_nom, u.prenom as utilisateur_prenom
                FROM documents d
                LEFT JOIN categories c ON d.categorie_id = c.id
                LEFT JOIN users u ON d.utilisateur_id = u.id
                WHERE 1=1
            ";
            $params = [];
            
            // Recherche par mot-clé
            if (!empty($filters['search'])) {
                $sql .= " AND (MATCH(d.nom_original, d.mots_cles, d.description) AGAINST (? IN NATURAL LANGUAGE MODE)
                         OR d.nom_original LIKE ? OR d.mots_cles LIKE ? OR d.description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $filters['search'];
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Filtre par catégorie
            if (!empty($filters['categorie_id'])) {
                $sql .= " AND d.categorie_id = ?";
                $params[] = $filters['categorie_id'];
            }
            
            // Filtre par utilisateur
            if (!empty($filters['utilisateur_id'])) {
                $sql .= " AND d.utilisateur_id = ?";
                $params[] = $filters['utilisateur_id'];
            }
            
            // Filtre par type de fichier
            if (!empty($filters['type_mime'])) {
                $sql .= " AND d.type_mime LIKE ?";
                $params[] = '%' . $filters['type_mime'] . '%';
            }
            
            // Filtre par date
            if (!empty($filters['date_debut'])) {
                $sql .= " AND DATE(d.date_upload) >= ?";
                $params[] = $filters['date_debut'];
            }
            
            if (!empty($filters['date_fin'])) {
                $sql .= " AND DATE(d.date_upload) <= ?";
                $params[] = $filters['date_fin'];
            }
            
            // Tri
            $orderBy = $filters['order_by'] ?? 'date_upload';
            $orderDir = $filters['order_dir'] ?? 'DESC';
            $sql .= " ORDER BY d.{$orderBy} {$orderDir}";
            
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
     * Mettre à jour un document
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            if (isset($data['categorie_id'])) {
                $fields[] = "categorie_id = ?";
                $params[] = $data['categorie_id'];
            }
            
            if (isset($data['mots_cles'])) {
                $fields[] = "mots_cles = ?";
                $params[] = $data['mots_cles'];
            }
            
            if (isset($data['description'])) {
                $fields[] = "description = ?";
                $params[] = $data['description'];
            }
            
            if (empty($fields)) {
                return [
                    'success' => false,
                    'message' => 'Aucune donnée à mettre à jour'
                ];
            }
            
            $params[] = $id;
            
            $sql = "UPDATE documents SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            logActivity('modification_document', 'documents', $id, $data);
            
            return [
                'success' => true,
                'message' => 'Document mis à jour avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer un document
     */
    public function delete($id) {
        try {
            // Récupérer les informations du document
            $document = $this->getById($id);
            if (!$document) {
                return [
                    'success' => false,
                    'message' => 'Document introuvable'
                ];
            }
            
            // Vérifier les permissions
            if (!hasPermission('documents', 'delete') && $document['utilisateur_id'] != $_SESSION['user_id']) {
                return [
                    'success' => false,
                    'message' => 'Permission insuffisante pour supprimer ce document'
                ];
            }
            
            // Supprimer le fichier physique
            if (file_exists($document['chemin_fichier'])) {
                unlink($document['chemin_fichier']);
            }
            
            // Supprimer de la base de données
            $stmt = $this->pdo->prepare("DELETE FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity('suppression_document', 'documents', $id, [
                'nom_original' => $document['nom_original'],
                'chemin_fichier' => $document['chemin_fichier']
            ]);
            
            return [
                'success' => true,
                'message' => 'Document supprimé avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Télécharger un document
     */
    public function download($id) {
        try {
            $document = $this->getById($id);
            if (!$document) {
                return [
                    'success' => false,
                    'message' => 'Document introuvable'
                ];
            }
            
            if (!file_exists($document['chemin_fichier'])) {
                return [
                    'success' => false,
                    'message' => 'Fichier physique introuvable'
                ];
            }
            
            logActivity('telechargement_document', 'documents', $id, [
                'nom_original' => $document['nom_original']
            ]);
            
            // Headers pour le téléchargement
            header('Content-Type: ' . $document['type_mime']);
            header('Content-Disposition: attachment; filename="' . $document['nom_original'] . '"');
            header('Content-Length: ' . $document['taille_fichier']);
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            readfile($document['chemin_fichier']);
            exit;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du téléchargement : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Valider un fichier uploadé
     */
    private function validateFile($file) {
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'upload du fichier'
            ];
        }
        
        // Vérifier la taille
        if ($file['size'] > MAX_FILE_SIZE) {
            return [
                'success' => false,
                'message' => 'Le fichier est trop volumineux (max: ' . formatFileSize(MAX_FILE_SIZE) . ')'
            ];
        }
        
        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_FILE_TYPES)) {
            return [
                'success' => false,
                'message' => 'Type de fichier non autorisé. Extensions autorisées: ' . implode(', ', ALLOWED_FILE_TYPES)
            ];
        }
        
        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            return [
                'success' => false,
                'message' => 'Type de fichier non autorisé'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Obtenir les statistiques des documents
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total des documents
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM documents");
            $stats['total_documents'] = $stmt->fetch()['total'];
            
            // Documents par catégorie
            $stmt = $this->pdo->query("
                SELECT c.nom, COUNT(d.id) as count, c.couleur
                FROM categories c
                LEFT JOIN documents d ON c.id = d.categorie_id
                GROUP BY c.id, c.nom, c.couleur
                ORDER BY count DESC
            ");
            $stats['by_category'] = $stmt->fetchAll();
            
            // Taille totale des documents
            $stmt = $this->pdo->query("SELECT SUM(taille_fichier) as total_size FROM documents");
            $stats['total_size'] = $stmt->fetch()['total_size'] ?? 0;
            
            // Documents récents (derniers 7 jours)
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM documents 
                WHERE date_upload >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stats['recent_documents'] = $stmt->fetch()['count'];
            
            // Top utilisateurs
            $stmt = $this->pdo->query("
                SELECT u.nom, u.prenom, COUNT(d.id) as count
                FROM users u
                LEFT JOIN documents d ON u.id = d.utilisateur_id
                GROUP BY u.id, u.nom, u.prenom
                HAVING count > 0
                ORDER BY count DESC
                LIMIT 5
            ");
            $stats['top_users'] = $stmt->fetchAll();
            
            return $stats;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Compter les documents d'un utilisateur
     */
    public function countByUser($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM documents WHERE utilisateur_id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Compter les documents d'un utilisateur ce mois-ci
     */
    public function countByUserThisMonth($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM documents 
                WHERE utilisateur_id = ? 
                AND MONTH(date_upload) = MONTH(CURRENT_DATE()) 
                AND YEAR(date_upload) = YEAR(CURRENT_DATE())
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Calculer l'espace total utilisé par un utilisateur
     */
    public function getTotalSizeByUser($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT SUM(taille_fichier) as total FROM documents WHERE utilisateur_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
