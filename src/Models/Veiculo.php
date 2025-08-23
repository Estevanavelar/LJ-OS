<?php

namespace LJOS\Models;

/**
 * Modelo de Veículo
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Veiculo extends BaseModel
{
    protected $table = 'veiculos';
    protected $primaryKey = 'id_veiculo';
    
    protected $fillable = [
        'id_cliente',
        'placa',
        'marca',
        'modelo',
        'ano',
        'ano_modelo',
        'cor',
        'combustivel',
        'km_atual',
        'observacoes',
        'status'
    ];
    
    protected $casts = [
        'data_cadastro' => 'datetime',
        'km_atual' => 'integer',
        'ano' => 'integer',
        'ano_modelo' => 'integer'
    ];
    
    /**
     * Busca veículo por placa
     */
    public function findByPlaca(string $placa)
    {
        return $this->findBy('placa', $placa);
    }
    
    /**
     * Busca veículos por cliente
     */
    public function findByCliente(int $idCliente): array
    {
        return $this->findAllBy('id_cliente', $idCliente);
    }
    
    /**
     * Busca veículos por marca
     */
    public function findByMarca(string $marca): array
    {
        return $this->findAllBy('marca', $marca);
    }
    
    /**
     * Busca veículos por modelo
     */
    public function findByModelo(string $modelo): array
    {
        return $this->findAllBy('modelo', $modelo);
    }
    
    /**
     * Busca veículos por ano
     */
    public function findByAno(int $ano): array
    {
        return $this->findAllBy('ano', $ano);
    }
    
    /**
     * Busca veículos por combustível
     */
    public function findByCombustivel(string $combustivel): array
    {
        return $this->findAllBy('combustivel', $combustivel);
    }
    
    /**
     * Busca veículos ativos
     */
    public function findAtivos(): array
    {
        return $this->findAllBy('status', 'ATIVO');
    }
    
    /**
     * Busca veículo com dados do cliente
     */
    public function findWithCliente(int $id)
    {
        $sql = "
            SELECT v.*, c.nome as nome_cliente, c.telefone as telefone_cliente, c.email as email_cliente
            FROM veiculos v 
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente 
            WHERE v.id_veiculo = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca veículos com histórico de serviços
     */
    public function findWithHistorico(int $id)
    {
        $sql = "
            SELECT v.*, 
                   COUNT(os.id_os) as total_ordens,
                   SUM(os.valor_final) as valor_total_servicos,
                   MAX(os.data_conclusao) as ultimo_servico
            FROM veiculos v 
            LEFT JOIN ordens_servico os ON v.id_veiculo = os.id_veiculo
            WHERE v.id_veiculo = ?
            GROUP BY v.id_veiculo
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca veículos por faixa de KM
     */
    public function findByFaixaKM(int $kmMin, int $kmMax): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE km_atual BETWEEN ? AND ? AND status = 'ATIVO'";
        return $this->db->query($sql, [$kmMin, $kmMax])->fetchAll();
    }
    
    /**
     * Atualiza KM do veículo
     */
    public function updateKM(int $id, int $kmAtual): bool
    {
        return $this->update($id, ['km_atual' => $kmAtual]);
    }
    
    /**
     * Busca veículos com documentos vencendo
     */
    public function findComDocumentosVencendo(int $dias = 30): array
    {
        $sql = "
            SELECT v.*, c.nome as nome_cliente, c.telefone as telefone_cliente
            FROM veiculos v 
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente 
            WHERE v.status = 'ATIVO'
            ORDER BY c.nome, v.marca, v.modelo
        ";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Estatísticas de veículos
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de veículos
        $stats['total'] = $this->count();
        
        // Veículos por marca
        $sql = "SELECT marca, COUNT(*) as total FROM {$this->table} WHERE status = 'ATIVO' GROUP BY marca ORDER BY total DESC LIMIT 10";
        $stats['por_marca'] = $this->db->query($sql)->fetchAll();
        
        // Veículos por combustível
        $sql = "SELECT combustivel, COUNT(*) as total FROM {$this->table} WHERE status = 'ATIVO' GROUP BY combustivel ORDER BY total DESC";
        $stats['por_combustivel'] = $this->db->query($sql)->fetchAll();
        
        // Veículos por ano
        $sql = "SELECT ano, COUNT(*) as total FROM {$this->table} WHERE status = 'ATIVO' GROUP BY ano ORDER BY ano DESC LIMIT 10";
        $stats['por_ano'] = $this->db->query($sql)->fetchAll();
        
        return $stats;
    }
}
