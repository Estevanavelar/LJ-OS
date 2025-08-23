<?php

namespace LJOS\Models;

/**
 * Modelo de Usuário
 * 
 * @package LJOS\Models
 * @author LJ-OS Team
 * @version 1.0.0
 */
class Usuario extends BaseModel
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    
    protected $fillable = [
        'nome',
        'email',
        'senha',
        'nivel_acesso',
        'status',
        'foto_perfil',
        'telefone',
        'observacoes'
    ];
    
    protected $hidden = [
        'senha'
    ];
    
    protected $casts = [
        'data_cadastro' => 'datetime',
        'ultimo_login' => 'datetime'
    ];
    
    /**
     * Busca usuário por email
     */
    public function findByEmail(string $email)
    {
        return $this->findBy('email', $email);
    }
    
    /**
     * Busca usuários por nível de acesso
     */
    public function findByNivelAcesso(string $nivel): array
    {
        return $this->findAllBy('nivel_acesso', $nivel);
    }
    
    /**
     * Busca usuários ativos
     */
    public function findAtivos(): array
    {
        return $this->findAllBy('status', 'ATIVO');
    }
    
    /**
     * Atualiza último login
     */
    public function updateUltimoLogin(int $id): bool
    {
        return $this->update($id, [
            'ultimo_login' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Cria hash da senha
     */
    public function hashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }
    
    /**
     * Verifica se a senha está correta
     */
    public function verificarSenha(string $senha, string $hash): bool
    {
        return password_verify($senha, $hash);
    }
    
    /**
     * Busca usuário com dados do funcionário
     */
    public function findWithFuncionario(int $id)
    {
        $sql = "
            SELECT u.*, f.* 
            FROM usuarios u 
            LEFT JOIN funcionarios f ON u.id_usuario = f.id_usuario 
            WHERE u.id_usuario = ?
        ";
        
        return $this->rawQueryOne($sql, [$id]);
    }
    
    /**
     * Busca todos os usuários com dados de funcionário
     */
    public function allWithFuncionario(): array
    {
        $sql = "
            SELECT u.*, f.* 
            FROM usuarios u 
            LEFT JOIN funcionarios f ON u.id_usuario = f.id_usuario 
            ORDER BY u.nome
        ";
        
        return $this->rawQuery($sql);
    }
    
    /**
     * Busca usuários por departamento
     */
    public function findByDepartamento(string $departamento): array
    {
        $sql = "
            SELECT u.*, f.departamento 
            FROM usuarios u 
            INNER JOIN funcionarios f ON u.id_usuario = f.id_usuario 
            WHERE f.departamento = ?
            ORDER BY u.nome
        ";
        
        return $this->rawQuery($sql, [$departamento]);
    }
    
    /**
     * Busca usuários com permissões específicas
     */
    public function findByPermissoes(array $permissoes): array
    {
        $placeholders = str_repeat('?,', count($permissoes) - 1) . '?';
        $sql = "
            SELECT u.* 
            FROM usuarios u 
            WHERE u.nivel_acesso IN ({$placeholders})
            ORDER BY u.nome
        ";
        
        return $this->rawQuery($sql, $permissoes);
    }
    
    /**
     * Atualiza status do usuário
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * Atualiza nível de acesso
     */
    public function updateNivelAcesso(int $id, string $nivel): bool
    {
        return $this->update($id, ['nivel_acesso' => $nivel]);
    }
    
    /**
     * Busca usuários por período de cadastro
     */
    public function findByPeriodoCadastro(string $dataInicio, string $dataFim): array
    {
        $sql = "
            SELECT * FROM usuarios 
            WHERE DATE(data_cadastro) BETWEEN ? AND ?
            ORDER BY data_cadastro DESC
        ";
        
        return $this->rawQuery($sql, [$dataInicio, $dataFim]);
    }
    
    /**
     * Busca usuários inativos por tempo
     */
    public function findInativosPorTempo(int $dias): array
    {
        $sql = "
            SELECT * FROM usuarios 
            WHERE status = 'INATIVO' 
            AND ultimo_login < datetime('now', '-{$dias} days')
            ORDER BY ultimo_login
        ";
        
        return $this->rawQuery($sql);
    }
    
    /**
     * Busca estatísticas de usuários
     */
    public function getEstatisticas(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'ATIVO' THEN 1 END) as ativos,
                COUNT(CASE WHEN status = 'INATIVO' THEN 1 END) as inativos,
                COUNT(CASE WHEN nivel_acesso = 'ADMIN' THEN 1 END) as admins,
                COUNT(CASE WHEN nivel_acesso = 'GERENTE' THEN 1 END) as gerentes,
                COUNT(CASE WHEN nivel_acesso = 'ATENDENTE' THEN 1 END) as atendentes,
                COUNT(CASE WHEN nivel_acesso = 'FUNCIONARIO' THEN 1 END) as funcionarios
            FROM usuarios
        ";
        
        return $this->rawQueryOne($sql);
    }
    
    /**
     * Busca usuários com login recente
     */
    public function findComLoginRecente(int $horas = 24): array
    {
        $sql = "
            SELECT * FROM usuarios 
            WHERE ultimo_login > datetime('now', '-{$horas} hours')
            ORDER BY ultimo_login DESC
        ";
        
        return $this->rawQuery($sql);
    }
    
    /**
     * Busca usuários sem login
     */
    public function findSemLogin(): array
    {
        $sql = "
            SELECT * FROM usuarios 
            WHERE ultimo_login IS NULL
            ORDER BY data_cadastro
        ";
        
        return $this->rawQuery($sql);
    }
}
