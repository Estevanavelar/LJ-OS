<?php

namespace LJOS\Models;

/**
 * Modelo de Receita
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Receita extends BaseModel
{
    protected $table = 'receitas';
    protected $primaryKey = 'id_receita';
    
    protected $fillable = [
        'id_categoria',
        'id_os',
        'descricao',
        'valor',
        'data_recebimento',
        'forma_pagamento',
        'observacoes'
    ];
    
    protected $casts = [
        'data_cadastro' => 'datetime',
        'data_recebimento' => 'datetime',
        'valor' => 'decimal'
    ];
    
    /**
     * Busca receitas por categoria
     */
    public function findByCategoria(int $idCategoria): array
    {
        return $this->findAllBy('id_categoria', $idCategoria);
    }
    
    /**
     * Busca receitas por OS
     */
    public function findByOS(int $idOS): array
    {
        return $this->findAllBy('id_os', $idOS);
    }
    
    /**
     * Busca receitas por forma de pagamento
     */
    public function findByFormaPagamento(string $formaPagamento): array
    {
        return $this->findAllBy('forma_pagamento', $formaPagamento);
    }
    
    /**
     * Busca receitas por data
     */
    public function findByData(string $data): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_recebimento) = ? ORDER BY data_recebimento DESC";
        return $this->db->query($sql, [$data])->fetchAll();
    }
    
    /**
     * Busca receitas por período
     */
    public function findByPeriodo(string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_recebimento) BETWEEN ? AND ? ORDER BY data_recebimento DESC";
        return $this->db->query($sql, [$dataInicio, $dataFim])->fetchAll();
    }
    
    /**
     * Busca receitas do dia
     */
    public function findDoDia(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_recebimento) = CURDATE() ORDER BY data_recebimento DESC";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca receitas da semana
     */
    public function findDaSemana(): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE YEARWEEK(data_recebimento, 1) = YEARWEEK(CURDATE(), 1) 
            ORDER BY data_recebimento DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca receitas do mês
     */
    public function findDoMes(): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE YEAR(data_recebimento) = YEAR(CURDATE()) 
            AND MONTH(data_recebimento) = MONTH(CURDATE())
            ORDER BY data_recebimento DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca receita com dados completos
     */
    public function findCompleto(int $id)
    {
        $sql = "
            SELECT r.*, 
                   cf.nome as nome_categoria, cf.tipo as tipo_categoria,
                   os.numero_os, os.valor_final as valor_os
            FROM receitas r 
            LEFT JOIN categorias_financeiras cf ON r.id_categoria = cf.id_categoria
            LEFT JOIN ordens_servico os ON r.id_os = os.id_os
            WHERE r.id_receita = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca receitas por faixa de valor
     */
    public function findByFaixaValor(float $valorMin, float $valorMax): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE valor BETWEEN ? AND ? ORDER BY valor DESC";
        return $this->db->query($sql, [$valorMin, $valorMax])->fetchAll();
    }
    
    /**
     * Calcula total de receitas por período
     */
    public function calcularTotalPorPeriodo(string $dataInicio, string $dataFim): float
    {
        $sql = "
            SELECT SUM(valor) as total FROM {$this->table} 
            WHERE DATE(data_recebimento) BETWEEN ? AND ?
        ";
        
        $result = $this->db->query($sql, [$dataInicio, $dataFim])->fetch();
        return (float) ($result['total'] ?? 0.0);
    }
    
    /**
     * Calcula total de receitas do mês
     */
    public function calcularTotalDoMes(): float
    {
        $sql = "
            SELECT SUM(valor) as total FROM {$this->table} 
            WHERE YEAR(data_recebimento) = YEAR(CURDATE()) 
            AND MONTH(data_recebimento) = MONTH(CURDATE())
        ";
        
        $result = $this->db->query($sql)->fetch();
        return (float) ($result['total'] ?? 0.0);
    }
    
    /**
     * Calcula total de receitas do ano
     */
    public function calcularTotalDoAno(): float
    {
        $sql = "
            SELECT SUM(valor) as total FROM {$this->table} 
            WHERE YEAR(data_recebimento) = YEAR(CURDATE())
        ";
        
        $result = $this->db->query($sql)->fetch();
        return (float) ($result['total'] ?? 0.0);
    }
    
    /**
     * Busca receitas por categoria e período
     */
    public function findPorCategoriaPeriodo(int $idCategoria, string $dataInicio, string $dataFim): array
    {
        $sql = "
            SELECT r.*, cf.nome as nome_categoria
            FROM receitas r 
            LEFT JOIN categorias_financeiras cf ON r.id_categoria = cf.id_categoria
            WHERE r.id_categoria = ? 
            AND DATE(r.data_recebimento) BETWEEN ? AND ?
            ORDER BY r.data_recebimento DESC
        ";
        
        return $this->db->query($sql, [$idCategoria, $dataInicio, $dataFim])->fetchAll();
    }
    
    /**
     * Busca receitas por forma de pagamento e período
     */
    public function findPorFormaPagamentoPeriodo(string $formaPagamento, string $dataInicio, string $dataFim): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE forma_pagamento = ? 
            AND DATE(data_recebimento) BETWEEN ? AND ?
            ORDER BY data_recebimento DESC
        ";
        
        return $this->db->query($sql, [$formaPagamento, $dataInicio, $dataFim])->fetchAll();
    }
    
    /**
     * Estatísticas de receitas
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de receitas
        $stats['total'] = $this->count();
        
        // Receitas por categoria
        $sql = "
            SELECT cf.nome as categoria, COUNT(r.id_receita) as total, SUM(r.valor) as valor_total
            FROM receitas r 
            LEFT JOIN categorias_financeiras cf ON r.id_categoria = cf.id_categoria
            GROUP BY cf.id_categoria
            ORDER BY valor_total DESC
        ";
        $stats['por_categoria'] = $this->db->query($sql)->fetchAll();
        
        // Receitas por forma de pagamento
        $sql = "
            SELECT forma_pagamento, COUNT(*) as total, SUM(valor) as valor_total
            FROM {$this->table} 
            GROUP BY forma_pagamento
            ORDER BY valor_total DESC
        ";
        $stats['por_forma_pagamento'] = $this->db->query($sql)->fetchAll();
        
        // Receitas por mês
        $sql = "
            SELECT 
                strftime('%Y-%m', data_recebimento) as mes,
                COUNT(*) as total,
                SUM(valor) as valor_total
            FROM {$this->table} 
            WHERE data_recebimento >= date('now', '-12 months')
            GROUP BY strftime('%Y-%m', data_recebimento)
            ORDER BY mes DESC
        ";
        $stats['por_mes'] = $this->db->query($sql)->fetchAll();
        
        // Receitas por dia da semana
        $sql = "
            SELECT 
                CASE 
                    WHEN strftime('%w', data_recebimento) = '0' THEN 'Domingo'
                    WHEN strftime('%w', data_recebimento) = '1' THEN 'Segunda'
                    WHEN strftime('%w', data_recebimento) = '2' THEN 'Terça'
                    WHEN strftime('%w', data_recebimento) = '3' THEN 'Quarta'
                    WHEN strftime('%w', data_recebimento) = '4' THEN 'Quinta'
                    WHEN strftime('%w', data_recebimento) = '5' THEN 'Sexta'
                    WHEN strftime('%w', data_recebimento) = '6' THEN 'Sábado'
                END as dia_semana,
                COUNT(*) as total,
                SUM(valor) as valor_total
            FROM {$this->table} 
            WHERE data_recebimento >= date('now', '-30 days')
            GROUP BY strftime('%w', data_recebimento)
            ORDER BY strftime('%w', data_recebimento)
        ";
        $stats['por_dia_semana'] = $this->db->query($sql)->fetchAll();
        
        // Valores totais
        $stats['total_mes'] = $this->calcularTotalDoMes();
        $stats['total_ano'] = $this->calcularTotalDoAno();
        
        // Valor médio por receita
        $sql = "SELECT AVG(valor) as valor_medio FROM {$this->table}";
        $stats['valor_medio'] = $this->db->query($sql)->fetch();
        
        return $stats;
    }
}
