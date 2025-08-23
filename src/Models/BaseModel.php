<?php

namespace LJOS\Models;

use LJOS\Database\Database;
use PDO;
use Exception;

/**
 * Classe base para todos os modelos
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
abstract class BaseModel
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Busca todos os registros
     */
    public function all(array $columns = ['*']): array
    {
        $columnsStr = implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table}";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca registro por ID
     */
    public function find(int $id, array $columns = ['*'])
    {
        $columnsStr = implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca registro por campo específico
     */
    public function findBy(string $field, $value, array $columns = ['*'])
    {
        $columnsStr = implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE {$field} = ?";
        
        $stmt = $this->db->query($sql, [$value]);
        return $stmt->fetch();
    }
    
    /**
     * Busca múltiplos registros por campo específico
     */
    public function findAllBy(string $field, $value, array $columns = ['*']): array
    {
        $columnsStr = implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE {$field} = ?";
        
        $stmt = $this->db->query($sql, [$value]);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca com condições personalizadas
     */
    public function where(string $field, string $operator, $value, array $columns = ['*']): array
    {
        $columnsStr = implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE {$field} {$operator} ?";
        
        $stmt = $this->db->query($sql, [$value]);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca com múltiplas condições
     */
    public function whereMultiple(array $conditions, array $columns = ['*']): array
    {
        $columnsStr = implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE ";
        
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $condition) {
            $whereClauses[] = "{$condition[0]} {$condition[1]} ?";
            $params[] = $condition[2];
        }
        
        $sql .= implode(' AND ', $whereClauses);
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Cria novo registro
     */
    public function create(array $data): int
    {
        // Filtrar apenas campos preenchíveis
        $data = $this->filterFillable($data);
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->db->execute($sql, array_values($data));
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Atualiza registro existente
     */
    public function update(int $id, array $data): bool
    {
        // Filtrar apenas campos preenchíveis
        $data = $this->filterFillable($data);
        
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        
        $params = array_values($data);
        $params[] = $id;
        
        $affectedRows = $this->db->execute($sql, $params);
        return $affectedRows > 0;
    }
    
    /**
     * Remove registro
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        
        $affectedRows = $this->db->execute($sql, [$id]);
        return $affectedRows > 0;
    }
    
    /**
     * Conta total de registros
     */
    public function count(string $field = '*'): int
    {
        $sql = "SELECT COUNT({$field}) as total FROM {$this->table}";
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        return (int) $result['total'];
    }
    
    /**
     * Busca com paginação
     */
    public function paginate(int $page = 1, int $perPage = 15, array $columns = ['*']): array
    {
        $offset = ($page - 1) * $perPage;
        
        $columnsStr = implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} LIMIT ? OFFSET ?";
        
        $stmt = $this->db->query($sql, [$perPage, $offset]);
        $data = $stmt->fetchAll();
        
        $total = $this->count();
        $totalPages = ceil($total / $perPage);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Busca com ordenação
     */
    public function orderBy(string $field, string $direction = 'ASC', array $columns = ['*']): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $columnsStr = implode(', ', $columns);
        
        $sql = "SELECT {$columnsStr} FROM {$this->table} ORDER BY {$field} {$direction}";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca com limite
     */
    public function limit(int $limit, array $columns = ['*']): array
    {
        $columnsStr = implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Executa query SQL personalizada
     */
    public function rawQuery(string $sql, array $params = []): array
    {
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Executa query SQL personalizada (uma linha)
     */
    public function rawQueryOne(string $sql, array $params = [])
    {
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Filtra dados pelos campos preenchíveis
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Oculta campos sensíveis
     */
    protected function hideFields(array $data): array
    {
        if (empty($this->hidden)) {
            return $data;
        }
        
        return array_diff_key($data, array_flip($this->hidden));
    }
    
    /**
     * Aplica conversões de tipo
     */
    protected function applyCasts(array $data): array
    {
        if (empty($this->casts)) {
            return $data;
        }
        
        foreach ($this->casts as $field => $cast) {
            if (isset($data[$field])) {
                $data[$field] = $this->castValue($data[$field], $cast);
            }
        }
        
        return $data;
    }
    
    /**
     * Converte valor para tipo específico
     */
    protected function castValue($value, string $cast)
    {
        switch ($cast) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'string':
                return (string) $value;
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'datetime':
                return new \DateTime($value);
            case 'date':
                return new \DateTime($value);
            default:
                return $value;
        }
    }
    
    /**
     * Inicia transação
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }
    
    /**
     * Confirma transação
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }
    
    /**
     * Reverte transação
     */
    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}
