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
            
            // Filtre par statut (par défaut, ne montrer que les documents actifs)
            if (isset($filters['statut'])) {
                $sql .= " AND d.statut = ?";
                $params[] = $filters['statut'];
            } else {
                $sql .= " AND d.statut = 'actif'";
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
     * Mettre à jour un document avec possibilité de remplacer le fichier
     */
    public function updateWithFile($id, $data, $newFile = null) {
        try {
            // Récupérer le document actuel
            $currentDoc = $this->getById($id);
            if (!$currentDoc) {
                return [
                    'success' => false,
                    'message' => 'Document introuvable'
                ];
            }
            
            // Vérifier les permissions
            if (!hasPermission('documents', 'update') && $currentDoc['utilisateur_id'] != $_SESSION['user_id']) {
                return [
                    'success' => false,
                    'message' => 'Permission insuffisante pour modifier ce document'
                ];
            }
            
            $fields = [];
            $params = [];
            $oldFilePath = null;
            
            // Traiter le nouveau fichier si fourni
            if ($newFile) {
                $validation = $this->validateFile($newFile);
                if (!$validation['success']) {
                    return $validation;
                }
                
                // Générer un nom de fichier sécurisé
                $extension = strtolower(pathinfo($newFile['name'], PATHINFO_EXTENSION));
                $filename = uniqid() . '_' . time() . '.' . $extension;
                $filepath = UPLOAD_PATH . '/documents/' . $filename;
                
                // Déplacer le nouveau fichier
                if (!move_uploaded_file($newFile['tmp_name'], $filepath)) {
                    return [
                        'success' => false,
                        'message' => 'Erreur lors du téléchargement du nouveau fichier'
                    ];
                }
                
                // Préparer les champs pour la mise à jour du fichier
                $fields[] = "nom_original = ?";
                $params[] = $newFile['name'];
                
                $fields[] = "nom_fichier = ?";
                $params[] = $filename;
                
                $fields[] = "chemin_fichier = ?";
                $params[] = $filepath;
                
                $fields[] = "type_mime = ?";
                $params[] = $newFile['type'];
                
                $fields[] = "taille_fichier = ?";
                $params[] = $newFile['size'];
                
                // Marquer l'ancien fichier pour suppression
                $oldFilePath = $currentDoc['chemin_fichier'];
            }
            
            // Ajouter les autres champs de mise à jour
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
            
            // Ajouter la date de modification
            $fields[] = "date_modification = NOW()";
            $params[] = $id;
            
            // Exécuter la mise à jour
            $sql = "UPDATE documents SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Supprimer l'ancien fichier si un nouveau a été uploadé
            if ($oldFilePath && file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
            
            // Logger l'activité
            $logData = $data;
            if ($newFile) {
                $logData['nouveau_fichier'] = $newFile['name'];
                $logData['ancien_fichier'] = $currentDoc['nom_original'];
            }
            
            logActivity('modification_document_complet', 'documents', $id, $logData);
            
            $message = 'Document mis à jour avec succès';
            if ($newFile) {
                $message .= ' (fichier remplacé)';
            }
            
            return [
                'success' => true,
                'message' => $message
            ];
            
        } catch (Exception $e) {
            // Supprimer le nouveau fichier en cas d'erreur
            if (isset($filepath) && file_exists($filepath)) {
                unlink($filepath);
            }
            
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
    
    /**
     * Archiver un document
     */
    public function archive($id, $motif = null) {
        try {
            // Récupérer le document
            $document = $this->getById($id);
            if (!$document) {
                return [
                    'success' => false,
                    'message' => 'Document introuvable'
                ];
            }
            
            // Vérifier les permissions
            if (!hasPermission('documents', 'update') && $document['utilisateur_id'] != $_SESSION['user_id']) {
                return [
                    'success' => false,
                    'message' => 'Permission insuffisante pour archiver ce document'
                ];
            }
            
            // Vérifier si le document n'est pas déjà archivé
            if ($document['statut'] === 'archive') {
                return [
                    'success' => false,
                    'message' => 'Ce document est déjà archivé'
                ];
            }
            
            // Archiver le document
            $stmt = $this->pdo->prepare("
                UPDATE documents 
                SET statut = 'archive', 
                    date_archivage = NOW(), 
                    motif_archivage = ?, 
                    archive_par = ?
                WHERE id = ?
            ");
            $stmt->execute([$motif, $_SESSION['user_id'], $id]);
            
            // Enregistrer dans l'historique
            $this->logArchivageAction($id, 'archive', $motif);
            
            logActivity('archivage_document', 'documents', $id, [
                'motif' => $motif,
                'nom_original' => $document['nom_original']
            ]);
            
            return [
                'success' => true,
                'message' => 'Document archivé avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'archivage : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Désarchiver un document
     */
    public function unarchive($id) {
        try {
            // Récupérer le document
            $document = $this->getById($id);
            if (!$document) {
                return [
                    'success' => false,
                    'message' => 'Document introuvable'
                ];
            }
            
            // Vérifier les permissions
            if (!hasPermission('documents', 'update') && $document['utilisateur_id'] != $_SESSION['user_id']) {
                return [
                    'success' => false,
                    'message' => 'Permission insuffisante pour désarchiver ce document'
                ];
            }
            
            // Vérifier si le document est archivé
            if ($document['statut'] !== 'archive') {
                return [
                    'success' => false,
                    'message' => 'Ce document n\'est pas archivé'
                ];
            }
            
            // Désarchiver le document
            $stmt = $this->pdo->prepare("
                UPDATE documents 
                SET statut = 'actif', 
                    date_archivage = NULL, 
                    motif_archivage = NULL, 
                    archive_par = NULL
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            // Enregistrer dans l'historique
            $this->logArchivageAction($id, 'desarchive');
            
            logActivity('desarchivage_document', 'documents', $id, [
                'nom_original' => $document['nom_original']
            ]);
            
            return [
                'success' => true,
                'message' => 'Document désarchivé avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du désarchivage : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer définitivement un document (soft delete)
     */
    public function softDelete($id, $motif = null) {
        try {
            // Récupérer le document
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
            
            // Marquer comme supprimé
            $stmt = $this->pdo->prepare("
                UPDATE documents 
                SET statut = 'supprime', 
                    date_archivage = NOW(), 
                    motif_archivage = ?, 
                    archive_par = ?
                WHERE id = ?
            ");
            $stmt->execute([$motif, $_SESSION['user_id'], $id]);
            
            // Enregistrer dans l'historique
            $this->logArchivageAction($id, 'supprime', $motif);
            
            logActivity('suppression_soft_document', 'documents', $id, [
                'motif' => $motif,
                'nom_original' => $document['nom_original']
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
     * Restaurer un document supprimé
     */
    public function restore($id) {
        try {
            // Récupérer le document
            $document = $this->getById($id);
            if (!$document) {
                return [
                    'success' => false,
                    'message' => 'Document introuvable'
                ];
            }
            
            // Vérifier les permissions
            if (!hasPermission('documents', 'delete')) {
                return [
                    'success' => false,
                    'message' => 'Permission insuffisante pour restaurer ce document'
                ];
            }
            
            // Vérifier si le document est supprimé
            if ($document['statut'] !== 'supprime') {
                return [
                    'success' => false,
                    'message' => 'Ce document n\'est pas supprimé'
                ];
            }
            
            // Restaurer le document
            $stmt = $this->pdo->prepare("
                UPDATE documents 
                SET statut = 'actif', 
                    date_archivage = NULL, 
                    motif_archivage = NULL, 
                    archive_par = NULL
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            // Enregistrer dans l'historique
            $this->logArchivageAction($id, 'restaure');
            
            logActivity('restauration_document', 'documents', $id, [
                'nom_original' => $document['nom_original']
            ]);
            
            return [
                'success' => true,
                'message' => 'Document restauré avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la restauration : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Archiver en masse des documents
     */
    public function archiveBulk($documentIds, $motif = null) {
        try {
            if (empty($documentIds)) {
                return [
                    'success' => false,
                    'message' => 'Aucun document sélectionné'
                ];
            }
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            foreach ($documentIds as $id) {
                $result = $this->archive($id, $motif);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Document ID $id : " . $result['message'];
                }
            }
            
            $message = "$successCount document(s) archivé(s) avec succès";
            if ($errorCount > 0) {
                $message .= ", $errorCount erreur(s)";
            }
            
            return [
                'success' => $successCount > 0,
                'message' => $message,
                'details' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'archivage en masse : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtenir les documents par statut
     */
    public function getByStatus($statut, $filters = []) {
        try {
            $filters['statut'] = $statut;
            return $this->search($filters);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtenir les statistiques d'archivage
     */
    public function getArchivingStats() {
        try {
            $stats = [];
            
            // Statistiques par statut
            $stmt = $this->pdo->query("
                SELECT statut, COUNT(*) as count, SUM(taille_fichier) as total_size
                FROM documents 
                GROUP BY statut
            ");
            $stats['by_status'] = $stmt->fetchAll();
            
            // Documents archivés récemment (7 derniers jours)
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM documents 
                WHERE statut = 'archive' 
                AND date_archivage >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stats['recent_archived'] = $stmt->fetch()['count'];
            
            // Top des motifs d'archivage
            $stmt = $this->pdo->query("
                SELECT motif_archivage, COUNT(*) as count 
                FROM documents 
                WHERE statut IN ('archive', 'supprime') 
                AND motif_archivage IS NOT NULL
                GROUP BY motif_archivage 
                ORDER BY count DESC 
                LIMIT 5
            ");
            $stats['top_motifs'] = $stmt->fetchAll();
            
            // Documents candidats à l'archivage (plus de 12 mois)
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM documents 
                WHERE statut = 'actif' 
                AND date_upload < DATE_SUB(NOW(), INTERVAL 12 MONTH)
            ");
            $stats['candidates_archivage'] = $stmt->fetch()['count'];
            
            return $stats;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Appliquer les règles d'archivage automatique
     */
    public function applyAutoArchivingRules() {
        try {
            $archivedCount = 0;
            
            // Récupérer les règles actives
            $stmt = $this->pdo->query("
                SELECT * FROM regles_archivage 
                WHERE actif = TRUE 
                ORDER BY categorie_id IS NULL, duree_mois DESC
            ");
            $rules = $stmt->fetchAll();
            
            foreach ($rules as $rule) {
                // Construire la requête selon la règle
                $sql = "
                    SELECT id, nom_original 
                    FROM documents 
                    WHERE statut = 'actif' 
                    AND date_upload < DATE_SUB(NOW(), INTERVAL ? MONTH)
                ";
                $params = [$rule['duree_mois']];
                
                if ($rule['categorie_id']) {
                    $sql .= " AND categorie_id = ?";
                    $params[] = $rule['categorie_id'];
                }
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $candidates = $stmt->fetchAll();
                
                // Archiver les documents candidats
                foreach ($candidates as $candidate) {
                    $motif = "Archivage automatique - Règle: " . $rule['nom'];
                    $result = $this->archive($candidate['id'], $motif);
                    if ($result['success']) {
                        $archivedCount++;
                    }
                }
            }
            
            return [
                'success' => true,
                'message' => "$archivedCount document(s) archivé(s) automatiquement",
                'archived_count' => $archivedCount
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'archivage automatique : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Enregistrer une action d'archivage dans l'historique
     */
    private function logArchivageAction($documentId, $action, $motif = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO historique_archivage (document_id, action, utilisateur_id, motif)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$documentId, $action, $_SESSION['user_id'], $motif]);
        } catch (Exception $e) {
            // Log silencieux - ne pas faire échouer l'opération principale
        }
    }
}
