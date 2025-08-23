<?php

namespace LJOS\Models;

/**
 * Modelo de Cliente
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Cliente extends BaseModel
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    
    protected $fillable = [
        'nome',
        'tipo_pessoa',
        'cpf_cnpj',
        'rg_ie',
        'telefone',
        'email',
        'endereco',
        'cep',
        'cidade',
        'estado',
        'data_nascimento',
        'observacoes',
        'programa_fidelidade',
        'pontos_fidelidade'
    ];
    
    protected $casts = [
        'data_nascimento' => 'date',
        'data_cadastro' => 'datetime',
        'programa_fidelidade' => 'boolean'
    ];
    
    /**
     * Busca cliente por CPF/CNPJ
     */
    public function findByCpfCnpj(string $cpfCnpj)
    {
        return $this->findBy('cpf_cnpj', $cpfCnpj);
    }
    
    /**
     * Busca cliente por telefone
     */
    public function findByTelefone(string $telefone)
    {
        return $this->findBy('telefone', $telefone);
    }
    
    /**
     * Busca cliente por email
     */
    public function findByEmail(string $email)
    {
        return $this->findBy('email', $email);
    }
    
    /**
     * Busca clientes por tipo de pessoa
     */
    public function findByTipoPessoa(string $tipo): array
    {
        return $this->findAllBy('tipo_pessoa', $tipo);
    }
    
    /**
     * Busca clientes ativos
     */
    public function findAtivos(): array
    {
        return $this->findAllBy('status', 'ATIVO');
    }
    
    /**
     * Busca clientes por cidade
     */
    public function findByCidade(string $cidade): array
    {
        return $this->findAllBy('cidade', $cidade);
    }
    
    /**
     * Busca clientes por estado
     */
    public function findByEstado(string $estado): array
    {
        return $this->findAllBy('estado', $estado);
    }
    
    /**
     * Busca clientes com fidelidade ativa
     */
    public function findComFidelidade(): array
    {
        return $this->where('programa_fidelidade', '=', 1);
    }
    
    /**
     * Busca clientes por faixa de pontos
     */
    public function findByFaixaPontos(int $min, int $max): array
    {
        $sql = "
            SELECT * FROM clientes 
            WHERE pontos_fidelidade BETWEEN ? AND ?
            ORDER BY pontos_fidelidade DESC
        ";
        
        return $this->rawQuery($sql, [$min, $max]);
    }
    
    /**
     * Busca cliente com veículos
     */
    public function findWithVeiculos(int $id)
    {
        $sql = "
            SELECT c.*, v.* 
            FROM clientes c 
            LEFT JOIN veiculos v ON c.id_cliente = v.id_cliente 
            WHERE c.id_cliente = ?
            ORDER BY v.data_cadastro DESC
        ";
        
        return $this->rawQuery($sql, [$id]);
    }
    
    /**
     * Busca cliente com histórico de serviços
     */
    public function findWithHistorico(int $id)
    {
        $sql = "
            SELECT c.*, os.*, s.nome as servico_nome, s.preco as servico_preco
            FROM clientes c 
            LEFT JOIN ordens_servico os ON c.id_cliente = os.id_cliente 
            LEFT JOIN itens_ordem_servico ios ON os.id_os = ios.id_os
            LEFT JOIN servicos s ON ios.id_servico = s.id_servico
            WHERE c.id_cliente = ?
            ORDER BY os.data_abertura DESC
        ";
        
        return $this->rawQuery($sql, [$id]);
    }
    
    /**
     * Busca cliente com agendamentos
     */
    public function findWithAgendamentos(int $id)
    {
        $sql = "
            SELECT c.*, a.*, s.nome as servico_nome, v.placa, v.marca, v.modelo
            FROM clientes c 
            LEFT JOIN agendamentos a ON c.id_cliente = a.id_cliente 
            LEFT JOIN servicos s ON a.id_servico = s.id_servico
            LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
            WHERE c.id_cliente = ?
            ORDER BY a.data_agendamento DESC
        ";
        
        return $this->rawQuery($sql, [$id]);
    }
    
    /**
     * Busca clientes por período de cadastro
     */
    public function findByPeriodoCadastro(string $dataInicio, string $dataFim): array
    {
        $sql = "
            SELECT * FROM clientes 
            WHERE DATE(data_cadastro) BETWEEN ? AND ?
            ORDER BY data_cadastro DESC
        ";
        
        return $this->rawQuery($sql, [$dataInicio, $dataFim]);
    }
    
    /**
     * Busca clientes por valor gasto
     */
    public function findByValorGasto(float $min, float $max): array
    {
        $sql = "
            SELECT c.*, COALESCE(SUM(os.valor_final), 0) as total_gasto
            FROM clientes c 
            LEFT JOIN ordens_servico os ON c.id_cliente = os.id_cliente 
            GROUP BY c.id_cliente
            HAVING total_gasto BETWEEN ? AND ?
            ORDER BY total_gasto DESC
        ";
        
        return $this->rawQuery($sql, [$min, $max]);
    }
    
    /**
     * Busca clientes por frequência de visitas
     */
    public function findByFrequenciaVisitas(int $min, int $max): array
    {
        $sql = "
            SELECT c.*, COUNT(os.id_os) as total_visitas
            FROM clientes c 
            LEFT JOIN ordens_servico os ON c.id_cliente = os.id_cliente 
            GROUP BY c.id_cliente
            HAVING total_visitas BETWEEN ? AND ?
            ORDER BY total_visitas DESC
        ";
        
        return $this->rawQuery($sql, [$min, $max]);
    }
    
    /**
     * Busca clientes por último serviço
     */
    public function findPorUltimoServico(int $dias): array
    {
        $sql = "
            SELECT c.*, MAX(os.data_abertura) as ultimo_servico
            FROM clientes c 
            LEFT JOIN ordens_servico os ON c.id_cliente = os.id_cliente 
            GROUP BY c.id_cliente
            HAVING ultimo_servico < datetime('now', '-{$dias} days')
            ORDER BY ultimo_servico
        ";
        
        return $this->rawQuery($sql);
    }
    
    /**
     * Busca clientes VIP (alto valor gasto)
     */
    public function findVIP(float $valorMinimo = 1000.00): array
    {
        $sql = "
            SELECT c.*, COALESCE(SUM(os.valor_final), 0) as total_gasto
            FROM clientes c 
            LEFT JOIN ordens_servico os ON c.id_cliente = os.id_cliente 
            GROUP BY c.id_cliente
            HAVING total_gasto >= ?
            ORDER BY total_gasto DESC
        ";
        
        return $this->rawQuery($sql, [$valorMinimo]);
    }
    
    /**
     * Busca clientes por segmento
     */
    public function findPorSegmento(string $segmento): array
    {
        switch ($segmento) {
            case 'vip':
                return $this->findVIP();
            case 'fidelidade':
                return $this->findComFidelidade();
            case 'recorrentes':
                return $this->findByFrequenciaVisitas(5, 999);
            case 'novos':
                $dataLimite = date('Y-m-d', strtotime('-30 days'));
                return $this->findByPeriodoCadastro($dataLimite, date('Y-m-d'));
            default:
                return $this->all();
        }
    }
    
    /**
     * Atualiza pontos de fidelidade
     */
    public function updatePontosFidelidade(int $id, int $pontos): bool
    {
        return $this->update($id, ['pontos_fidelidade' => $pontos]);
    }
    
    /**
     * Adiciona pontos de fidelidade
     */
    public function adicionarPontos(int $id, int $pontos): bool
    {
        $cliente = $this->find($id);
        if (!$cliente) {
            return false;
        }
        
        $novosPontos = $cliente['pontos_fidelidade'] + $pontos;
        return $this->update($id, ['pontos_fidelidade' => $novosPontos]);
    }
    
    /**
     * Remove pontos de fidelidade
     */
    public function removerPontos(int $id, int $pontos): bool
    {
        $cliente = $this->find($id);
        if (!$cliente) {
            return false;
        }
        
        $novosPontos = max(0, $cliente['pontos_fidelidade'] - $pontos);
        return $this->update($id, ['pontos_fidelidade' => $novosPontos]);
    }
    
    /**
     * Ativa programa de fidelidade
     */
    public function ativarFidelidade(int $id): bool
    {
        return $this->update($id, ['programa_fidelidade' => 1]);
    }
    
    /**
     * Desativa programa de fidelidade
     */
    public function desativarFidelidade(int $id): bool
    {
        return $this->update($id, ['programa_fidelidade' => 0]);
    }
    
    /**
     * Busca estatísticas de clientes
     */
    public function getEstatisticas(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'ATIVO' THEN 1 END) as ativos,
                COUNT(CASE WHEN status = 'INATIVO' THEN 1 END) as inativos,
                COUNT(CASE WHEN tipo_pessoa = 'PF' THEN 1 END) as pessoa_fisica,
                COUNT(CASE WHEN tipo_pessoa = 'PJ' THEN 1 END) as pessoa_juridica,
                COUNT(CASE WHEN programa_fidelidade = 1 THEN 1 END) as com_fidelidade,
                COUNT(CASE WHEN programa_fidelidade = 0 THEN 1 END) as sem_fidelidade,
                AVG(pontos_fidelidade) as media_pontos,
                SUM(pontos_fidelidade) as total_pontos
            FROM clientes
        ";
        
        return $this->rawQueryOne($sql);
    }
    
    /**
     * Busca clientes por busca textual
     */
    public function search(string $termo): array
    {
        $sql = "
            SELECT * FROM clientes 
            WHERE nome LIKE ? 
            OR cpf_cnpj LIKE ? 
            OR telefone LIKE ? 
            OR email LIKE ?
            ORDER BY nome
        ";
        
        $termo = "%{$termo}%";
        return $this->rawQuery($sql, [$termo, $termo, $termo, $termo]);
    }
}
