<?php
/**
 * Configuration de base de données - Exemple
 * 
 * INSTRUCTIONS :
 * 1. Copiez ce fichier vers config/database.php
 * 2. Modifiez les valeurs selon votre environnement
 * 3. Le fichier config/database.php est ignoré par Git pour la sécurité
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'digidocs');
define('DB_USER', 'votre_utilisateur_db');
define('DB_PASS', 'votre_mot_de_passe_db');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

/**
 * Classe Database pour la connexion PDO
 */
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $port = DB_PORT;
    private $pdo;

    /**
     * Obtenir la connexion à la base de données
     */
    public function getConnection() {
        $this->pdo = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->charset
            ];

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
            die();
        }

        return $this->pdo;
    }

    /**
     * Tester la connexion
     */
    public function testConnection() {
        try {
            $pdo = $this->getConnection();
            return $pdo ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Test de connexion (optionnel - à supprimer en production)
if (defined('TEST_DB_CONNECTION') && TEST_DB_CONNECTION === true) {
    $db = new Database();
    if ($db->testConnection()) {
        echo "✅ Connexion à la base de données réussie !";
    } else {
        echo "❌ Échec de la connexion à la base de données.";
    }
}
?>
