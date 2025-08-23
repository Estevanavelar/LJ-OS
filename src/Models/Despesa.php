<?php

namespace LJOS\Models;

/**
 * Modelo de Despesa
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Despesa extends BaseModel
{
    protected $table = 'despesas';
    protected $primaryKey = 'id_despesa';
    
    protected $fillable = [
        'id_categoria',
        'descricao',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'forma_pagamento',
        'observacoes'
    ];
    
    protected $casts = [
        'data_cadastro' => 'datetime',
        'data_vencimento' => 'datetime',
        'data_pagamento' => 'datetime',
        'valor' => 'decimal'
    ];
    
    /**
     * Busca despesas por categoria
     */
    public function findByCategoria(int $idCategoria): array
    {
        return $this->findAllBy('id_categoria', $idCategoria);
    }
    
    /**
     * Busca despesas por forma de pagamento
     */
    public function findByFormaPagamento(string $formaPagamento): array
    {
        return $this->findAllBy('forma_pagamento', $formaPagamento);
    }
    
    /**
     * Busca despesas por data de vencimento
     */
    public function findByDataVencimento(string $data): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_vencimento) = ? ORDER BY data_vencimento";
        return $this->db->query($sql, [$data])->fetchAll();
    }
    
    /**
     * Busca despesas por período de vencimento
     */
    public function findByPeriodoVencimento(string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_vencimento) BETWEEN ? AND ? ORDER BY data_vencimento";
        return $this->db->query($sql, [$dataInicio, $dataFim])->fetchAll();
    }
    
    /**
     * Busca despesas por data de pagamento
     */
    public function findByDataPagamento(string $data): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_pagamento) = ? ORDER BY data_pagamento DESC";
        return $this->db->query($sql, [$data])->fetchAll();
    }
    
    /**
     * Busca despesas por período de pagamento
     */
    public function findByPeriodoPagamento(string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_pagamento) BETWEEN ? AND ? ORDER BY data_pagamento DESC";
        return $this->db->query($sql, [$dataInicio, $dataFim])->fetchAll();
    }
    
    /**
     * Busca despesas vencidas
     */
    public function findVencidas(): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE data_vencimento < CURDATE() 
            AND data_pagamento IS NULL
            ORDER BY data_vencimento ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca despesas a vencer
     */
    public function findAVencer(int $dias = 7): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND data_pagamento IS NULL
            ORDER BY data_vencimento ASC
        ";
        return $this->db->query($sql, [$dias])->fetchAll();
    }
    
    /**
     * Busca despesas pagas
     */
    public function findPagas(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE data_pagamento IS NOT NULL ORDER BY data_pagamento DESC";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca despesas pendentes
     */
    public function findPendentes(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE data_pagamento IS NULL ORDER BY data_vencimento ASC";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca despesa com dados completos
     */
    public function findCompleto(int $id)
    {
        $sql = "
            SELECT d.*, cf.nome as nome_categoria, cf.tipo as tipo_categoria
            FROM despesas d 
            LEFT JOIN categorias_financeiras cf ON d.id_categoria = cf.id_categoria
            WHERE d.id_despesa = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca despesas por faixa de valor
     */
    public function findByFaixaValor(float $valorMin, float $valorMax): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE valor BETWEEN ? AND ? ORDER BY valor DESC";
        return $this->db->query($sql, [$valorMin, $valorMax])->fetchAll();
    }
    
    /**
     * Calcula total de despesas por período
     */
    public function calcularTotalPorPeriodo(string $dataInicio, string $dataFim): float
    {
        $sql = "
            SELECT SUM(valor) as total FROM {$this->table} 
            WHERE DATE(data_vencimento) BETWEEN ? AND ?
        ";
        
        $result = $this->db->query($sql, [$dataInicio, $dataFim])->fetch();
        return (float) ($result['total'] ?? 0.0);
    }
    
    /**
     * Calcula total de despesas do mês
     */
    public function calcularTotalDoMes(): float
    {
        $sql = "
            SELECT SUM(valor) as total FROM {$this->table} 
            WHERE YEAR(data_vencimento) = YEAR(CURDATE()) 
            AND MONTH(data_vencimento) = MONTH(CURDATE())
        ";
        
        $result = $this->db->query($sql)->fetch();
        return (float) ($result['total'] ?? 0.0);
    }
    
    /**
     * Calcula total de despesas do ano
     */
    public function calcularTotalDoAno(): float
    {
        $sql = "
            SELECT SUM(valor) as total FROM {$this->table} 
            WHERE YEAR(data_vencimento) = YEAR(CURDATE())
        ";
        
        $result = $this->db->query($sql)->fetch();
        return (float) ($result['total'] ?? 0.0);
    }
    
    /**
     * Calcula total de despesas pagas por período
     */
    public function calcularTotalPagasPorPeriodo(string $dataInicio, string $dataFim): float
    {
        $sql = "
            SELECT SUM(valor) as total FROM {$this->table} 
            WHERE DATE(data_pagamento) BETWEEN ? AND ?
            AND data_pagamento IS NOT NULL
        ";
        
        $result = $this->db->query($sql, [$dataInicio, $dataFim])->fetch();
        return (float) ($result['total'] ?? 0.0);
    }
    
    /**
     * Calcula total de despesas pendentes
     */
    public function calcularTotalPendentes(): float
    {
        $sql = "
            SELECT SUM(valor) as total FROM {$this->table} 
            WHERE data_pagamento IS NULL
        ";
        
        $result = $this->db->query($sql)->fetch();
        return (float) ($result['total'] ?? 0.0);
    }
    
    /**
     * Marca despesa como paga
     */
    public function marcarComoPaga(int $id, string $formaPagamento = null, string $observacoes = ''): bool
    {
        $dados = [
            'data_pagamento' => date('Y-m-d H:i:s')
        ];
        
        if ($formaPagamento) {
            $dados['forma_pagamento'] = $formaPagamento;
        }
        
        if ($observacoes) {
            $dados['observacoes'] = $observacoes;
        }
        
        return $this->update($id, $dados);
    }
    
    /**
     * Busca despesas por categoria e período
     */
    public function findPorCategoriaPeriodo(int $idCategoria, string $dataInicio, string $dataFim): array
    {
        $sql = "
            SELECT d.*, cf.nome as nome_categoria
            FROM despesas d 
            LEFT JOIN categorias_financeiras cf ON d.id_categoria = cf.id_categoria
            WHERE d.id_categoria = ? 
            AND DATE(d.data_vencimento) BETWEEN ? AND ?
            ORDER BY d.data_vencimento
        ";
        
        return $this->db->query($sql, [$idCategoria, $dataInicio, $dataFim])->fetchAll();
    }
    
    /**
     * Estatísticas de despesas
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de despesas
        $stats['total'] = $this->count();
        
        // Despesas por categoria
        $sql = "
            SELECT cf.nome as categoria, COUNT(d.id_despesa) as total, SUM(d.valor) as valor_total
            FROM despesas d 
            LEFT JOIN categorias_financeiras cf ON d.id_categoria = cf.id_categoria
            GROUP BY cf.id_categoria
            ORDER BY valor_total DESC
        ";
        $stats['por_categoria'] = $this->db->query($sql)->fetchAll();
        
        // Despesas por forma de pagamento
        $sql = "
            SELECT forma_pagamento, COUNT(*) as total, SUM(valor) as valor_total
            FROM {$this->table} 
            WHERE forma_pagamento IS NOT NULL
            GROUP BY forma_pagamento
            ORDER BY valor_total DESC
        ";
        $stats['por_forma_pagamento'] = $this->db->query($sql)->fetchAll();
        
        // Despesas por mês
        $sql = "
            SELECT 
                strftime('%Y-%m', data_vencimento) as mes,
                COUNT(*) as total,
                SUM(valor) as valor_total
            FROM {$this->table} 
            WHERE data_vencimento >= date('now', '-12 months')
            GROUP BY strftime('%Y-%m', data_vencimento)
            ORDER BY mes DESC
        ";
        $stats['por_mes'] = $this->db->query($sql)->fetchAll();
        
        // Status das despesas
        $stats['total_pagas'] = count($this->findPagas());
        $stats['total_pendentes'] = count($this->findPendentes());
        $stats['total_vencidas'] = count($this->findVencidas());
        $stats['total_a_vencer'] = count($this->findAVencer(30));
        
        // Valores totais
        $stats['total_mes'] = $this->calcularTotalDoMes();
        $stats['total_ano'] = $this->calcularTotalDoAno();
        $stats['total_pendentes_valor'] = $this->calcularTotalPendentes();
        
        // Valor médio por despesa
        $sql = "SELECT AVG(valor) as valor_medio FROM {$this->table}";
        $stats['valor_medio'] = $this->db->query($sql)->fetch();
        
        return $stats;
    }
}
