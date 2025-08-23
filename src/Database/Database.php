<?php

namespace LJOS\Database;

use PDO;
use PDOException;
use Exception;

/**
 * Classe de conexão com banco de dados
 * 
 * @package LJOS\Database
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;
    
    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct()
    {
        $this->config = require_once __DIR__ . '/../../config/config.php';
        $this->connect();
    }
    
    /**
     * Obtém instância única da classe
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Estabelece conexão com o banco de dados
     */
    private function connect(): void
    {
        try {
            $dbPath = __DIR__ . '/../../database/lj_os.db';
            
            // Criar diretório do banco se não existir
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            // Conectar ao SQLite
            $this->connection = new PDO("sqlite:{$dbPath}");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Habilitar foreign keys
            $this->connection->exec('PRAGMA foreign_keys = ON');
            
            // Configurar timezone
            $this->connection->exec('PRAGMA timezone = "America/Sao_Paulo"');
            
            // Inicializar banco se necessário
            $this->initializeDatabase();
            
        } catch (PDOException $e) {
            throw new Exception("Erro de conexão com banco: " . $e->getMessage());
        }
    }
    
    /**
     * Inicializa o banco de dados com as tabelas
     */
    private function initializeDatabase(): void
    {
        try {
            // Verificar se as tabelas já existem
            $stmt = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'");
            if ($stmt->fetch()) {
                return; // Banco já inicializado
            }
            
            // Executar script de criação das tabelas
            $sqlFile = __DIR__ . '/../../sql/schema.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $this->connection->exec($sql);
            }
            
        } catch (Exception $e) {
            throw new Exception("Erro ao inicializar banco: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém a conexão PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
    
    /**
     * Executa uma query SQL
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Erro na query: " . $e->getMessage());
        }
    }
    
    /**
     * Executa uma query de inserção/atualização/exclusão
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Erro na execução: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém último ID inserido
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Inicia uma transação
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }
    
    /**
     * Reverte uma transação
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }
    
    /**
     * Verifica se está em transação
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
    
    /**
     * Fecha a conexão
     */
    public function close(): void
    {
        $this->connection = null;
    }
    
    /**
     * Previne clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Previne desserialização da instância
     */
    public function __wakeup()
    {
        throw new Exception("Não é possível desserializar um singleton");
    }
}
