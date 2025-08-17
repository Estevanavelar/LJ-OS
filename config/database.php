
<?php
/**
 * Configuração de conexão com o banco de dados
 * LJ-OS Sistema para Lava Jato - Replit (SQLite)
 */

// Usar SQLite no Replit para simplificar
define('DB_TYPE', 'sqlite');
define('DB_PATH', __DIR__ . '/../database/lj_os.db');
define('DB_CHARSET', 'utf8');

/**
 * Classe para gerenciar conexões com SQLite
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            // Criar diretório do banco se não existir
            $db_dir = dirname(DB_PATH);
            if (!file_exists($db_dir)) {
                mkdir($db_dir, 0755, true);
            }
            
            $dsn = "sqlite:" . DB_PATH;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, null, null, $options);
            
            // Configurações específicas do SQLite
            $this->connection->exec("PRAGMA foreign_keys = ON");
            $this->connection->exec("PRAGMA journal_mode = WAL");
            
        } catch (PDOException $e) {
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            die("Erro de conexão com o banco de dados. Tente novamente mais tarde.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function __clone() {}
    public function __wakeup() {}
}

function getDB() {
    return Database::getInstance()->getConnection();
}
