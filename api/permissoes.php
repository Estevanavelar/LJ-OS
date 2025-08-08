<?php
/**
 * API de Permissões
 * LJ-OS Sistema para Lava Jato
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}

// Obter conexão com o banco de dados
global $pdo;
$pdo = getDB();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'estatisticas':
            getEstatisticas();
            break;
        case 'usuarios':
            getUsuarios();
            break;
        case 'perfis':
            getPerfis();
            break;
        case 'logs':
            getLogs();
            break;
        case 'permissoes_usuario':
            getPermissoesUsuario();
            break;
        case 'filtrar_logs':
            filtrarLogs();
            break;
        default:
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                salvarPermissao();
            } else {
                http_response_code(400);
                echo json_encode(['erro' => 'Ação não reconhecida']);
            }
    }
} catch (Exception $e) {
    error_log("Erro na API de permissões: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno do servidor']);
}

function getEstatisticas() {
    global $pdo;
    
    try {
        // Usuários ativos
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios WHERE status = 'ativo'");
        $stmt->execute();
        $usuarios_ativos = $stmt->fetch()['total'];
        
        // Perfis criados
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM perfis_acesso WHERE status = 'ativo'");
        $stmt->execute();
        $perfis_criados = $stmt->fetch()['total'];
        
        // Permissões ativas
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM permissoes WHERE ativo = 1");
        $stmt->execute();
        $permissoes_ativas = $stmt->fetch()['total'];
        
        // Módulos protegidos
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT modulo) as total FROM permissoes WHERE ativo = 1");
        $stmt->execute();
        $modulos_protegidos = $stmt->fetch()['total'];
        
        echo json_encode([
            'sucesso' => true,
            'estatisticas' => [
                'usuarios_ativos' => $usuarios_ativos,
                'perfis_criados' => $perfis_criados,
                'permissoes_ativas' => $permissoes_ativas,
                'modulos_protegidos' => $modulos_protegidos
            ]
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao buscar estatísticas: ' . $e->getMessage());
    }
}

function getUsuarios() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                p.nome as perfil,
                COUNT(perm.id_permissao) as total_permissoes,
                MAX(l.data_hora) as ultimo_acesso
            FROM usuarios u
            LEFT JOIN perfis_acesso p ON u.id_perfil = p.id_perfil
            LEFT JOIN permissoes perm ON u.id_usuario = perm.id_usuario
            GROUP BY u.id_usuario
            ORDER BY u.nome ASC
        ");
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'sucesso' => true,
            'usuarios' => $usuarios
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao listar usuários: ' . $e->getMessage());
    }
}

function getPerfis() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                p.*,
                COUNT(u.id_usuario) as total_usuarios,
                COUNT(perm.id_permissao) as total_permissoes
            FROM perfis_acesso p
            LEFT JOIN usuarios u ON p.id_perfil = u.id_perfil
            LEFT JOIN permissoes_perfil perm ON p.id_perfil = perm.id_perfil
            WHERE p.status = 'ativo'
            GROUP BY p.id_perfil
            ORDER BY p.nome ASC
        ");
        $stmt->execute();
        $perfis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'sucesso' => true,
            'perfis' => $perfis
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao listar perfis: ' . $e->getMessage());
    }
}

function getLogs() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                l.*,
                u.nome as usuario_nome
            FROM logs_acesso l
            JOIN usuarios u ON l.id_usuario = u.id_usuario
            ORDER BY l.data_hora DESC
            LIMIT 100
        ");
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar usuários para filtro
        $stmt = $pdo->prepare("SELECT id_usuario, nome FROM usuarios ORDER BY nome");
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'sucesso' => true,
            'logs' => $logs,
            'usuarios' => $usuarios
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao buscar logs: ' . $e->getMessage());
    }
}

function getPermissoesUsuario() {
    global $pdo;
    
    $id = $_GET['id'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID do usuário não informado']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT modulo, funcionalidade 
            FROM permissoes 
            WHERE id_usuario = ? AND ativo = 1
        ");
        $stmt->execute([$id]);
        $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'sucesso' => true,
            'permissoes' => $permissoes
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao buscar permissões: ' . $e->getMessage());
    }
}

function filtrarLogs() {
    global $pdo;
    
    $usuario = $_GET['usuario'] ?? '';
    $acao = $_GET['acao'] ?? '';
    $dataInicio = $_GET['data_inicio'] ?? '';
    $dataFim = $_GET['data_fim'] ?? '';
    
    try {
        $where = "WHERE 1=1";
        $params = [];
        
        if ($usuario) {
            $where .= " AND l.id_usuario = ?";
            $params[] = $usuario;
        }
        
        if ($acao) {
            $where .= " AND l.acao = ?";
            $params[] = $acao;
        }
        
        if ($dataInicio) {
            $where .= " AND DATE(l.data_hora) >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $where .= " AND DATE(l.data_hora) <= ?";
            $params[] = $dataFim;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                l.*,
                u.nome as usuario_nome
            FROM logs_acesso l
            JOIN usuarios u ON l.id_usuario = u.id_usuario
            $where
            ORDER BY l.data_hora DESC
            LIMIT 100
        ");
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'sucesso' => true,
            'logs' => $logs
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao filtrar logs: ' . $e->getMessage());
    }
}

function salvarPermissao() {
    global $pdo;
    
    $usuarioId = $_POST['usuario_id'] ?? $_POST['usuario'] ?? '';
    $perfilId = $_POST['perfil'] ?? '';
    $permissoes = $_POST['permissoes'] ?? [];
    
    if (!$usuarioId) {
        http_response_code(400);
        echo json_encode(['erro' => 'Usuário não informado']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Se foi informado um perfil, aplicar permissões do perfil
        if ($perfilId) {
            // Atualizar usuário com o perfil
            $stmt = $pdo->prepare("UPDATE usuarios SET id_perfil = ? WHERE id_usuario = ?");
            $stmt->execute([$perfilId, $usuarioId]);
            
            // Buscar permissões do perfil
            $stmt = $pdo->prepare("
                SELECT modulo, funcionalidade 
                FROM permissoes_perfil 
                WHERE id_perfil = ? AND ativo = 1
            ");
            $stmt->execute([$perfilId]);
            $permissoesPerfil = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Aplicar permissões do perfil ao usuário
            foreach ($permissoesPerfil as $permissao) {
                $stmt = $pdo->prepare("
                    INSERT INTO permissoes (id_usuario, modulo, funcionalidade, ativo, data_criacao)
                    VALUES (?, ?, ?, 1, NOW())
                    ON DUPLICATE KEY UPDATE ativo = 1, data_atualizacao = NOW()
                ");
                $stmt->execute([$usuarioId, $permissao['modulo'], $permissao['funcionalidade']]);
            }
        }
        
        // Aplicar permissões específicas
        if (!empty($permissoes)) {
            // Primeiro, desativar todas as permissões do usuário
            $stmt = $pdo->prepare("UPDATE permissoes SET ativo = 0 WHERE id_usuario = ?");
            $stmt->execute([$usuarioId]);
            
            // Depois, ativar apenas as permissões selecionadas
            foreach ($permissoes as $modulo => $funcionalidades) {
                foreach ($funcionalidades as $funcionalidade => $valor) {
                    if ($valor == '1') {
                        $stmt = $pdo->prepare("
                            INSERT INTO permissoes (id_usuario, modulo, funcionalidade, ativo, data_criacao)
                            VALUES (?, ?, ?, 1, NOW())
                            ON DUPLICATE KEY UPDATE ativo = 1, data_atualizacao = NOW()
                        ");
                        $stmt->execute([$usuarioId, $modulo, $funcionalidade]);
                    }
                }
            }
        }
        
        $pdo->commit();
        
        // Registrar log
        registrarLog("Permissões configuradas", $_SESSION['usuario_id'], null, $usuarioId, [
            'perfil' => $perfilId,
            'permissoes' => count($permissoes)
        ]);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Permissões salvas com sucesso'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception('Erro ao salvar permissões: ' . $e->getMessage());
    }
}

// Função para verificar permissões (usada em outras partes do sistema)
function verificarPermissao($modulo, $funcionalidade = 'visualizar') {
    global $pdo;
    
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    try {
        // Verificar permissão direta do usuário
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM permissoes 
            WHERE id_usuario = ? AND modulo = ? AND funcionalidade = ? AND ativo = 1
        ");
        $stmt->execute([$_SESSION['usuario_id'], $modulo, $funcionalidade]);
        $permissaoDireta = $stmt->fetch()['total'] > 0;
        
        if ($permissaoDireta) {
            return true;
        }
        
        // Verificar permissão via perfil
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM permissoes_perfil pp
            JOIN usuarios u ON pp.id_perfil = u.id_perfil
            WHERE u.id_usuario = ? AND pp.modulo = ? AND pp.funcionalidade = ? AND pp.ativo = 1
        ");
        $stmt->execute([$_SESSION['usuario_id'], $modulo, $funcionalidade]);
        $permissaoPerfil = $stmt->fetch()['total'] > 0;
        
        return $permissaoPerfil;
    } catch (Exception $e) {
        error_log("Erro ao verificar permissão: " . $e->getMessage());
        return false;
    }
}
?> 