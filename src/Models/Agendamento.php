<?php

namespace LJOS\Models;

/**
 * Modelo de Agendamento
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Agendamento extends BaseModel
{
    protected $table = 'agendamentos';
    protected $primaryKey = 'id_agendamento';
    
    protected $fillable = [
        'id_cliente',
        'id_veiculo',
        'id_servico',
        'data_agendamento',
        'hora_entrega_estimada',
        'status',
        'observacoes'
    ];
    
    protected $casts = [
        'data_criacao' => 'datetime',
        'data_agendamento' => 'datetime',
        'hora_entrega_estimada' => 'datetime'
    ];
    
    /**
     * Busca agendamentos por cliente
     */
    public function findByCliente(int $idCliente): array
    {
        return $this->findAllBy('id_cliente', $idCliente);
    }
    
    /**
     * Busca agendamentos por veículo
     */
    public function findByVeiculo(int $idVeiculo): array
    {
        return $this->findAllBy('id_veiculo', $idVeiculo);
    }
    
    /**
     * Busca agendamentos por serviço
     */
    public function findByServico(int $idServico): array
    {
        return $this->findAllBy('id_servico', $idServico);
    }
    
    /**
     * Busca agendamentos por status
     */
    public function findByStatus(string $status): array
    {
        return $this->findAllBy('status', $status);
    }
    
    /**
     * Busca agendamentos por data
     */
    public function findByData(string $data): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_agendamento) = ? ORDER BY data_agendamento";
        return $this->db->query($sql, [$data])->fetchAll();
    }
    
    /**
     * Busca agendamentos por período
     */
    public function findByPeriodo(string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_agendamento) BETWEEN ? AND ? ORDER BY data_agendamento";
        return $this->db->query($sql, [$dataInicio, $dataFim])->fetchAll();
    }
    
    /**
     * Busca agendamentos do dia
     */
    public function findDoDia(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(data_agendamento) = CURDATE() ORDER BY data_agendamento";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca agendamentos da semana
     */
    public function findDaSemana(): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE YEARWEEK(data_agendamento, 1) = YEARWEEK(CURDATE(), 1) 
            ORDER BY data_agendamento
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca agendamentos do mês
     */
    public function findDoMes(): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE YEAR(data_agendamento) = YEAR(CURDATE()) 
            AND MONTH(data_agendamento) = MONTH(CURDATE())
            ORDER BY data_agendamento
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Busca agendamento com dados completos
     */
    public function findCompleto(int $id)
    {
        $sql = "
            SELECT a.*, 
                   c.nome as nome_cliente, c.telefone as telefone_cliente, c.email as email_cliente,
                   v.placa, v.marca, v.modelo, v.cor,
                   s.nome as nome_servico, s.preco, s.duracao_estimada
            FROM agendamentos a 
            LEFT JOIN clientes c ON a.id_cliente = c.id_cliente
            LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
            LEFT JOIN servicos s ON a.id_servico = s.id_servico
            WHERE a.id_agendamento = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca agendamentos por horário
     */
    public function findByHorario(string $data, string $horaInicio, string $horaFim): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE DATE(data_agendamento) = ? 
            AND TIME(data_agendamento) BETWEEN ? AND ?
            ORDER BY data_agendamento
        ";
        return $this->db->query($sql, [$data, $horaInicio, $horaFim])->fetchAll();
    }
    
    /**
     * Verifica disponibilidade de horário
     */
    public function verificarDisponibilidade(string $data, string $hora, int $duracaoMinutos): bool
    {
        $horaInicio = $hora;
        $horaFim = date('H:i:s', strtotime("+{$duracaoMinutos} minutes", strtotime($hora)));
        
        $sql = "
            SELECT COUNT(*) as total FROM {$this->table} 
            WHERE DATE(data_agendamento) = ? 
            AND status NOT IN ('CANCELADO')
            AND (
                (TIME(data_agendamento) <= ? AND TIME(hora_entrega_estimada) > ?) OR
                (TIME(data_agendamento) < ? AND TIME(hora_entrega_estimada) >= ?) OR
                (TIME(data_agendamento) >= ? AND TIME(hora_entrega_estimada) <= ?)
            )
        ";
        
        $result = $this->db->query($sql, [$data, $horaInicio, $horaInicio, $horaFim, $horaFim, $horaInicio, $horaFim])->fetch();
        return (int) ($result['total'] ?? 0) === 0;
    }
    
    /**
     * Busca próximos horários disponíveis
     */
    public function findHorariosDisponiveis(string $data, int $duracaoMinutos, int $limit = 10): array
    {
        $horarios = [];
        $horaInicio = '08:00:00';
        $horaFim = '18:00:00';
        $intervalo = 30; // 30 minutos
        
        for ($hora = strtotime($horaInicio); $hora <= strtotime($horaFim); $hora += ($intervalo * 60)) {
            $horaStr = date('H:i:s', $hora);
            
            if ($this->verificarDisponibilidade($data, $horaStr, $duracaoMinutos)) {
                $horarios[] = $horaStr;
                
                if (count($horarios) >= $limit) {
                    break;
                }
            }
        }
        
        return $horarios;
    }
    
    /**
     * Atualiza status do agendamento
     */
    public function atualizarStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * Estatísticas de agendamentos
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de agendamentos
        $stats['total'] = $this->count();
        
        // Agendamentos por status
        $sql = "SELECT status, COUNT(*) as total FROM {$this->table} GROUP BY status ORDER BY total DESC";
        $stats['por_status'] = $this->db->query($sql)->fetchAll();
        
        // Agendamentos por dia da semana
        $sql = "
            SELECT 
                CASE 
                    WHEN strftime('%w', data_agendamento) = '0' THEN 'Domingo'
                    WHEN strftime('%w', data_agendamento) = '1' THEN 'Segunda'
                    WHEN strftime('%w', data_agendamento) = '2' THEN 'Terça'
                    WHEN strftime('%w', data_agendamento) = '3' THEN 'Quarta'
                    WHEN strftime('%w', data_agendamento) = '4' THEN 'Quinta'
                    WHEN strftime('%w', data_agendamento) = '5' THEN 'Sexta'
                    WHEN strftime('%w', data_agendamento) = '6' THEN 'Sábado'
                END as dia_semana,
                COUNT(*) as total
            FROM {$this->table} 
            WHERE data_agendamento >= date('now', '-30 days')
            GROUP BY strftime('%w', data_agendamento)
            ORDER BY strftime('%w', data_agendamento)
        ";
        $stats['por_dia_semana'] = $this->db->query($sql)->fetchAll();
        
        // Agendamentos por mês
        $sql = "
            SELECT 
                strftime('%Y-%m', data_agendamento) as mes,
                COUNT(*) as total
            FROM {$this->table} 
            WHERE data_agendamento >= date('now', '-12 months')
            GROUP BY strftime('%Y-%m', data_agendamento)
            ORDER BY mes DESC
        ";
        $stats['por_mes'] = $this->db->query($sql)->fetchAll();
        
        return $stats;
    }
}
