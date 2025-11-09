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
            
            $nomDocument = $data['nom_document'] ?? ($modele['nom'] . '_' . date('Y-m-d_H-i-s'));
            
            // Générer le fichier HTML/PDF
            $fileResult = $this->generateDocumentFile($html, $nomDocument, $data['generate_pdf'] ?? false);
            if (!$fileResult['success']) {
                return $fileResult;
            }
            
            // Enregistrer dans documents_generes (table existante)
            $stmt = $this->pdo->prepare("
                INSERT INTO documents_generes (modele_id, utilisateur_id, nom_document, donnees_remplies, chemin_pdf)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $modeleId,
                $_SESSION['user_id'],
                $nomDocument,
                json_encode($data),
                $fileResult['file_path']
            ]);
            
            $documentGenereId = $this->pdo->lastInsertId();
            
            // Enregistrer aussi dans la table documents pour l'intégration avec le système GED
            $documentId = $this->saveToDocumentsTable($modele, $nomDocument, $fileResult, $data);
            
            logActivity('generation_document', 'documents_generes', $documentGenereId, [
                'modele_id' => $modeleId,
                'nom_document' => $nomDocument,
                'document_id' => $documentId
            ]);
            
            return [
                'success' => true,
                'document_id' => $documentGenereId,
                'ged_document_id' => $documentId,
                'html' => $html,
                'file_path' => $fileResult['file_path'],
                'message' => 'Document généré et enregistré avec succès'
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
     * Générer un fichier document (HTML/PDF) à partir du HTML
     */
    private function generateDocumentFile($html, $nomDocument, $generatePdf = false) {
        try {
            // Créer le répertoire s'il n'existe pas
            $generatedDir = UPLOAD_PATH . '/documents';
            if (!is_dir($generatedDir)) {
                mkdir($generatedDir, 0755, true);
            }
            
            // Toujours générer en PDF pour les documents générés
            $extension = 'pdf';
            $mimeType = 'application/pdf';
            
            // Générer un nom de fichier sécurisé
            $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomDocument) . '.' . $extension;
            $filepath = $generatedDir . '/' . $filename;
            
            // Préparer le contenu HTML complet
            $fullHtml = $this->prepareFullHtml($html, $nomDocument);
            
            // Générer le PDF à partir du HTML
            $success = $this->generatePdfFromHtml($fullHtml, $filepath);
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la création du fichier'
                ];
            }
            
            return [
                'success' => true,
                'file_path' => $filepath,
                'filename' => $filename,
                'mime_type' => $mimeType,
                'file_size' => filesize($filepath)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération du fichier : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Préparer le HTML complet avec CSS
     */
    private function prepareFullHtml($html, $nomDocument) {
        return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($nomDocument) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
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
    }
    
    /**
     * Enregistrer le document généré dans la table documents pour l'intégration GED
     */
    private function saveToDocumentsTable($modele, $nomDocument, $fileResult, $data) {
        try {
            // Déterminer la catégorie selon le type de modèle
            $categorieId = $this->getCategoryIdByType($modele['type']);
            
            // Préparer les mots-clés
            $motsCles = $this->generateKeywords($modele['type'], $data);
            
            // Préparer la description
            $description = "Document généré automatiquement à partir du modèle '" . $modele['nom'] . "'";
            if (isset($data['description']) && !empty($data['description'])) {
                $description .= " - " . $data['description'];
            }
            
            // Insérer dans la table documents
            $stmt = $this->pdo->prepare("
                INSERT INTO documents (nom_original, nom_fichier, chemin_fichier, type_mime, taille_fichier, 
                                     categorie_id, utilisateur_id, mots_cles, description, source_generation)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // S'assurer que le nom original a l'extension .pdf
            $nomOriginalCorrige = $nomDocument;
            if (!preg_match('/\.pdf$/i', $nomOriginalCorrige)) {
                $nomOriginalCorrige .= '.pdf';
            }
            
            $stmt->execute([
                $nomOriginalCorrige,
                $fileResult['filename'],
                $fileResult['file_path'],
                $fileResult['mime_type'],
                $fileResult['file_size'],
                $categorieId,
                $_SESSION['user_id'],
                $motsCles,
                $description,
                'template_' . $modele['type']
            ]);
            
            return $this->pdo->lastInsertId();
            
        } catch (Exception $e) {
            // Log l'erreur mais ne pas faire échouer la génération
            error_log("Erreur lors de l'enregistrement dans documents: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir l'ID de catégorie selon le type de modèle
     */
    private function getCategoryIdByType($type) {
        try {
            $categoryNames = [
                'contrat' => 'Contrats',
                'facture' => 'Factures', 
                'bon_commande' => 'Bons de commande'
            ];
            
            $categoryName = $categoryNames[$type] ?? 'Documents générés';
            
            // Chercher la catégorie existante
            $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE nom = ? LIMIT 1");
            $stmt->execute([$categoryName]);
            $category = $stmt->fetch();
            
            if ($category) {
                return $category['id'];
            }
            
            // Créer la catégorie si elle n'existe pas
            $colors = [
                'contrat' => '#007bff',
                'facture' => '#28a745',
                'bon_commande' => '#ffc107'
            ];
            
            $icons = [
                'contrat' => 'fas fa-handshake',
                'facture' => 'fas fa-receipt',
                'bon_commande' => 'fas fa-shopping-cart'
            ];
            
            $stmt = $this->pdo->prepare("
                INSERT INTO categories (nom, description, couleur, icone, utilisateur_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $categoryName,
                'Catégorie créée automatiquement pour les ' . strtolower($categoryName),
                $colors[$type] ?? '#6c757d',
                $icons[$type] ?? 'fas fa-file',
                $_SESSION['user_id']
            ]);
            
            return $this->pdo->lastInsertId();
            
        } catch (Exception $e) {
            // Retourner null si erreur, le document sera sans catégorie
            return null;
        }
    }
    
    /**
     * Générer des mots-clés automatiques selon le type et les données
     */
    private function generateKeywords($type, $data) {
        $keywords = [$type];
        
        // Ajouter des mots-clés spécifiques selon le type
        switch ($type) {
            case 'contrat':
                $keywords[] = 'emploi';
                if (isset($data['type_contrat'])) {
                    $keywords[] = $data['type_contrat'];
                }
                if (isset($data['nom_employe'])) {
                    $keywords[] = $data['nom_employe'];
                }
                break;
                
            case 'facture':
                $keywords[] = 'facturation';
                if (isset($data['numero_facture'])) {
                    $keywords[] = $data['numero_facture'];
                }
                if (isset($data['nom_client'])) {
                    $keywords[] = $data['nom_client'];
                }
                break;
                
            case 'bon_commande':
                $keywords[] = 'commande';
                if (isset($data['numero_bon'])) {
                    $keywords[] = $data['numero_bon'];
                }
                if (isset($data['fournisseur'])) {
                    $keywords[] = $data['fournisseur'];
                }
                break;
        }
        
        // Ajouter la date
        $keywords[] = date('Y-m');
        
        return implode(', ', array_unique($keywords));
    }
    
    /**
     * Générer un PDF à partir du HTML en utilisant DomPDF
     */
    private function generatePdfFromHtml($html, $filepath) {
        try {
            // Vérifier si DomPDF est disponible
            if (!class_exists('Dompdf\Dompdf')) {
                // Si DomPDF n'est pas disponible, utiliser une solution alternative
                return $this->generatePdfAlternative($html, $filepath);
            }
            
            // Utiliser DomPDF si disponible
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $output = $dompdf->output();
            return file_put_contents($filepath, $output) !== false;
            
        } catch (Exception $e) {
            error_log("Erreur génération PDF: " . $e->getMessage());
            return $this->generatePdfAlternative($html, $filepath);
        }
    }
    
    /**
     * Solution alternative pour générer un PDF (utilise wkhtmltopdf si disponible, sinon HTML)
     */
    private function generatePdfAlternative($html, $filepath) {
        try {
            // Essayer wkhtmltopdf en ligne de commande
            $tempHtmlFile = tempnam(sys_get_temp_dir(), 'doc_') . '.html';
            file_put_contents($tempHtmlFile, $html);
            
            // Commande wkhtmltopdf (si installé sur le serveur)
            $command = "wkhtmltopdf --page-size A4 --margin-top 0.75in --margin-right 0.75in --margin-bottom 0.75in --margin-left 0.75in '$tempHtmlFile' '$filepath' 2>&1";
            $output = shell_exec($command);
            
            // Nettoyer le fichier temporaire
            unlink($tempHtmlFile);
            
            // Vérifier si le PDF a été créé
            if (file_exists($filepath) && filesize($filepath) > 0) {
                return true;
            }
            
            // Si wkhtmltopdf échoue, créer un PDF simple avec TCPDF ou FPDF
            return $this->generateSimplePdf($html, $filepath);
            
        } catch (Exception $e) {
            error_log("Erreur PDF alternative: " . $e->getMessage());
            return $this->generateSimplePdf($html, $filepath);
        }
    }
    
    /**
     * Générer un PDF simple en extrayant le texte du HTML
     */
    private function generateSimplePdf($html, $filepath) {
        try {
            // Solution de fallback : créer un PDF basique avec le contenu texte
            require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
            
            if (class_exists('TCPDF')) {
                $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->SetCreator('DigiDocs');
                $pdf->SetTitle('Document généré');
                $pdf->SetMargins(15, 15, 15);
                $pdf->AddPage();
                
                // Nettoyer le HTML et extraire le contenu
                $content = strip_tags($html, '<h1><h2><h3><p><br><strong><b><em><i><ul><ol><li>');
                $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
                
                $pdf->writeHTML($content, true, false, true, false, '');
                $pdf->Output($filepath, 'F');
                
                return file_exists($filepath);
            }
            
            // Dernier recours : sauvegarder en HTML avec extension PDF
            return file_put_contents($filepath, $html) !== false;
            
        } catch (Exception $e) {
            error_log("Erreur PDF simple: " . $e->getMessage());
            // Dernier recours : sauvegarder en HTML avec extension PDF
            return file_put_contents($filepath, $html) !== false;
        }
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
