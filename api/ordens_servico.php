<?php
/**
 * API de Ordens de Serviço
 * LJ-OS Sistema para Lava Jato
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/functions.php';

try {
    $db = getDB();
    $acao = $_GET['acao'] ?? '';
    
    switch ($acao) {
        case 'buscar':
            $id_os = $_GET['id'] ?? null;
            if (!$id_os) {
                throw new Exception('ID da OS não informado');
            }
            
            $stmt = $db->prepare("
                SELECT os.*, c.nome as cliente_nome, c.cpf_cnpj, v.placa, v.marca, v.modelo, v.cor,
                       a.data_agendamento, s.nome_servico
                FROM ordens_servico os
                LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
                LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo
                LEFT JOIN agendamentos a ON os.id_agendamento = a.id_agendamento
                LEFT JOIN servicos s ON a.id_servico = s.id_servico
                WHERE os.id_os = ?
            ");
            $stmt->execute([$id_os]);
            $os = $stmt->fetch();
            
            if (!$os) {
                throw new Exception('OS não encontrada');
            }
            
            // Buscar serviços da OS
            $stmt = $db->prepare("
                SELECT oss.*, s.nome_servico, s.descricao
                FROM os_servicos oss
                LEFT JOIN servicos s ON oss.id_servico = s.id_servico
                WHERE oss.id_os = ?
            ");
            $stmt->execute([$id_os]);
            $os['servicos'] = $stmt->fetchAll();
            
            // Buscar produtos da OS
            $stmt = $db->prepare("
                SELECT osp.*, p.nome_produto, p.descricao
                FROM os_produtos osp
                LEFT JOIN produtos p ON osp.id_produto = p.id_produto
                WHERE osp.id_os = ?
            ");
            $stmt->execute([$id_os]);
            $os['produtos'] = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $os]);
            break;
            
        case 'listar':
            $filtro_status = $_GET['status'] ?? '';
            $filtro_cliente = $_GET['cliente'] ?? '';
            $filtro_data = $_GET['data'] ?? '';
            $limite = $_GET['limite'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            
            $where = "WHERE 1=1";
            $params = [];
            
            if ($filtro_status) {
                $where .= " AND os.status = ?";
                $params[] = $filtro_status;
            }
            
            if ($filtro_cliente) {
                $where .= " AND c.nome LIKE ?";
                $params[] = "%$filtro_cliente%";
            }
            
            if ($filtro_data) {
                $where .= " AND DATE(os.data_abertura) = ?";
                $params[] = $filtro_data;
            }
            
            $sql = "
                SELECT os.*, c.nome as cliente_nome, v.placa, v.marca, v.modelo
                FROM ordens_servico os
                LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
                LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo
                $where
                ORDER BY os.data_abertura DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = (int)$limite;
            $params[] = (int)$offset;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $ordens = $stmt->fetchAll();
            
            // Contar total
            $sql_count = "
                SELECT COUNT(*) as total
                FROM ordens_servico os
                LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
                $where
            ";
            
            $stmt = $db->prepare($sql_count);
            $stmt->execute(array_slice($params, 0, -2));
            $total = $stmt->fetch()['total'];
            
            echo json_encode([
                'success' => true, 
                'data' => $ordens,
                'total' => $total,
                'limite' => $limite,
                'offset' => $offset
            ]);
            break;
            
        case 'alterar_status':
            $id_os = $_POST['id_os'] ?? null;
            $novo_status = $_POST['status'] ?? null;
            
            if (!$id_os || !$novo_status) {
                throw new Exception('ID da OS e novo status são obrigatórios');
            }
            
            $stmt = $db->prepare("UPDATE ordens_servico SET status = ? WHERE id_os = ?");
            $stmt->execute([$novo_status, $id_os]);
            
            echo json_encode(['success' => true, 'message' => 'Status alterado com sucesso']);
            break;
            
        case 'verificar_disponibilidade':
            $data = $_GET['data'] ?? '';
            $vaga = $_GET['vaga'] ?? '';
            
            if (!$data || !$vaga) {
                throw new Exception('Data e vaga são obrigatórios');
            }
            
            $stmt = $db->prepare("
                SELECT COUNT(*) as total
                FROM ordens_servico
                WHERE DATE(data_abertura) = ? AND vaga = ? AND status IN ('aberta', 'em_andamento')
            ");
            $stmt->execute([$data, $vaga]);
            $resultado = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'disponivel' => $resultado['total'] == 0,
                'ocupada' => $resultado['total'] > 0
            ]);
            break;
            
        case 'agendamentos_hoje':
            $data = date('Y-m-d');
            
            $stmt = $db->prepare("
                SELECT os.*, c.nome as cliente_nome, v.placa, v.marca, v.modelo
                FROM ordens_servico os
                LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
                LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo
                WHERE DATE(os.data_abertura) = ? AND os.status IN ('aberta', 'em_andamento')
                ORDER BY os.data_abertura ASC
            ");
            $stmt->execute([$data]);
            $agendamentos = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $agendamentos]);
            break;
            
        case 'agendamentos_semana':
            $data_inicio = date('Y-m-d');
            $data_fim = date('Y-m-d', strtotime('+7 days'));
            
            $stmt = $db->prepare("
                SELECT os.*, c.nome as cliente_nome, v.placa, v.marca, v.modelo
                FROM ordens_servico os
                LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
                LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo
                WHERE DATE(os.data_abertura) BETWEEN ? AND ? AND os.status IN ('aberta', 'em_andamento')
                ORDER BY os.data_abertura ASC
            ");
            $stmt->execute([$data_inicio, $data_fim]);
            $agendamentos = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $agendamentos]);
            break;
            
        case 'estatisticas':
            // Estatísticas gerais
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_os,
                    COUNT(CASE WHEN status = 'aberta' THEN 1 END) as abertas,
                    COUNT(CASE WHEN status = 'em_andamento' THEN 1 END) as em_andamento,
                    COUNT(CASE WHEN status = 'finalizada' THEN 1 END) as finalizadas,
                    COUNT(CASE WHEN status = 'cancelada' THEN 1 END) as canceladas,
                    SUM(valor_total) as valor_total
                FROM ordens_servico
                WHERE DATE(data_abertura) = CURDATE()
            ");
            $stmt->execute();
            $hoje = $stmt->fetch();
            
            // Estatísticas da semana
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_os,
                    SUM(valor_total) as valor_total
                FROM ordens_servico
                WHERE data_abertura >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ");
            $stmt->execute();
            $semana = $stmt->fetch();
            
            // Estatísticas do mês
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_os,
                    SUM(valor_total) as valor_total
                FROM ordens_servico
                WHERE data_abertura >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $mes = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'hoje' => $hoje,
                    'semana' => $semana,
                    'mes' => $mes
                ]
            ]);
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 