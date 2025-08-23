<?php

namespace LJOS\Models;

/**
 * Modelo de Movimentação de Estoque
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class MovimentacaoEstoque extends BaseModel
{
    protected $table = 'movimentacoes_estoque';
    protected $primaryKey = 'id_movimentacao';
    
    protected $fillable = [
        'id_produto',
        'id_funcionario',
        'tipo',
        'quantidade',
        'preco_unitario',
        'valor_total',
        'motivo',
        'observacoes'
    ];
    
    protected $casts = [
        'data_movimentacao' => 'datetime',
        'quantidade' => 'integer',
        'preco_unitario' => 'decimal',
        'valor_total' => 'decimal'
    ];
    
    /**
     * Busca movimentações por produto
     */
    public function findByProduto(int $idProduto): array
    {
        return $this->findAllBy('id_produto', $idProduto);
    }
    
    /**
     * Busca movimentações por funcionário
     */
    public function findByFuncionario(int $idFuncionario): array
    {
        return $this->findAllBy('id_funcionario', $idFuncionario);
    }
    
    /**
     * Busca movimentações por tipo
     */
    public function findByTipo(string $tipo): array
    {
        return $this->findAllBy('tipo', $tipo);
    }
    
    /**
     * Busca movimentações por período
     */
    public function findByPeriodo(string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_movimentacao) BETWEEN ? AND ? ORDER BY data_movimentacao DESC";
        return $this->db->query($sql, [$dataInicio, $dataFim])->fetchAll();
    }
    
    /**
     * Busca movimentações do dia
     */
    public function findDoDia(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_movimentacao) = CURDATE() ORDER BY data_movimentacao DESC";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca movimentações da semana
     */
    public function findDaSemana(): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE YEARWEEK(data_movimentacao, 1) = YEARWEEK(CURDATE(), 1) 
            ORDER BY data_movimentacao DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca movimentações do mês
     */
    public function findDoMes(): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE YEAR(data_movimentacao) = YEAR(CURDATE()) 
            AND MONTH(data_movimentacao) = MONTH(CURDATE())
            ORDER BY data_movimentacao DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca movimentação com dados completos
     */
    public function findCompleto(int $id)
    {
        $sql = "
            SELECT me.*, 
                   p.nome as nome_produto, p.codigo as codigo_produto,
                   f.nome as nome_funcionario, f.cargo as cargo_funcionario
            FROM movimentacoes_estoque me 
            LEFT JOIN produtos p ON me.id_produto = p.id_produto
            LEFT JOIN funcionarios f ON me.id_funcionario = f.id_funcionario
            WHERE me.id_movimentacao = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Registra entrada de estoque
     */
    public function registrarEntrada(int $idProduto, int $quantidade, float $precoUnitario, int $idFuncionario, string $motivo, string $observacoes = ''): int
    {
        $valorTotal = $quantidade * $precoUnitario;
        
        return $this->create([
            'id_produto' => $idProduto,
            'id_funcionario' => $idFuncionario,
            'tipo' => 'ENTRADA',
            'quantidade' => $quantidade,
            'preco_unitario' => $precoUnitario,
            'valor_total' => $valorTotal,
            'motivo' => $motivo,
            'observacoes' => $observacoes
        ]);
    }
    
    /**
     * Registra saída de estoque
     */
    public function registrarSaida(int $idProduto, int $quantidade, float $precoUnitario, int $idFuncionario, string $motivo, string $observacoes = ''): int
    {
        $valorTotal = $quantidade * $precoUnitario;
        
        return $this->create([
            'id_produto' => $idProduto,
            'id_funcionario' => $idFuncionario,
            'tipo' => 'SAIDA',
            'quantidade' => $quantidade,
            'preco_unitario' => $precoUnitario,
            'valor_total' => $valorTotal,
            'motivo' => $motivo,
            'observacoes' => $observacoes
        ]);
    }
    
    /**
     * Registra transferência de estoque
     */
    public function registrarTransferencia(int $idProduto, int $quantidade, float $precoUnitario, int $idFuncionario, string $motivo, string $observacoes = ''): int
    {
        $valorTotal = $quantidade * $precoUnitario;
        
        return $this->create([
            'id_produto' => $idProduto,
            'id_funcionario' => $idFuncionario,
            'tipo' => 'TRANSFERENCIA',
            'quantidade' => $quantidade,
            'preco_unitario' => $precoUnitario,
            'valor_total' => $valorTotal,
            'motivo' => $motivo,
            'observacoes' => $observacoes
        ]);
    }
    
    /**
     * Registra ajuste de estoque
     */
    public function registrarAjuste(int $idProduto, int $quantidade, float $precoUnitario, int $idFuncionario, string $motivo, string $observacoes = ''): int
    {
        $valorTotal = $quantidade * $precoUnitario;
        
        return $this->create([
            'id_produto' => $idProduto,
            'id_funcionario' => $idFuncionario,
            'tipo' => 'AJUSTE',
            'quantidade' => $quantidade,
            'preco_unitario' => $precoUnitario,
            'valor_total' => $valorTotal,
            'motivo' => $motivo,
            'observacoes' => $observacoes
        ]);
    }
    
    /**
     * Calcula valor total do estoque
     */
    public function calcularValorTotalEstoque(): float
    {
        $sql = "
            SELECT SUM(
                (SELECT COALESCE(SUM(CASE WHEN tipo = 'ENTRADA' THEN quantidade ELSE -quantidade END), 0)
                 FROM movimentacoes_estoque me2 
                 WHERE me2.id_produto = me.id_produto) * me.preco_unitario
            ) as valor_total
            FROM movimentacoes_estoque me
            GROUP BY me.id_produto
        ";
        
        $result = $this->db->query($sql)->fetchAll();
        $valorTotal = 0.0;
        
        foreach ($result as $row) {
            $valorTotal += (float) ($row['valor_total'] ?? 0.0);
        }
        
        return $valorTotal;
    }
    
    /**
     * Busca produtos mais movimentados
     */
    public function findProdutosMaisMovimentados(int $limit = 10): array
    {
        $sql = "
            SELECT p.nome as nome_produto, p.codigo as codigo_produto,
                   COUNT(me.id_movimentacao) as total_movimentacoes,
                   SUM(CASE WHEN me.tipo = 'ENTRADA' THEN me.quantidade ELSE -me.quantidade END) as saldo_atual
            FROM movimentacoes_estoque me
            LEFT JOIN produtos p ON me.id_produto = p.id_produto
            GROUP BY me.id_produto
            ORDER BY total_movimentacoes DESC
            LIMIT ?
        ";
        
        return $this->db->query($sql, [$limit])->fetchAll();
    }
    
    /**
     * Estatísticas de movimentações
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de movimentações
        $stats['total'] = $this->count();
        
        // Movimentações por tipo
        $sql = "SELECT tipo, COUNT(*) as total FROM {$this->table} GROUP BY tipo ORDER BY total DESC";
        $stats['por_tipo'] = $this->db->query($sql)->fetchAll();
        
        // Movimentações por mês
        $sql = "
            SELECT 
                strftime('%Y-%m', data_movimentacao) as mes,
                COUNT(*) as total,
                SUM(valor_total) as valor_total
            FROM {$this->table} 
            WHERE data_movimentacao >= date('now', '-12 months')
            GROUP BY strftime('%Y-%m', data_movimentacao)
            ORDER BY mes DESC
        ";
        $stats['por_mes'] = $this->db->query($sql)->fetchAll();
        
        // Valor total movimentado
        $sql = "SELECT SUM(valor_total) as valor_total FROM {$this->table}";
        $stats['valor_total_movimentado'] = $this->db->query($sql)->fetch();
        
        // Produtos mais movimentados
        $stats['produtos_mais_movimentados'] = $this->findProdutosMaisMovimentados(5);
        
        return $stats;
    }
}
