<?php

namespace LJOS\Models;

/**
 * Modelo de Produto
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Produto extends BaseModel
{
    protected $table = 'produtos';
    protected $primaryKey = 'id_produto';
    
    protected $fillable = [
        'id_categoria',
        'codigo',
        'codigo_barras',
        'nome',
        'descricao',
        'marca',
        'modelo',
        'tamanho',
        'preco_custo',
        'preco_venda',
        'margem',
        'estoque_minimo',
        'estoque_maximo',
        'unidade_medida',
        'localizacao',
        'status'
    ];
    
    protected $casts = [
        'data_cadastro' => 'datetime',
        'preco_custo' => 'decimal',
        'preco_venda' => 'decimal',
        'margem' => 'decimal',
        'estoque_minimo' => 'integer',
        'estoque_maximo' => 'integer'
    ];
    
    /**
     * Busca produto por código
     */
    public function findByCodigo(string $codigo)
    {
        return $this->findBy('codigo', $codigo);
    }
    
    /**
     * Busca produto por código de barras
     */
    public function findByCodigoBarras(string $codigoBarras)
    {
        return $this->findBy('codigo_barras', $codigoBarras);
    }
    
    /**
     * Busca produtos por categoria
     */
    public function findByCategoria(int $idCategoria): array
    {
        return $this->findAllBy('id_categoria', $idCategoria);
    }
    
    /**
     * Busca produtos por marca
     */
    public function findByMarca(string $marca): array
    {
        return $this->findAllBy('marca', $marca);
    }
    
    /**
     * Busca produtos ativos
     */
    public function findAtivos(): array
    {
        return $this->findAllBy('status', 'ATIVO');
    }
    
    /**
     * Busca produtos com estoque baixo
     */
    public function findComEstoqueBaixo(): array
    {
        $sql = "
            SELECT p.*, 
                   (SELECT COALESCE(SUM(CASE WHEN tipo = 'ENTRADA' THEN quantidade ELSE -quantidade END), 0)
                    FROM movimentacoes_estoque me 
                    WHERE me.id_produto = p.id_produto) as estoque_atual
            FROM {$this->table} p 
            WHERE p.status = 'ATIVO'
            HAVING estoque_atual <= p.estoque_minimo
            ORDER BY estoque_atual ASC
        ";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca produtos com estoque alto
     */
    public function findComEstoqueAlto(): array
    {
        $sql = "
            SELECT p.*, 
                   (SELECT COALESCE(SUM(CASE WHEN tipo = 'ENTRADA' THEN quantidade ELSE -quantidade END), 0)
                    FROM movimentacoes_estoque me 
                    WHERE me.id_produto = p.id_produto) as estoque_atual
            FROM {$this->table} p 
            WHERE p.status = 'ATIVO'
            HAVING estoque_atual >= p.estoque_maximo
            ORDER BY estoque_atual DESC
        ";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca produto com dados da categoria
     */
    public function findWithCategoria(int $id)
    {
        $sql = "
            SELECT p.*, cp.nome as nome_categoria, cp.descricao as descricao_categoria
            FROM produtos p 
            LEFT JOIN categorias_produtos cp ON p.id_categoria = cp.id_categoria 
            WHERE p.id_produto = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca produto com estoque atual
     */
    public function findComEstoque(int $id)
    {
        $produto = $this->findWithCategoria($id);
        if (!$produto) {
            return null;
        }
        
        // Calcular estoque atual
        $sql = "
            SELECT COALESCE(SUM(CASE WHEN tipo = 'ENTRADA' THEN quantidade ELSE -quantidade END), 0) as estoque_atual
            FROM movimentacoes_estoque 
            WHERE id_produto = ?
        ";
        
        $result = $this->db->query($sql, [$id])->fetch();
        $produto['estoque_atual'] = (int) ($result['estoque_atual'] ?? 0);
        
        return $produto;
    }
    
    /**
     * Busca produtos por faixa de preço
     */
    public function findByFaixaPreco(float $precoMin, float $precoMax): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE preco_venda BETWEEN ? AND ? AND status = 'ATIVO' ORDER BY preco_venda";
        return $this->db->query($sql, [$precoMin, $precoMax])->fetchAll();
    }
    
    /**
     * Busca produtos por margem de lucro
     */
    public function findByMargem(float $margemMin, float $margemMax): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE margem BETWEEN ? AND ? AND status = 'ATIVO' ORDER BY margem DESC";
        return $this->db->query($sql, [$margemMin, $margemMax])->fetchAll();
    }
    
    /**
     * Calcula estoque atual do produto
     */
    public function calcularEstoqueAtual(int $id): int
    {
        $sql = "
            SELECT COALESCE(SUM(CASE WHEN tipo = 'ENTRADA' THEN quantidade ELSE -quantidade END), 0) as estoque_atual
            FROM movimentacoes_estoque 
            WHERE id_produto = ?
        ";
        
        $result = $this->db->query($sql, [$id])->fetch();
        return (int) ($result['estoque_atual'] ?? 0);
    }
    
    /**
     * Verifica se produto tem estoque suficiente
     */
    public function temEstoque(int $id, int $quantidade): bool
    {
        $estoqueAtual = $this->calcularEstoqueAtual($id);
        return $estoqueAtual >= $quantidade;
    }
    
    /**
     * Atualiza preços do produto
     */
    public function atualizarPrecos(int $id, float $novoPrecoCusto, float $novoPrecoVenda): bool
    {
        $margem = (($novoPrecoVenda - $novoPrecoCusto) / $novoPrecoCusto) * 100;
        
        return $this->update($id, [
            'preco_custo' => $novoPrecoCusto,
            'preco_venda' => $novoPrecoVenda,
            'margem' => $margem
        ]);
    }
    
    /**
     * Atualiza estoque mínimo/máximo
     */
    public function atualizarEstoque(int $id, int $estoqueMinimo, int $estoqueMaximo): bool
    {
        return $this->update($id, [
            'estoque_minimo' => $estoqueMinimo,
            'estoque_maximo' => $estoqueMaximo
        ]);
    }
    
    /**
     * Estatísticas de produtos
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de produtos
        $stats['total'] = $this->count();
        
        // Produtos por categoria
        $sql = "
            SELECT cp.nome as categoria, COUNT(p.id_produto) as total
            FROM produtos p 
            LEFT JOIN categorias_produtos cp ON p.id_categoria = cp.id_categoria
            WHERE p.status = 'ATIVO'
            GROUP BY cp.id_categoria
            ORDER BY total DESC
        ";
        $stats['por_categoria'] = $this->db->query($sql)->fetchAll();
        
        // Produtos por marca
        $sql = "SELECT marca, COUNT(*) as total FROM {$this->table} WHERE status = 'ATIVO' GROUP BY marca ORDER BY total DESC LIMIT 10";
        $stats['por_marca'] = $this->db->query($sql)->fetchAll();
        
        // Faixa de preços
        $sql = "SELECT MIN(preco_venda) as preco_min, MAX(preco_venda) as preco_max, AVG(preco_venda) as preco_medio FROM {$this->table} WHERE status = 'ATIVO'";
        $stats['faixa_precos'] = $this->db->query($sql)->fetch();
        
        // Produtos com estoque baixo
        $stats['estoque_baixo'] = count($this->findComEstoqueBaixo());
        
        // Produtos com estoque alto
        $stats['estoque_alto'] = count($this->findComEstoqueAlto());
        
        return $stats;
    }
}
