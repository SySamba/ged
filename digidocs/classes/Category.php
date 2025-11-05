<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Classe Category - Gestion des catégories de documents
 */
class Category {
    private $pdo;
    
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Obtenir toutes les catégories
     */
    public function getAll() {
        try {
            $stmt = $this->pdo->query("
                SELECT c.*, COUNT(d.id) as nb_documents
                FROM categories c
                LEFT JOIN documents d ON c.id = d.categorie_id
                GROUP BY c.id
                ORDER BY c.nom
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtenir une catégorie par ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, COUNT(d.id) as nb_documents
                FROM categories c
                LEFT JOIN documents d ON c.id = d.categorie_id
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Créer une nouvelle catégorie
     */
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO categories (nom, description, couleur, icone)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nom'],
                $data['description'] ?? '',
                $data['couleur'] ?? '#007bff',
                $data['icone'] ?? 'folder'
            ]);
            
            $categoryId = $this->pdo->lastInsertId();
            
            logActivity('creation_categorie', 'categories', $categoryId, [
                'nom' => $data['nom']
            ]);
            
            return [
                'success' => true,
                'category_id' => $categoryId,
                'message' => 'Catégorie créée avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mettre à jour une catégorie
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            if (isset($data['nom'])) {
                $fields[] = "nom = ?";
                $params[] = $data['nom'];
            }
            
            if (isset($data['description'])) {
                $fields[] = "description = ?";
                $params[] = $data['description'];
            }
            
            if (isset($data['couleur'])) {
                $fields[] = "couleur = ?";
                $params[] = $data['couleur'];
            }
            
            if (isset($data['icone'])) {
                $fields[] = "icone = ?";
                $params[] = $data['icone'];
            }
            
            if (empty($fields)) {
                return [
                    'success' => false,
                    'message' => 'Aucune donnée à mettre à jour'
                ];
            }
            
            $params[] = $id;
            
            $sql = "UPDATE categories SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            logActivity('modification_categorie', 'categories', $id, $data);
            
            return [
                'success' => true,
                'message' => 'Catégorie mise à jour avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer une catégorie
     */
    public function delete($id) {
        try {
            // Vérifier s'il y a des documents dans cette catégorie
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM documents WHERE categorie_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch()['count'];
            
            if ($count > 0) {
                return [
                    'success' => false,
                    'message' => 'Impossible de supprimer une catégorie contenant des documents'
                ];
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity('suppression_categorie', 'categories', $id);
            
            return [
                'success' => true,
                'message' => 'Catégorie supprimée avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ];
        }
    }
}
