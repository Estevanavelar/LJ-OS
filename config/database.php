
<?php
/**
 * Sistema de Banco de Dados Multi-SGBD
 * Suporte para MySQL, PostgreSQL e SQLite
 */

// Configurações de banco baseadas no ambiente
$db_config = [
    'type' => getenv('DB_TYPE') ?: 'sqlite',
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: null,
    'name' => getenv('DB_NAME') ?: 'lj_os',
    'user' => getenv('DB_USER') ?: '',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    'sqlite_path' => __DIR__ . '/../database/lj_os.db'
];

/**
 * Classe de Banco de Dados Universal
 */
class DatabaseManager {
    private static $instance = null;
    private $connection;
    private $db_type;
    
    private function __construct() {
        global $db_config;
        $this->db_type = $db_config['type'];
        $this->connect($db_config);
    }
    
    private function connect($config) {
        try {
            switch ($config['type']) {
                case 'mysql':
                    $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";
                    if ($config['port']) {
                        $dsn .= ";port={$config['port']}";
                    }
                    break;
                    
                case 'postgresql':
                case 'pgsql':
                    $dsn = "pgsql:host={$config['host']};dbname={$config['name']}";
                    if ($config['port']) {
                        $dsn .= ";port={$config['port']}";
                    }
                    break;
                    
                case 'sqlite':
                default:
                    // Criar diretório se não existir
                    $dir = dirname($config['sqlite_path']);
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $dsn = "sqlite:{$config['sqlite_path']}";
                    break;
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            if ($config['type'] === 'sqlite') {
                $this->connection = new PDO($dsn, null, null, $options);
                // Configurações específicas do SQLite
                $this->connection->exec("PRAGMA foreign_keys = ON");
                $this->connection->exec("PRAGMA journal_mode = WAL");
                $this->connection->exec("PRAGMA synchronous = NORMAL");
            } else {
                $this->connection = new PDO($dsn, $config['user'], $config['pass'], $options);
            }
            
        } catch (PDOException $e) {
            error_log("Erro de conexão com banco: " . $e->getMessage());
            
            // Fallback para SQLite se outro banco falhar
            if ($config['type'] !== 'sqlite') {
                try {
                    $sqlite_dsn = "sqlite:{$config['sqlite_path']}";
                    $this->connection = new PDO($sqlite_dsn, null, null, $options);
                    $this->db_type = 'sqlite';
                    $this->connection->exec("PRAGMA foreign_keys = ON");
                    error_log("Usando SQLite como fallback");
                } catch (PDOException $e2) {
                    die("Erro crítico: Não foi possível conectar a nenhum banco de dados.");
                }
            } else {
                die("Erro de conexão com o banco de dados SQLite: " . $e->getMessage());
            }
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
    
    public function getType() {
        return $this->db_type;
    }
    
    /**
     * Executa query compatível com múltiplos SGBDs
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Erro na query: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * Obtém último ID inserido (compatível com diferentes SGBDs)
     */
    public function lastInsertId($sequence = null) {
        if ($this->db_type === 'pgsql' && $sequence) {
            return $this->connection->lastInsertId($sequence);
        }
        return $this->connection->lastInsertId();
    }
    
    /**
     * Inicia transação
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma transação
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Desfaz transação
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Converte SQL para o SGBD específico
     */
    public function adaptSQL($sql) {
        switch ($this->db_type) {
            case 'mysql':
                // Conversões específicas do MySQL
                $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
                $sql = str_replace('INTEGER PRIMARY KEY', 'INT PRIMARY KEY AUTO_INCREMENT', $sql);
                break;
                
            case 'pgsql':
                // Conversões específicas do PostgreSQL
                $sql = str_replace('AUTOINCREMENT', '', $sql);
                $sql = str_replace('INTEGER PRIMARY KEY', 'SERIAL PRIMARY KEY', $sql);
                $sql = str_replace('DATETIME', 'TIMESTAMP', $sql);
                break;
                
            case 'sqlite':
            default:
                // SQLite é o padrão
                break;
        }
        
        return $sql;
    }
    
    private function __clone() {}
    public function __wakeup() {}
}

// Funções helper globais
function getDB() {
    return DatabaseManager::getInstance()->getConnection();
}

function getDBManager() {
    return DatabaseManager::getInstance();
}

function dbQuery($sql, $params = []) {
    return DatabaseManager::getInstance()->query($sql, $params);
}
