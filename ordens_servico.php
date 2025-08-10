<?php
/**
 * Módulo de Ordens de Serviço - Totalmente Responsivo
 * LJ-OS Sistema para Lava Jato
 */

require_once 'includes/functions.php';

// Verificar login
verificarLogin();

$db = getDB();
$acao = $_GET['acao'] ?? 'listar';
$id_os = $_GET['id'] ?? null;
$erro = '';
$sucesso = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    try {
        if ($acao === 'salvar') {
            $id_cliente = $_POST['id_cliente'];
            $id_veiculo = $_POST['id_veiculo'];
            $id_agendamento = $_POST['id_agendamento'] ?: null;
            $observacoes = $_POST['observacoes'];
            $vaga = $_POST['vaga'];
            $km_veiculo = $_POST['km_veiculo'];
            
            if ($id_os) {
                // Atualizar OS existente
                $stmt = $db->prepare("UPDATE ordens_servico SET id_cliente = ?, id_veiculo = ?, id_agendamento = ?, observacoes = ?, vaga = ?, km_veiculo = ? WHERE id_os = ?");
                $stmt->execute([$id_cliente, $id_veiculo, $id_agendamento, $observacoes, $vaga, $km_veiculo, $id_os]);
                $sucesso = 'Ordem de serviço atualizada com sucesso!';
            } else {
                // Criar nova OS
                $codigo_os = 'OS-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $stmt = $db->prepare("INSERT INTO ordens_servico (codigo_os, id_cliente, id_veiculo, id_agendamento, observacoes, vaga, km_veiculo, usuario_abertura) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$codigo_os, $id_cliente, $id_veiculo, $id_agendamento, $observacoes, $vaga, $km_veiculo, $_SESSION['usuario_id']]);
                $id_os = $db->lastInsertId();
                $sucesso = 'Ordem de serviço criada com sucesso!';
            }
        } elseif ($acao === 'alterar_status') {
            $novo_status = $_POST['novo_status'];
            $stmt = $db->prepare("UPDATE ordens_servico SET status = ? WHERE id_os = ?");
            $stmt->execute([$novo_status, $id_os]);
            $sucesso = 'Status alterado com sucesso!';
        }
    } catch (Exception $e) {
        $erro = 'Erro ao processar: ' . $e->getMessage();
    }
}

// Buscar dados para formulário
$clientes = [];
$veiculos = [];
$agendamentos = [];
$servicos = [];
$produtos = [];

if ($acao === 'editar' || $acao === 'visualizar' || $acao === 'salvar') {
    // Buscar clientes
    $stmt = $db->prepare("SELECT id_cliente, nome, cpf_cnpj FROM clientes WHERE status = 'ativo' ORDER BY nome");
    $stmt->execute();
    $clientes = $stmt->fetchAll();
    
    // Buscar serviços
    $stmt = $db->prepare("SELECT id_servico, nome_servico, preco FROM servicos WHERE status = 'ativo' ORDER BY nome_servico");
    $stmt->execute();
    $servicos = $stmt->fetchAll();
    
    // Buscar produtos
    $stmt = $db->prepare("SELECT id_produto, nome_produto, preco_venda, estoque_atual FROM produtos WHERE status = 'ativo' ORDER BY nome_produto");
    $stmt->execute();
    $produtos = $stmt->fetchAll();
    
    if ($id_os) {
        // Buscar OS específica
        $stmt = $db->prepare("SELECT * FROM ordens_servico WHERE id_os = ?");
        $stmt->execute([$id_os]);
        $os = $stmt->fetch();
        
        if ($os) {
            // Buscar veículos do cliente
            $stmt = $db->prepare("SELECT id_veiculo, placa, marca, modelo FROM veiculos WHERE id_cliente = ? AND status = 'ativo'");
            $stmt->execute([$os['id_cliente']]);
            $veiculos = $stmt->fetchAll();
            
            // Buscar agendamentos do cliente
            $stmt = $db->prepare("SELECT id_agendamento, data_agendamento, id_servico FROM agendamentos WHERE id_cliente = ? AND status IN ('pendente', 'confirmado')");
            $stmt->execute([$os['id_cliente']]);
            $agendamentos = $stmt->fetchAll();
        }
    }
}

// Buscar lista de OS
if ($acao === 'listar') {
    $filtro_status = $_GET['status'] ?? '';
    $filtro_cliente = $_GET['cliente'] ?? '';
    $filtro_data = $_GET['data'] ?? '';
    
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
    
    $sql = "SELECT os.*, c.nome as cliente_nome, v.placa, v.marca, v.modelo 
            FROM ordens_servico os 
            LEFT JOIN clientes c ON os.id_cliente = c.id_cliente 
            LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo 
            $where 
            ORDER BY os.data_abertura DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $ordens_servico = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<style>
/* Estilos personalizados para a página de OS */
.table th {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(189, 191, 144, 0.1);
}

.form-control-sm, .form-select-sm {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
}

/* Responsividade */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .btn-sm {
        padding: 0.125rem 0.25rem;
        font-size: 0.7rem;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .dropdown-menu {
        font-size: 0.875rem;
    }
}


</style>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-12 px-md-4">
            <!-- Header simples -->
            <div class="d-flex justify-content-between align-items-center py-3 mb-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-clipboard-list text-primary me-2" style="font-size: 1.5rem;"></i>
                    <h4 class="mb-0">Ordens de Serviço</h4>
                </div>
                <?php if ($acao === 'listar'): ?>
                    <div class="d-flex gap-2">
                        <a href="?acao=editar" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Nova OS
                        </a>
                        <button class="btn btn-outline-secondary" onclick="toggleFiltros()">
                            <i class="fas fa-filter me-1"></i> Filtros
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $erro; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($acao === 'listar'): ?>
                <!-- Filtros colapsáveis -->
                <div class="card mb-3" id="filtrosCard" style="display: none;">
                    <div class="card-body py-3">
                        <form method="GET" class="row g-2">
                            <input type="hidden" name="acao" value="listar">
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="aberta" <?php echo $filtro_status === 'aberta' ? 'selected' : ''; ?>>Aberta</option>
                                    <option value="em_andamento" <?php echo $filtro_status === 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="finalizada" <?php echo $filtro_status === 'finalizada' ? 'selected' : ''; ?>>Finalizada</option>
                                    <option value="cancelada" <?php echo $filtro_status === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Cliente</label>
                                <input type="text" name="cliente" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filtro_cliente); ?>" placeholder="Nome do cliente">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Data</label>
                                <input type="date" name="data" class="form-control form-control-sm" value="<?php echo $filtro_data; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search me-1"></i> Filtrar
                                    </button>
                                    <a href="?acao=listar" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times me-1"></i> Limpar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de OS -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-2 px-3">Código</th>
                                        <th class="py-2 px-3">Cliente</th>
                                        <th class="py-2 px-3">Veículo</th>
                                        <th class="py-2 px-3">Data</th>
                                        <th class="py-2 px-3">Valor</th>
                                        <th class="py-2 px-3">Status</th>
                                        <th class="py-2 px-3 text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ordens_servico as $os): ?>
                                        <tr>
                                            <td class="py-2 px-3">
                                                <strong><?php echo htmlspecialchars($os['codigo_os']); ?></strong>
                                            </td>
                                            <td class="py-2 px-3"><?php echo htmlspecialchars($os['cliente_nome']); ?></td>
                                            <td class="py-2 px-3">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($os['marca'] . ' ' . $os['modelo']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($os['placa']); ?></small>
                                                </div>
                                            </td>
                                            <td class="py-2 px-3"><?php echo date('d/m/Y H:i', strtotime($os['data_abertura'])); ?></td>
                                            <td class="py-2 px-3">
                                                <strong>R$ <?php echo number_format($os['valor_total'], 2, ',', '.'); ?></strong>
                                            </td>
                                            <td class="py-2 px-3">
                                                <?php
                                                $status_class = [
                                                    'aberta' => 'warning',
                                                    'em_andamento' => 'info',
                                                    'finalizada' => 'success',
                                                    'cancelada' => 'danger'
                                                ];
                                                $status_text = [
                                                    'aberta' => 'Aberta',
                                                    'em_andamento' => 'Em Andamento',
                                                    'finalizada' => 'Finalizada',
                                                    'cancelada' => 'Cancelada'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $status_class[$os['status']]; ?>">
                                                    <?php echo $status_text[$os['status']]; ?>
                                                </span>
                                            </td>
                                            <td class="py-2 px-3">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="?acao=visualizar&id=<?php echo $os['id_os']; ?>" class="btn btn-outline-primary btn-sm" title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="?acao=editar&id=<?php echo $os['id_os']; ?>" class="btn btn-outline-warning btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-info btn-sm" title="Alterar Status" 
                                                            onclick="alterarStatus(<?php echo $os['id_os']; ?>, '<?php echo $os['status']; ?>')">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($acao === 'editar' || $acao === 'visualizar'): ?>
                <!-- Formulário de OS -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?php echo $acao === 'editar' ? 'Editar' : 'Visualizar'; ?> Ordem de Serviço
                            <?php if ($id_os): ?>
                                - <?php echo htmlspecialchars($os['codigo_os']); ?>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?acao=salvar<?php echo $id_os ? '&id=' . $id_os : ''; ?>">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Cliente *</label>
                                        <select name="id_cliente" class="form-select" required <?php echo $acao === 'visualizar' ? 'disabled' : ''; ?>>
                                            <option value="">Selecione um cliente</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                <option value="<?php echo $cliente['id_cliente']; ?>" 
                                                        <?php echo ($id_os && $os['id_cliente'] == $cliente['id_cliente']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cliente['nome'] . ' - ' . $cliente['cpf_cnpj']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Veículo *</label>
                                        <select name="id_veiculo" class="form-select" required <?php echo $acao === 'visualizar' ? 'disabled' : ''; ?>>
                                            <option value="">Selecione um veículo</option>
                                            <?php foreach ($veiculos as $veiculo): ?>
                                                <option value="<?php echo $veiculo['id_veiculo']; ?>" 
                                                        <?php echo ($id_os && $os['id_veiculo'] == $veiculo['id_veiculo']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($veiculo['marca'] . ' ' . $veiculo['modelo'] . ' - ' . $veiculo['placa']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Agendamento</label>
                                        <select name="id_agendamento" class="form-select" <?php echo $acao === 'visualizar' ? 'disabled' : ''; ?>>
                                            <option value="">Nenhum agendamento</option>
                                            <?php foreach ($agendamentos as $agendamento): ?>
                                                <option value="<?php echo $agendamento['id_agendamento']; ?>" 
                                                        <?php echo ($id_os && $os['id_agendamento'] == $agendamento['id_agendamento']) ? 'selected' : ''; ?>>
                                                    <?php echo date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Vaga</label>
                                        <input type="number" name="vaga" class="form-control" value="<?php echo $id_os ? $os['vaga'] : ''; ?>" 
                                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">KM do Veículo</label>
                                        <input type="number" name="km_veiculo" class="form-control" value="<?php echo $id_os ? $os['km_veiculo'] : ''; ?>" 
                                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observações</label>
                                <textarea name="observacoes" class="form-control" rows="3" <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>><?php echo $id_os ? htmlspecialchars($os['observacoes']) : ''; ?></textarea>
                            </div>

                            <?php if ($acao === 'editar'): ?>
                                <div class="d-flex justify-content-between">
                                    <a href="?acao=listar" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Voltar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Salvar OS
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-content-between">
                                    <a href="?acao=listar" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Voltar
                                    </a>
                                    <a href="?acao=editar&id=<?php echo $id_os; ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Modal Alterar Status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alterar Status da OS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?acao=alterar_status&id=<?php echo $id_os; ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Novo Status</label>
                        <select name="novo_status" class="form-select" required>
                            <option value="aberta">Aberta</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="finalizada">Finalizada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Alterar Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Carregar veículos quando cliente for selecionado
document.querySelector('select[name="id_cliente"]').addEventListener('change', function() {
    const clienteId = this.value;
    const veiculoSelect = document.querySelector('select[name="id_veiculo"]');
    
    if (clienteId) {
        fetch(`api/veiculos.php?acao=buscar_por_cliente&id_cliente=${clienteId}`)
            .then(response => response.json())
            .then(data => {
                veiculoSelect.innerHTML = '<option value="">Selecione um veículo</option>';
                data.forEach(veiculo => {
                    veiculoSelect.innerHTML += `<option value="${veiculo.id_veiculo}">${veiculo.marca} ${veiculo.modelo} - ${veiculo.placa}</option>`;
                });
            });
    } else {
        veiculoSelect.innerHTML = '<option value="">Selecione um veículo</option>';
    }
});

// Função para alterar status
function alterarStatus(idOs, statusAtual) {
    const modal = new bootstrap.Modal(document.getElementById('modalStatus'));
    const select = document.querySelector('select[name="novo_status"]');
    select.value = statusAtual;
    modal.show();
}

// Função para mostrar/ocultar filtros
function toggleFiltros() {
    const filtrosCard = document.getElementById('filtrosCard');
    if (filtrosCard.style.display === 'none') {
        filtrosCard.style.display = 'block';
    } else {
        filtrosCard.style.display = 'none';
    }
}

// Mostrar filtros se houver algum ativo
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') || urlParams.get('cliente') || urlParams.get('data')) {
        document.getElementById('filtrosCard').style.display = 'block';
    }
});


</script>

<?php include 'includes/footer.php'; ?> 