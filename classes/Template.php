<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Classe Template - Gestion des modèles Canva
 */
class Template {
    private $pdo;
    
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Obtenir tous les modèles
     */
    public function getAll($type = null) {
        try {
            $sql = "SELECT * FROM modeles_canva WHERE actif = 1";
            $params = [];
            
            if ($type) {
                $sql .= " AND type = ?";
                $params[] = $type;
            }
            
            $sql .= " ORDER BY nom";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtenir un modèle par ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM modeles_canva WHERE id = ? AND actif = 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Générer un document à partir d'un modèle
     */
    public function generateDocument($modeleId, $data) {
        try {
            $modele = $this->getById($modeleId);
            if (!$modele) {
                return [
                    'success' => false,
                    'message' => 'Modèle introuvable'
                ];
            }
            
            // Remplacer les variables dans le template
            $html = $this->replaceVariables($modele['template_html'], $data);
            
            // Enregistrer le document généré
            $stmt = $this->pdo->prepare("
                INSERT INTO documents_generes (modele_id, utilisateur_id, nom_document, donnees_remplies)
                VALUES (?, ?, ?, ?)
            ");
            
            $nomDocument = $data['nom_document'] ?? ($modele['nom'] . '_' . date('Y-m-d_H-i-s'));
            
            $stmt->execute([
                $modeleId,
                $_SESSION['user_id'],
                $nomDocument,
                json_encode($data)
            ]);
            
            $documentId = $this->pdo->lastInsertId();
            
            // Générer le PDF si nécessaire
            $pdfPath = null;
            if (isset($data['generate_pdf']) && $data['generate_pdf']) {
                $pdfPath = $this->generatePDF($html, $nomDocument);
            }
            
            // Mettre à jour avec le chemin PDF si généré
            if ($pdfPath) {
                $stmt = $this->pdo->prepare("UPDATE documents_generes SET chemin_pdf = ? WHERE id = ?");
                $stmt->execute([$pdfPath, $documentId]);
            }
            
            logActivity('generation_document', 'documents_generes', $documentId, [
                'modele_id' => $modeleId,
                'nom_document' => $nomDocument
            ]);
            
            return [
                'success' => true,
                'document_id' => $documentId,
                'html' => $html,
                'pdf_path' => $pdfPath,
                'message' => 'Document généré avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Remplacer les variables dans le template
     */
    public function replaceVariables($template, $data) {
        // Remplacer les variables simples {{variable}}
        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $template = str_replace('{{' . $key . '}}', htmlspecialchars($value), $template);
            }
        }
        
        // Traitement spécial pour les tableaux (items de facture, etc.)
        if (isset($data['items']) && is_array($data['items'])) {
            $itemsHtml = '';
            foreach ($data['items'] as $item) {
                $itemsHtml .= '<tr>';
                $itemsHtml .= '<td>' . htmlspecialchars($item['designation'] ?? '') . '</td>';
                $itemsHtml .= '<td>' . htmlspecialchars($item['quantite'] ?? '') . '</td>';
                $itemsHtml .= '<td>' . htmlspecialchars($item['prix_unitaire'] ?? '') . '</td>';
                $itemsHtml .= '<td>' . htmlspecialchars($item['total'] ?? '') . '</td>';
                $itemsHtml .= '</tr>';
            }
            $template = str_replace('{{items}}', $itemsHtml, $template);
        }
        
        // Nettoyer les variables non remplacées
        $template = preg_replace('/\{\{[^}]+\}\}/', '', $template);
        
        return $template;
    }
    
    /**
     * Générer un PDF à partir du HTML
     */
    private function generatePDF($html, $nomDocument) {
        // Pour cette implémentation, nous sauvegardons juste le HTML
        // Dans un environnement de production, vous pourriez utiliser une bibliothèque comme TCPDF ou DomPDF
        
        $filename = uniqid() . '_' . $nomDocument . '.html';
        $filepath = UPLOAD_PATH . '/generated/' . $filename;
        
        // Ajouter le CSS pour l'impression
        $fullHtml = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($nomDocument) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .contrat-template, .facture-template, .bon-commande-template { max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h2 { color: #666; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .signatures { display: flex; justify-content: space-between; margin-top: 50px; }
        .signatures > div { text-align: center; width: 200px; border-top: 1px solid #333; padding-top: 10px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .parties { display: flex; justify-content: space-between; margin: 30px 0; }
        .parties > div { width: 45%; }
        .totaux { text-align: right; margin-top: 20px; }
        .conditions { margin: 30px 0; }
        .total { text-align: right; font-size: 1.2em; margin-top: 20px; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>' . $html . '</body>
</html>';
        
        if (file_put_contents($filepath, $fullHtml)) {
            return $filepath;
        }
        
        return null;
    }
    
    /**
     * Obtenir les documents générés par un utilisateur
     */
    public function getGeneratedDocuments($userId = null) {
        try {
            $sql = "
                SELECT dg.*, mc.nom as modele_nom, mc.type as modele_type
                FROM documents_generes dg
                JOIN modeles_canva mc ON dg.modele_id = mc.id
            ";
            $params = [];
            
            if ($userId) {
                $sql .= " WHERE dg.utilisateur_id = ?";
                $params[] = $userId;
            }
            
            $sql .= " ORDER BY dg.date_generation DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtenir un document généré par ID
     */
    public function getGeneratedDocument($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT dg.*, mc.nom as modele_nom, mc.type as modele_type, mc.template_html
                FROM documents_generes dg
                JOIN modeles_canva mc ON dg.modele_id = mc.id
                WHERE dg.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Supprimer un document généré
     */
    public function deleteGeneratedDocument($id) {
        try {
            // Récupérer le document
            $doc = $this->getGeneratedDocument($id);
            if (!$doc) {
                return [
                    'success' => false,
                    'message' => 'Document introuvable'
                ];
            }
            
            // Vérifier les permissions
            if ($doc['utilisateur_id'] != $_SESSION['user_id'] && !hasPermission('modeles', 'delete')) {
                return [
                    'success' => false,
                    'message' => 'Permission insuffisante'
                ];
            }
            
            // Supprimer le fichier PDF s'il existe
            if ($doc['chemin_pdf'] && file_exists($doc['chemin_pdf'])) {
                unlink($doc['chemin_pdf']);
            }
            
            // Supprimer de la base de données
            $stmt = $this->pdo->prepare("DELETE FROM documents_generes WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity('suppression_document_genere', 'documents_generes', $id);
            
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
     * Obtenir les statistiques des modèles
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total des modèles
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM modeles_canva WHERE actif = 1");
            $stats['total_modeles'] = $stmt->fetch()['total'];
            
            // Modèles par type
            $stmt = $this->pdo->query("
                SELECT type, COUNT(*) as count 
                FROM modeles_canva 
                WHERE actif = 1 
                GROUP BY type
            ");
            $stats['by_type'] = $stmt->fetchAll();
            
            // Documents générés
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM documents_generes");
            $stats['total_generated'] = $stmt->fetch()['total'];
            
            // Documents générés récemment
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM documents_generes 
                WHERE date_generation >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stats['recent_generated'] = $stmt->fetch()['count'];
            
            return $stats;
            
        } catch (Exception $e) {
            return [];
        }
    }
}
