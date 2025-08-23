<?php

namespace LJOS\Models;

/**
 * Modelo de Funcionário
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Funcionario extends BaseModel
{
    protected $table = 'funcionarios';
    protected $primaryKey = 'id_funcionario';
    
    protected $fillable = [
        'id_usuario',
        'nome',
        'cpf',
        'rg',
        'data_nascimento',
        'data_admissao',
        'cargo',
        'departamento',
        'salario',
        'telefone',
        'email',
        'endereco',
        'foto',
        'status'
    ];
    
    protected $casts = [
        'data_admissao' => 'datetime',
        'data_nascimento' => 'date',
        'salario' => 'decimal'
    ];
    
    /**
     * Busca funcionário por CPF
     */
    public function findByCPF(string $cpf)
    {
        return $this->findBy('cpf', $cpf);
    }
    
    /**
     * Busca funcionário por usuário
     */
    public function findByUsuario(int $idUsuario)
    {
        return $this->findBy('id_usuario', $idUsuario);
    }
    
    /**
     * Busca funcionários por cargo
     */
    public function findByCargo(string $cargo): array
    {
        return $this->findAllBy('cargo', $cargo);
    }
    
    /**
     * Busca funcionários por departamento
     */
    public function findByDepartamento(string $departamento): array
    {
        return $this->findAllBy('departamento', $departamento);
    }
    
    /**
     * Busca funcionários ativos
     */
    public function findAtivos(): array
    {
        return $this->findAllBy('status', 'ATIVO');
    }
    
    /**
     * Busca funcionário com dados do usuário
     */
    public function findWithUsuario(int $id)
    {
        $sql = "
            SELECT f.*, u.nome as nome_usuario, u.email as email_usuario, u.nivel_acesso, u.status as status_usuario
            FROM funcionarios f 
            LEFT JOIN usuarios u ON f.id_usuario = u.id_usuario 
            WHERE f.id_funcionario = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca funcionários com estatísticas de trabalho
     */
    public function findComEstatisticas(int $id)
    {
        $sql = "
            SELECT f.*, 
                   COUNT(os.id_os) as total_ordens,
                   SUM(os.valor_final) as valor_total_servicos,
                   AVG(os.valor_final) as valor_medio_servicos,
                   MAX(os.data_conclusao) as ultimo_servico
            FROM funcionarios f 
            LEFT JOIN ordens_servico os ON f.id_funcionario = os.id_funcionario
            WHERE f.id_funcionario = ?
            GROUP BY f.id_funcionario
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca funcionários por faixa salarial
     */
    public function findByFaixaSalarial(float $salarioMin, float $salarioMax): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE salario BETWEEN ? AND ? AND status = 'ATIVO' ORDER BY salario";
        return $this->db->query($sql, [$salarioMin, $salarioMax])->fetchAll();
    }
    
    /**
     * Busca funcionários por tempo de empresa
     */
    public function findByTempoEmpresa(int $anosMin, int $anosMax): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE status = 'ATIVO'
            AND (strftime('%Y', 'now') - strftime('%Y', data_admissao)) BETWEEN ? AND ?
            ORDER BY data_admissao
        ";
        return $this->db->query($sql, [$anosMin, $anosMax])->fetchAll();
    }
    
    /**
     * Busca funcionários disponíveis para serviço
     */
    public function findDisponiveis(): array
    {
        $sql = "
            SELECT f.* FROM funcionarios f 
            WHERE f.status = 'ATIVO'
            AND f.cargo IN ('LAVADOR', 'DETALHISTA', 'POLIDOR', 'ATENDENTE')
            ORDER BY f.nome
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca funcionários por especialidade
     */
    public function findByEspecialidade(string $especialidade): array
    {
        $cargos = [];
        
        switch (strtoupper($especialidade)) {
            case 'LAVAGEM':
                $cargos = ['LAVADOR'];
                break;
            case 'POLIMENTO':
                $cargos = ['POLIDOR'];
                break;
            case 'DETALHAMENTO':
                $cargos = ['DETALHISTA'];
                break;
            case 'ATENDIMENTO':
                $cargos = ['ATENDENTE', 'RECEPCIONISTA'];
                break;
            default:
                $cargos = [$especialidade];
        }
        
        $placeholders = str_repeat('?,', count($cargos) - 1) . '?';
        $sql = "SELECT * FROM {$this->table} WHERE cargo IN ({$placeholders}) AND status = 'ATIVO' ORDER BY nome";
        
        return $this->db->query($sql, $cargos)->fetchAll();
    }
    
    /**
     * Atualiza salário do funcionário
     */
    public function atualizarSalario(int $id, float $novoSalario): bool
    {
        return $this->update($id, ['salario' => $novoSalario]);
    }
    
    /**
     * Atualiza cargo do funcionário
     */
    public function atualizarCargo(int $id, string $novoCargo): bool
    {
        return $this->update($id, ['cargo' => $novoCargo]);
    }
    
    /**
     * Atualiza departamento do funcionário
     */
    public function atualizarDepartamento(int $id, string $novoDepartamento): bool
    {
        return $this->update($id, ['departamento' => $novoDepartamento]);
    }
    
    /**
     * Estatísticas de funcionários
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de funcionários
        $stats['total'] = $this->count();
        
        // Funcionários por cargo
        $sql = "SELECT cargo, COUNT(*) as total FROM {$this->table} WHERE status = 'ATIVO' GROUP BY cargo ORDER BY total DESC";
        $stats['por_cargo'] = $this->db->query($sql)->fetchAll();
        
        // Funcionários por departamento
        $sql = "SELECT departamento, COUNT(*) as total FROM {$this->table} WHERE status = 'ATIVO' GROUP BY departamento ORDER BY total DESC";
        $stats['por_departamento'] = $this->db->query($sql)->fetchAll();
        
        // Funcionários por status
        $sql = "SELECT status, COUNT(*) as total FROM {$this->table} GROUP BY status ORDER BY total DESC";
        $stats['por_status'] = $this->db->query($sql)->fetchAll();
        
        // Faixa salarial
        $sql = "SELECT MIN(salario) as salario_min, MAX(salario) as salario_max, AVG(salario) as salario_medio FROM {$this->table} WHERE status = 'ATIVO' AND salario > 0";
        $stats['faixa_salarial'] = $this->db->query($sql)->fetch();
        
        // Tempo médio de empresa
        $sql = "
            SELECT AVG(strftime('%Y', 'now') - strftime('%Y', data_admissao)) as tempo_medio_empresa
            FROM {$this->table} 
            WHERE status = 'ATIVO' AND data_admissao IS NOT NULL
        ";
        $stats['tempo_medio_empresa'] = $this->db->query($sql)->fetch();
        
        return $stats;
    }
}
