<?php
/**
 * Módulo de Agendamentos
 * LJ-OS Sistema para Lava Jato
 */

require_once 'includes/header.php';

$acao = $_GET['acao'] ?? 'listar';
$erro = '';
$sucesso = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $acao = $_POST['acao'] ?? 'salvar';
    
    if ($acao === 'salvar') {
        $id_agendamento = $_POST['id_agendamento'] ?? '';
        $id_cliente = sanitizar($_POST['id_cliente']);
        $id_veiculo = sanitizar($_POST['id_veiculo']);
        $id_servico = sanitizar($_POST['id_servico']);
        $data_agendamento = sanitizar($_POST['data_agendamento']);
        $hora_agendamento = sanitizar($_POST['hora_agendamento']);
        $vaga = (int)$_POST['vaga'];
        $valor_estimado = (float)$_POST['valor_estimado'];
        $observacoes = sanitizar($_POST['observacoes']);
        
        try {
            $db = getDB();
            
            // Validar dados
            if (empty($id_cliente)) throw new Exception('Cliente é obrigatório');
            if (empty($id_veiculo)) throw new Exception('Veículo é obrigatório');
            if (empty($id_servico)) throw new Exception('Serviço é obrigatório');
            if (empty($data_agendamento)) throw new Exception('Data é obrigatória');
            if (empty($hora_agendamento)) throw new Exception('Hora é obrigatória');
            
            // Combinar data e hora
            $data_hora = $data_agendamento . ' ' . $hora_agendamento;
            
            // Verificar se a data/hora já está ocupada
            $stmt = $db->prepare("SELECT id_agendamento FROM agendamentos WHERE data_agendamento = ? AND status != 'cancelado' AND id_agendamento != ?");
            $stmt->execute([$data_hora, $id_agendamento ?: 0]);
            if ($stmt->rowCount() > 0) {
                throw new Exception('Já existe um agendamento para esta data e hora');
            }
            
            if (empty($id_agendamento)) {
                // Inserir novo agendamento
                $stmt = $db->prepare("INSERT INTO agendamentos (id_cliente, id_veiculo, id_servico, data_agendamento, vaga, valor_estimado, observacoes, usuario_cadastro) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_cliente, $id_veiculo, $id_servico, $data_hora, $vaga, $valor_estimado, $observacoes, $_SESSION['usuario_id']]);
                $sucesso = 'Agendamento realizado com sucesso!';
            } else {
                // Atualizar agendamento existente
                $stmt = $db->prepare("UPDATE agendamentos SET id_cliente = ?, id_veiculo = ?, id_servico = ?, data_agendamento = ?, vaga = ?, valor_estimado = ?, observacoes = ? WHERE id_agendamento = ?");
                $stmt->execute([$id_cliente, $id_veiculo, $id_servico, $data_hora, $vaga, $valor_estimado, $observacoes, $id_agendamento]);
                $sucesso = 'Agendamento atualizado com sucesso!';
            }
            
            $acao = 'listar';
            
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }
}

// Buscar dados para formulário
$agendamento = null;
$clientes = [];
$veiculos = [];
$servicos = [];

if ($acao === 'editar' || $acao === 'visualizar') {
    $id_agendamento = (int)$_GET['id'];
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT a.*, c.nome as nome_cliente, v.placa, v.marca, v.modelo, s.nome_servico, s.preco
            FROM agendamentos a
            LEFT JOIN clientes c ON a.id_cliente = c.id_cliente
            LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
            LEFT JOIN servicos s ON a.id_servico = s.id_servico
            WHERE a.id_agendamento = ?
        ");
        $stmt->execute([$id_agendamento]);
        $agendamento = $stmt->fetch();
        
        if (!$agendamento) {
            $erro = 'Agendamento não encontrado';
            $acao = 'listar';
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
        $acao = 'listar';
    }
}

// Buscar dados para selects
try {
    $db = getDB();
    
    // Clientes
    $stmt = $db->prepare("SELECT id_cliente, nome, cpf_cnpj FROM clientes WHERE status = 'ativo' ORDER BY nome");
    $stmt->execute();
    $clientes = $stmt->fetchAll();
    
    // Serviços
    $stmt = $db->prepare("SELECT id_servico, nome_servico, preco, duracao_estimada FROM servicos WHERE status = 'ativo' ORDER BY nome_servico");
    $stmt->execute();
    $servicos = $stmt->fetchAll();
    
} catch (Exception $e) {
    $erro = $e->getMessage();
}

// Buscar agendamentos para listagem
$agendamentos = [];
$filtro_data = $_GET['data'] ?? '';
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_status = $_GET['status'] ?? '';

if ($acao === 'listar') {
    try {
        $db = getDB();
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($filtro_data)) {
            $where .= " AND DATE(a.data_agendamento) = ?";
            $params[] = $filtro_data;
        }
        
        if (!empty($filtro_cliente)) {
            $where .= " AND c.nome LIKE ?";
            $params[] = "%$filtro_cliente%";
        }
        
        if (!empty($filtro_status)) {
            $where .= " AND a.status = ?";
            $params[] = $filtro_status;
        }
        
        $sql = "
            SELECT a.*, c.nome as nome_cliente, c.telefone, v.placa, v.marca, v.modelo, s.nome_servico, s.preco
            FROM agendamentos a
            LEFT JOIN clientes c ON a.id_cliente = c.id_cliente
            LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
            LEFT JOIN servicos s ON a.id_servico = s.id_servico
            $where
            ORDER BY a.data_agendamento ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $agendamentos = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>

<h1 class="page-title">
    <i class="fas fa-calendar-alt"></i>
    <?php echo $acao === 'listar' ? 'Agendamentos' : ($acao === 'novo' ? 'Novo Agendamento' : ($acao === 'editar' ? 'Editar Agendamento' : 'Visualizar Agendamento')); ?>
</h1>

<?php if ($erro): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo $erro; ?>
    </div>
<?php endif; ?>

<?php if ($sucesso): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $sucesso; ?>
    </div>
<?php endif; ?>

<?php if ($acao === 'listar'): ?>
    <!-- Listagem de Agendamentos -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list"></i>
                    Lista de Agendamentos
                </h3>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="?acao=novo" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <span class="d-none d-sm-inline">Novo Agendamento</span>
                    </a>
                    <a href="?acao=calendario" class="btn btn-info">
                        <i class="fas fa-calendar"></i>
                        <span class="d-none d-sm-inline">Ver Calendário</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" class="mb-4">
                <input type="hidden" name="acao" value="listar">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Data</label>
                        <input type="date" name="data" class="form-control" value="<?php echo htmlspecialchars($filtro_data); ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Cliente</label>
                        <input type="text" name="cliente" class="form-control" value="<?php echo htmlspecialchars($filtro_cliente); ?>" placeholder="Nome do cliente">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="pendente" <?php echo $filtro_status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="confirmado" <?php echo $filtro_status === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                            <option value="em_andamento" <?php echo $filtro_status === 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                            <option value="concluido" <?php echo $filtro_status === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                            <option value="cancelado" <?php echo $filtro_status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-search"></i>
                            <span class="d-none d-sm-inline">Filtrar</span>
                        </button>
                        <a href="?acao=listar" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                            <span class="d-none d-sm-inline">Limpar</span>
                        </a>
                    </div>
                </div>
            </form>
            
            <!-- Tabela Responsiva -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th class="d-none d-md-table-cell">Cliente</th>
                            <th class="d-none d-lg-table-cell">Veículo</th>
                            <th class="d-none d-lg-table-cell">Serviço</th>
                            <th class="d-none d-md-table-cell">Vaga</th>
                            <th class="d-none d-xl-table-cell">Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($agendamentos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar fa-2x mb-2"></i>
                                    <br>
                                    Nenhum agendamento encontrado
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($agendamentos as $a): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo formatarDataHora($a['data_agendamento']); ?></strong>
                                        <div class="d-md-none small text-muted">
                                            <?php echo htmlspecialchars($a['nome_cliente']); ?>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo htmlspecialchars($a['nome_cliente']); ?>
                                        <div class="small text-muted">
                                            <?php echo formatarTelefone($a['telefone']); ?>
                                        </div>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <?php echo htmlspecialchars($a['placa'] . ' - ' . $a['marca'] . ' ' . $a['modelo']); ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <?php echo htmlspecialchars($a['nome_servico']); ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php if ($a['vaga']): ?>
                                            <span class="badge bg-primary">Vaga <?php echo $a['vaga']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <?php echo formatarMoeda($a['valor_estimado']); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'pendente' => 'bg-warning',
                                            'confirmado' => 'bg-info',
                                            'em_andamento' => 'bg-primary',
                                            'concluido' => 'bg-success',
                                            'cancelado' => 'bg-danger'
                                        ];
                                        $status_text = [
                                            'pendente' => 'Pendente',
                                            'confirmado' => 'Confirmado',
                                            'em_andamento' => 'Em Andamento',
                                            'concluido' => 'Concluído',
                                            'cancelado' => 'Cancelado'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $status_class[$a['status']] ?? 'bg-secondary'; ?>">
                                            <?php echo $status_text[$a['status']] ?? ucfirst($a['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?acao=visualizar&id=<?php echo $a['id_agendamento']; ?>" class="btn btn-outline-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?acao=editar&id=<?php echo $a['id_agendamento']; ?>" class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-success" onclick="alterarStatus(<?php echo $a['id_agendamento']; ?>, 'confirmado')" title="Confirmar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="alterarStatus(<?php echo $a['id_agendamento']; ?>, 'cancelado')" title="Cancelar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Formulário de Agendamento -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-<?php echo $acao === 'novo' ? 'plus' : 'edit'; ?>"></i>
                    <?php echo $acao === 'novo' ? 'Novo Agendamento' : ($acao === 'editar' ? 'Editar Agendamento' : 'Visualizar Agendamento'); ?>
                </h3>
                <a href="?acao=listar" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span class="d-none d-sm-inline">Voltar</span>
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="acao" value="salvar">
                <?php if ($agendamento): ?>
                    <input type="hidden" name="id_agendamento" value="<?php echo $agendamento['id_agendamento']; ?>">
                <?php endif; ?>
                
                <div class="row g-3">
                    <!-- Cliente -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Cliente *</label>
                        <select name="id_cliente" id="id_cliente" class="form-select" required <?php echo $acao === 'visualizar' ? 'disabled' : ''; ?>>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id_cliente']; ?>" 
                                    <?php echo ($agendamento && $agendamento['id_cliente'] == $cliente['id_cliente']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cliente['nome'] . ' - ' . formatarCPFCNPJ($cliente['cpf_cnpj'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecione um cliente</div>
                    </div>
                    
                    <!-- Veículo -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Veículo *</label>
                        <select name="id_veiculo" id="id_veiculo" class="form-select" required <?php echo $acao === 'visualizar' ? 'disabled' : ''; ?>>
                            <option value="">Selecione um veículo</option>
                            <?php if ($agendamento): ?>
                                <option value="<?php echo $agendamento['id_veiculo']; ?>" selected>
                                    <?php echo htmlspecialchars($agendamento['placa'] . ' - ' . $agendamento['marca'] . ' ' . $agendamento['modelo']); ?>
                                </option>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Selecione um veículo</div>
                    </div>
                    
                    <!-- Serviço -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Serviço *</label>
                        <select name="id_servico" id="id_servico" class="form-select" required <?php echo $acao === 'visualizar' ? 'disabled' : ''; ?>>
                            <option value="">Selecione um serviço</option>
                            <?php foreach ($servicos as $servico): ?>
                                <option value="<?php echo $servico['id_servico']; ?>" 
                                    data-preco="<?php echo $servico['preco']; ?>"
                                    <?php echo ($agendamento && $agendamento['id_servico'] == $servico['id_servico']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($servico['nome_servico'] . ' - ' . formatarMoeda($servico['preco'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecione um serviço</div>
                    </div>
                    
                    <!-- Data -->
                    <div class="col-12 col-md-3">
                        <label class="form-label">Data *</label>
                        <input type="date" name="data_agendamento" id="data_agendamento" class="form-control" 
                               value="<?php echo $agendamento ? date('Y-m-d', strtotime($agendamento['data_agendamento'])) : date('Y-m-d'); ?>" 
                               min="<?php echo date('Y-m-d'); ?>" required 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="invalid-feedback">Selecione uma data</div>
                    </div>
                    
                    <!-- Hora -->
                    <div class="col-12 col-md-3">
                        <label class="form-label">Hora *</label>
                        <input type="time" name="hora_agendamento" id="hora_agendamento" class="form-control" 
                               value="<?php echo $agendamento ? date('H:i', strtotime($agendamento['data_agendamento'])) : '09:00'; ?>" 
                               required 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="invalid-feedback">Selecione uma hora</div>
                    </div>
                    
                    <!-- Vaga -->
                    <div class="col-12 col-md-3">
                        <label class="form-label">Vaga</label>
                        <select name="vaga" class="form-select" <?php echo $acao === 'visualizar' ? 'disabled' : ''; ?>>
                            <option value="">Sem vaga específica</option>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($agendamento && $agendamento['vaga'] == $i) ? 'selected' : ''; ?>>
                                    Vaga <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <!-- Valor Estimado -->
                    <div class="col-12 col-md-3">
                        <label class="form-label">Valor Estimado</label>
                        <input type="number" name="valor_estimado" id="valor_estimado" class="form-control" 
                               value="<?php echo $agendamento ? $agendamento['valor_estimado'] : ''; ?>" 
                               step="0.01" min="0" 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="form-text">Será preenchido automaticamente</div>
                    </div>
                    
                    <!-- Observações -->
                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3" 
                                  placeholder="Observações sobre o agendamento..." 
                                  <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>><?php echo $agendamento ? htmlspecialchars($agendamento['observacoes']) : ''; ?></textarea>
                    </div>
                </div>
                
                <!-- Botões -->
                <?php if ($acao !== 'visualizar'): ?>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $agendamento ? 'Atualizar' : 'Agendar'; ?>
                        </button>
                        <a href="?acao=listar" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
// Validação do formulário
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Carregar veículos quando cliente for selecionado
document.addEventListener('DOMContentLoaded', function() {
    const clienteSelect = document.getElementById('id_cliente');
    const veiculoSelect = document.getElementById('id_veiculo');
    const servicoSelect = document.getElementById('id_servico');
    const valorInput = document.getElementById('valor_estimado');
    
    if (clienteSelect) {
        clienteSelect.addEventListener('change', function() {
            const clienteId = this.value;
            veiculoSelect.innerHTML = '<option value="">Selecione um veículo</option>';
            
            if (clienteId) {
                fetch(`api/veiculos.php?acao=buscar_por_cliente&cliente_id=${clienteId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            data.veiculos.forEach(veiculo => {
                                const option = document.createElement('option');
                                option.value = veiculo.id_veiculo;
                                option.textContent = `${veiculo.placa} - ${veiculo.marca} ${veiculo.modelo}`;
                                veiculoSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao carregar veículos:', error);
                    });
            }
        });
    }
    
    // Atualizar valor quando serviço for selecionado
    if (servicoSelect && valorInput) {
        servicoSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const preco = selectedOption.getAttribute('data-preco');
            if (preco) {
                valorInput.value = preco;
            }
        });
    }
});

// Função para alterar status do agendamento
function alterarStatus(id, status) {
    const statusText = {
        'confirmado': 'confirmar',
        'cancelado': 'cancelar'
    };
    
    if (confirm(`Tem certeza que deseja ${statusText[status]} este agendamento?`)) {
        fetch('api/agendamentos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': document.querySelector('input[name="_token"]').value // Incluir token CSRF
            },
            body: `acao=alterar_status&id=${id}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                LavaJato.showAlert(`Agendamento ${statusText[status]} com sucesso!`, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                LavaJato.showAlert(data.erro || 'Erro ao alterar status', 'danger');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            LavaJato.showAlert('Erro ao processar requisição', 'danger');
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?> 