<?php
/**
 * Configuration de la base de données DigiDocs
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'u588247422_geddb';
    private $username = 'u588247422_geduser';
    private $password = 'Khombole2021';
    private $charset = 'utf8mb4';
    private $pdo;

    /**
     * Connexion à la base de données
     */
    public function getConnection() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
                ];
                
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        
        return $this->pdo;
    }

    /**
     * Fermer la connexion
     */
    public function closeConnection() {
        $this->pdo = null;
    }

    /**
     * Exécuter un fichier SQL
     */
    public function executeSqlFile($filepath) {
        if (!file_exists($filepath)) {
            throw new Exception("Fichier SQL introuvable : " . $filepath);
        }

        $sql = file_get_contents($filepath);
        $statements = explode(';', $sql);
        
        $pdo = $this->getConnection();
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
    }
}
