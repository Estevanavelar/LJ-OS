<?php

namespace LJOS\Models;

/**
 * Modelo de Ordem de Serviço
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class OrdemServico extends BaseModel
{
    protected $table = 'ordens_servico';
    protected $primaryKey = 'id_os';
    
    protected $fillable = [
        'id_cliente',
        'id_veiculo',
        'id_funcionario',
        'numero_os',
        'data_abertura',
        'data_conclusao',
        'status',
        'valor_total',
        'desconto',
        'valor_final',
        'observacoes'
    ];
    
    protected $casts = [
        'data_abertura' => 'datetime',
        'data_conclusao' => 'datetime',
        'valor_total' => 'decimal',
        'desconto' => 'decimal',
        'valor_final' => 'decimal'
    ];
    
    /**
     * Busca OS por número
     */
    public function findByNumero(string $numero)
    {
        return $this->findBy('numero_os', $numero);
    }
    
    /**
     * Busca OS por cliente
     */
    public function findByCliente(int $idCliente): array
    {
        return $this->findAllBy('id_cliente', $idCliente);
    }
    
    /**
     * Busca OS por veículo
     */
    public function findByVeiculo(int $idVeiculo): array
    {
        return $this->findAllBy('id_veiculo', $idVeiculo);
    }
    
    /**
     * Busca OS por funcionário
     */
    public function findByFuncionario(int $idFuncionario): array
    {
        return $this->findAllBy('id_funcionario', $idFuncionario);
    }
    
    /**
     * Busca OS por status
     */
    public function findByStatus(string $status): array
    {
        return $this->findAllBy('status', $status);
    }
    
    /**
     * Busca OS por data de abertura
     */
    public function findByDataAbertura(string $data): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_abertura) = ? ORDER BY data_abertura DESC";
        return $this->db->query($sql, [$data])->fetchAll();
    }
    
    /**
     * Busca OS por período
     */
    public function findByPeriodo(string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_abertura) BETWEEN ? AND ? ORDER BY data_abertura DESC";
        return $this->db->query($sql, [$dataInicio, $dataFim])->fetchAll();
    }
    
    /**
     * Busca OS do dia
     */
    public function findDoDia(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_abertura) = CURDATE() ORDER BY data_abertura DESC";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca OS da semana
     */
    public function findDaSemana(): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE YEARWEEK(data_abertura, 1) = YEARWEEK(CURDATE(), 1) 
            ORDER BY data_abertura DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca OS do mês
     */
    public function findDoMes(): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE YEAR(data_abertura) = YEAR(CURDATE()) 
            AND MONTH(data_abertura) = MONTH(CURDATE())
            ORDER BY data_abertura DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca OS com dados completos
     */
    public function findCompleto(int $id)
    {
        $sql = "
            SELECT os.*, 
                   c.nome as nome_cliente, c.telefone as telefone_cliente, c.email as email_cliente,
                   v.placa, v.marca, v.modelo, v.cor,
                   f.nome as nome_funcionario, f.cargo as cargo_funcionario
            FROM ordens_servico os 
            LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
            LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo
            LEFT JOIN funcionarios f ON os.id_funcionario = f.id_funcionario
            WHERE os.id_os = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca OS com itens
     */
    public function findComItens(int $id)
    {
        $os = $this->findCompleto($id);
        if (!$os) {
            return null;
        }
        
        // Buscar itens da OS
        $sql = "
            SELECT ios.*, s.nome as nome_servico, s.descricao as descricao_servico
            FROM itens_ordem_servico ios
            LEFT JOIN servicos s ON ios.id_servico = s.id_servico
            WHERE ios.id_os = ?
            ORDER BY ios.id_item
        ";
        
        $os['itens'] = $this->db->query($sql, [$id])->fetchAll();
        
        return $os;
    }
    
    /**
     * Gera número único para OS
     */
    public function gerarNumeroOS(): string
    {
        $ano = date('Y');
        $mes = date('m');
        
        // Buscar última OS do mês
        $sql = "
            SELECT numero_os FROM {$this->table} 
            WHERE numero_os LIKE ? 
            ORDER BY numero_os DESC 
            LIMIT 1
        ";
        
        $ultimaOS = $this->db->query($sql, ["OS{$ano}{$mes}%"])->fetch();
        
        if ($ultimaOS) {
            $numero = (int) substr($ultimaOS['numero_os'], -4);
            $numero++;
        } else {
            $numero = 1;
        }
        
        return sprintf("OS%s%s%04d", $ano, $mes, $numero);
    }
    
    /**
     * Abre nova OS
     */
    public function abrirOS(array $dados): int
    {
        $dados['numero_os'] = $this->gerarNumeroOS();
        $dados['data_abertura'] = date('Y-m-d H:i:s');
        $dados['status'] = 'ABERTA';
        
        return $this->create($dados);
    }
    
    /**
     * Inicia OS
     */
    public function iniciarOS(int $id, int $idFuncionario): bool
    {
        return $this->update($id, [
            'id_funcionario' => $idFuncionario,
            'status' => 'EM_ANDAMENTO'
        ]);
    }
    
    /**
     * Conclui OS
     */
    public function concluirOS(int $id): bool
    {
        return $this->update($id, [
            'status' => 'CONCLUIDA',
            'data_conclusao' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Cancela OS
     */
    public function cancelarOS(int $id, string $motivo = ''): bool
    {
        return $this->update($id, [
            'status' => 'CANCELADA',
            'observacoes' => $motivo ? "CANCELADA: {$motivo}" : 'CANCELADA'
        ]);
    }
    
    /**
     * Calcula valor total da OS
     */
    public function calcularValorTotal(int $id): float
    {
        $sql = "
            SELECT SUM(valor_total) as total FROM itens_ordem_servico 
            WHERE id_os = ?
        ";
        
        $result = $this->db->query($sql, [$id])->fetch();
        return (float) ($result['total'] ?? 0.0);
    }
    
    /**
     * Aplica desconto na OS
     */
    public function aplicarDesconto(int $id, float $desconto): bool
    {
        $valorTotal = $this->calcularValorTotal($id);
        $valorFinal = $valorTotal - $desconto;
        
        return $this->update($id, [
            'desconto' => $desconto,
            'valor_final' => $valorFinal
        ]);
    }
    
    /**
     * Estatísticas de OS
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de OS
        $stats['total'] = $this->count();
        
        // OS por status
        $sql = "SELECT status, COUNT(*) as total FROM {$this->table} GROUP BY status ORDER BY total DESC";
        $stats['por_status'] = $this->db->query($sql)->fetchAll();
        
        // OS por mês
        $sql = "
            SELECT 
                strftime('%Y-%m', data_abertura) as mes,
                COUNT(*) as total,
                SUM(valor_final) as valor_total
            FROM {$this->table} 
            WHERE data_abertura >= date('now', '-12 months')
            GROUP BY strftime('%Y-%m', data_abertura)
            ORDER BY mes DESC
        ";
        $stats['por_mes'] = $this->db->query($sql)->fetchAll();
        
        // Valor médio por OS
        $sql = "SELECT AVG(valor_final) as valor_medio FROM {$this->table} WHERE status = 'CONCLUIDA'";
        $stats['valor_medio'] = $this->db->query($sql)->fetch();
        
        // Tempo médio de conclusão
        $sql = "
            SELECT AVG(strftime('%s', data_conclusao) - strftime('%s', data_abertura)) / 3600 as tempo_medio_horas
            FROM {$this->table} 
            WHERE status = 'CONCLUIDA' AND data_conclusao IS NOT NULL
        ";
        $stats['tempo_medio_conclusao'] = $this->db->query($sql)->fetch();
        
        return $stats;
    }
}
