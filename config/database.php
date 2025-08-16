
<?php
/**
 * Configuração de conexão com o banco de dados PostgreSQL
 * LJ-OS Sistema para Lava Jato - Replit
 */

// Configurações do banco de dados PostgreSQL
if (isset($_ENV['DATABASE_URL'])) {
    // Ambiente Replit com PostgreSQL
    $db_url = $_ENV['DATABASE_URL'];
    $db_parts = parse_url($db_url);
    
    define('DB_HOST', $db_parts['host']);
    define('DB_NAME', ltrim($db_parts['path'], '/'));
    define('DB_USER', $db_parts['user']);
    define('DB_PASS', $db_parts['pass']);
    define('DB_PORT', $db_parts['port'] ?? 5432);
} else {
    // Fallback para desenvolvimento local
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'lava_jato_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', 5432);
}

define('DB_CHARSET', 'utf8');

/**
 * Classe para gerenciar conexões com PostgreSQL
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
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
?>
