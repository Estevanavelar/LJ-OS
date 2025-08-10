<?php
/**
 * Configuração de conexão com o banco de dados
 * LJ-OS Sistema para Lava Jato
 */

require_once __DIR__ . '/../src/Env.php';
\LJOS\Env::load(__DIR__ . '/../.env');

// Configurações do banco de dados
define('DB_HOST', \LJOS\Env::get('DB_HOST', 'localhost'));
define('DB_NAME', \LJOS\Env::get('DB_NAME', 'lava_jato_db'));
define('DB_USER', \LJOS\Env::get('DB_USER', 'root'));
define('DB_PASS', \LJOS\Env::get('DB_PASS', ''));
define('DB_CHARSET', \LJOS\Env::get('DB_CHARSET', 'utf8mb4'));

/**
 * Classe para gerenciar conexões com o banco de dados
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        try {
            // String de conexão PDO
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            // Opções de configuração do PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            // Criar conexão
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log do erro e exibição de mensagem amigável
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            die("Erro de conexão com o banco de dados. Tente novamente mais tarde.");
        }
    }
    
    /**
     * Método para obter instância única da classe (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Método para obter a conexão PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Previne clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Previne deserialização da instância
     */
    public function __wakeup() {}
}

/**
 * Função auxiliar para obter conexão com o banco
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
?>