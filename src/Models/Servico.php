<?php

namespace LJOS\Models;

/**
 * Modelo de Serviço
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Servico extends BaseModel
{
    protected $table = 'servicos';
    protected $primaryKey = 'id_servico';
    
    protected $fillable = [
        'id_categoria',
        'nome',
        'descricao',
        'preco',
        'duracao_estimada',
        'tipo_veiculo',
        'status'
    ];
    
    protected $casts = [
        'data_cadastro' => 'datetime',
        'preco' => 'decimal',
        'duracao_estimada' => 'integer'
    ];
    
    /**
     * Busca serviço por categoria
     */
    public function findByCategoria(int $idCategoria): array
    {
        return $this->findAllBy('id_categoria', $idCategoria);
    }
    
    /**
     * Busca serviços por tipo de veículo
     */
    public function findByTipoVeiculo(string $tipoVeiculo): array
    {
        return $this->whereMultiple([
            ['tipo_veiculo', '=', $tipoVeiculo],
            ['status', '=', 'ATIVO']
        ]);
    }
    
    /**
     * Busca serviços ativos
     */
    public function findAtivos(): array
    {
        return $this->findAllBy('status', 'ATIVO');
    }
    
    /**
     * Busca serviços por faixa de preço
     */
    public function findByFaixaPreco(float $precoMin, float $precoMax): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE preco BETWEEN ? AND ? AND status = 'ATIVO' ORDER BY preco";
        return $this->db->query($sql, [$precoMin, $precoMax])->fetchAll();
    }
    
    /**
     * Busca serviços por duração estimada
     */
    public function findByDuracao(int $duracaoMin, int $duracaoMax): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE duracao_estimada BETWEEN ? AND ? AND status = 'ATIVO' ORDER BY duracao_estimada";
        return $this->db->query($sql, [$duracaoMin, $duracaoMax])->fetchAll();
    }
    
    /**
     * Busca serviço com dados da categoria
     */
    public function findWithCategoria(int $id)
    {
        $sql = "
            SELECT s.*, cs.nome as nome_categoria, cs.descricao as descricao_categoria
            FROM servicos s 
            LEFT JOIN categorias_servicos cs ON s.id_categoria = cs.id_categoria 
            WHERE s.id_servico = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca serviços populares (mais utilizados)
     */
    public function findPopulares(int $limit = 10): array
    {
        $sql = "
            SELECT s.*, COUNT(ios.id_servico) as total_uso
            FROM servicos s 
            LEFT JOIN itens_ordem_servico ios ON s.id_servico = ios.id_servico
            WHERE s.status = 'ATIVO'
            GROUP BY s.id_servico
            ORDER BY total_uso DESC
            LIMIT ?
        ";
        
        return $this->db->query($sql, [$limit])->fetchAll();
    }
    
    /**
     * Calcula preço com desconto
     */
    public function calcularPrecoComDesconto(float $preco, float $descontoPercentual): float
    {
        return $preco * (1 - ($descontoPercentual / 100));
    }
    
    /**
     * Calcula tempo total para múltiplos serviços
     */
    public function calcularTempoTotal(array $idsServicos): int
    {
        if (empty($idsServicos)) {
            return 0;
        }
        
        $placeholders = str_repeat('?,', count($idsServicos) - 1) . '?';
        $sql = "SELECT SUM(duracao_estimada) as tempo_total FROM {$this->table} WHERE id_servico IN ({$placeholders})";
        
        $result = $this->db->query($sql, $idsServicos)->fetch();
        return (int) ($result['tempo_total'] ?? 0);
    }
    
    /**
     * Calcula preço total para múltiplos serviços
     */
    public function calcularPrecoTotal(array $idsServicos): float
    {
        if (empty($idsServicos)) {
            return 0.0;
        }
        
        $placeholders = str_repeat('?,', count($idsServicos) - 1) . '?';
        $sql = "SELECT SUM(preco) as preco_total FROM {$this->table} WHERE id_servico IN ({$placeholders})";
        
        $result = $this->db->query($sql, $idsServicos)->fetch();
        return (float) ($result['preco_total'] ?? 0.0);
    }
    
    /**
     * Estatísticas de serviços
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de serviços
        $stats['total'] = $this->count();
        
        // Serviços por categoria
        $sql = "
            SELECT cs.nome as categoria, COUNT(s.id_servico) as total
            FROM servicos s 
            LEFT JOIN categorias_servicos cs ON s.id_categoria = cs.id_categoria
            WHERE s.status = 'ATIVO'
            GROUP BY cs.id_categoria
            ORDER BY total DESC
        ";
        $stats['por_categoria'] = $this->db->query($sql)->fetchAll();
        
        // Serviços por tipo de veículo
        $sql = "SELECT tipo_veiculo, COUNT(*) as total FROM {$this->table} WHERE status = 'ATIVO' GROUP BY tipo_veiculo ORDER BY total DESC";
        $stats['por_tipo_veiculo'] = $this->db->query($sql)->fetchAll();
        
        // Faixa de preços
        $sql = "SELECT MIN(preco) as preco_min, MAX(preco) as preco_max, AVG(preco) as preco_medio FROM {$this->table} WHERE status = 'ATIVO'";
        $stats['faixa_precos'] = $this->db->query($sql)->fetch();
        
        return $stats;
    }
}
