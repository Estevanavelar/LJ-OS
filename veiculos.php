<?php
/**
 * Módulo de Veículos
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
        $id_veiculo = $_POST['id_veiculo'] ?? '';
        $id_cliente = sanitizar($_POST['id_cliente']);
        $placa = strtoupper(sanitizar($_POST['placa']));
        $marca = sanitizar($_POST['marca']);
        $modelo = sanitizar($_POST['modelo']);
        $ano = (int)$_POST['ano'];
        $ano_modelo = (int)$_POST['ano_modelo'];
        $cor = sanitizar($_POST['cor']);
        $combustivel = sanitizar($_POST['combustivel']);
        $km_atual = (int)$_POST['km_atual'];
        $observacoes = sanitizar($_POST['observacoes']);
        
        try {
            $db = getDB();
            
            // Validar dados
            if (empty($id_cliente)) throw new Exception('Cliente é obrigatório');
            if (empty($placa)) throw new Exception('Placa é obrigatória');
            if (empty($marca)) throw new Exception('Marca é obrigatória');
            if (empty($modelo)) throw new Exception('Modelo é obrigatório');
            if ($ano < 1900 || $ano > date('Y') + 1) throw new Exception('Ano inválido');
            
            // Verificar se placa já existe (exceto para edição)
            if (empty($id_veiculo)) {
                $stmt = $db->prepare("SELECT id_veiculo FROM veiculos WHERE placa = ?");
                $stmt->execute([$placa]);
                if ($stmt->rowCount() > 0) {
                    throw new Exception('Placa já cadastrada');
                }
            }
            
            if (empty($id_veiculo)) {
                // Inserir novo veículo
                $stmt = $db->prepare("INSERT INTO veiculos (id_cliente, placa, marca, modelo, ano, ano_modelo, cor, combustivel, km_atual, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_cliente, $placa, $marca, $modelo, $ano, $ano_modelo, $cor, $combustivel, $km_atual, $observacoes]);
                $sucesso = 'Veículo cadastrado com sucesso!';
            } else {
                // Atualizar veículo existente
                $stmt = $db->prepare("UPDATE veiculos SET id_cliente = ?, placa = ?, marca = ?, modelo = ?, ano = ?, ano_modelo = ?, cor = ?, combustivel = ?, km_atual = ?, observacoes = ? WHERE id_veiculo = ?");
                $stmt->execute([$id_cliente, $placa, $marca, $modelo, $ano, $ano_modelo, $cor, $combustivel, $km_atual, $observacoes, $id_veiculo]);
                $sucesso = 'Veículo atualizado com sucesso!';
            }
            
            $acao = 'listar';
            
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }
}

// Buscar dados para formulário
$veiculo = null;
$clientes = [];
if ($acao === 'editar' || $acao === 'visualizar') {
    $id_veiculo = (int)$_GET['id'];
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM veiculos WHERE id_veiculo = ?");
        $stmt->execute([$id_veiculo]);
        $veiculo = $stmt->fetch();
        
        if (!$veiculo) {
            $erro = 'Veículo não encontrado';
            $acao = 'listar';
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
        $acao = 'listar';
    }
}

// Buscar lista de clientes
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id_cliente, nome, cpf_cnpj FROM clientes WHERE status = 'ativo' ORDER BY nome");
    $stmt->execute();
    $clientes = $stmt->fetchAll();
} catch (Exception $e) {
    $erro = $e->getMessage();
}

// Buscar veículos para listagem
$veiculos = [];
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_placa = $_GET['placa'] ?? '';

if ($acao === 'listar') {
    try {
        $db = getDB();
        $where = "WHERE v.status = 'ativo'";
        $params = [];
        
        if (!empty($filtro_cliente)) {
            $where .= " AND c.nome LIKE ?";
            $params[] = "%$filtro_cliente%";
        }
        
        if (!empty($filtro_placa)) {
            $where .= " AND v.placa LIKE ?";
            $params[] = "%$filtro_placa%";
        }
        
        $sql = "SELECT v.*, c.nome as nome_cliente, c.cpf_cnpj 
                FROM veiculos v 
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente 
                $where 
                ORDER BY v.data_cadastro DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $veiculos = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>

<h1 class="page-title">
    <i class="fas fa-car"></i>
    <?php echo $acao === 'listar' ? 'Veículos' : ($acao === 'novo' ? 'Novo Veículo' : ($acao === 'editar' ? 'Editar Veículo' : 'Visualizar Veículo')); ?>
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
    <!-- Listagem de Veículos -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list"></i>
                    Lista de Veículos
                </h3>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="?acao=novo" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <span class="d-none d-sm-inline">Novo Veículo</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" class="mb-4">
                <input type="hidden" name="acao" value="listar">
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Cliente</label>
                        <input type="text" name="cliente" class="form-control" value="<?php echo htmlspecialchars($filtro_cliente); ?>" placeholder="Nome do cliente">
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Placa</label>
                        <input type="text" name="placa" class="form-control" value="<?php echo htmlspecialchars($filtro_placa); ?>" placeholder="Placa do veículo">
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-secondary flex-fill">
                                <i class="fas fa-search"></i>
                                <span class="d-none d-sm-inline">Filtrar</span>
                            </button>
                            <a href="?acao=listar" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                                <span class="d-none d-sm-inline">Limpar</span>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Tabela Responsiva -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th class="d-none d-md-table-cell">Cliente</th>
                            <th class="d-none d-lg-table-cell">Marca/Modelo</th>
                            <th class="d-none d-lg-table-cell">Ano</th>
                            <th class="d-none d-md-table-cell">Cor</th>
                            <th class="d-none d-xl-table-cell">KM</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($veiculos)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-car fa-2x mb-2"></i>
                                    <br>
                                    Nenhum veículo encontrado
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($veiculos as $v): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($v['placa']); ?></strong>
                                        <div class="d-md-none small text-muted">
                                            <?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo']); ?>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo htmlspecialchars($v['nome_cliente']); ?>
                                        <div class="small text-muted">
                                            <?php echo formatarCPFCNPJ($v['cpf_cnpj']); ?>
                                        </div>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo']); ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <?php echo $v['ano']; ?>
                                        <?php if ($v['ano_modelo'] && $v['ano_modelo'] != $v['ano']): ?>
                                            <span class="text-muted">/<?php echo $v['ano_modelo']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($v['cor']); ?></span>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <?php echo number_format($v['km_atual'], 0, ',', '.'); ?> km
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?acao=visualizar&id=<?php echo $v['id_veiculo']; ?>" class="btn btn-outline-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?acao=editar&id=<?php echo $v['id_veiculo']; ?>" class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" onclick="toggleStatus(<?php echo $v['id_veiculo']; ?>)" title="Inativar">
                                                <i class="fas fa-trash"></i>
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
    <!-- Formulário de Veículo -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-<?php echo $acao === 'novo' ? 'plus' : 'edit'; ?>"></i>
                    <?php echo $acao === 'novo' ? 'Novo Veículo' : ($acao === 'editar' ? 'Editar Veículo' : 'Visualizar Veículo'); ?>
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
                <?php if ($veiculo): ?>
                    <input type="hidden" name="id_veiculo" value="<?php echo $veiculo['id_veiculo']; ?>">
                <?php endif; ?>
                
                <div class="row g-3">
                    <!-- Cliente -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Cliente *</label>
                        <select name="id_cliente" class="form-select" required <?php echo $acao === 'visualizar' ? 'disabled' : ''; ?>>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id_cliente']; ?>" 
                                    <?php echo ($veiculo && $veiculo['id_cliente'] == $cliente['id_cliente']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cliente['nome'] . ' - ' . formatarCPFCNPJ($cliente['cpf_cnpj'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecione um cliente</div>
                    </div>
                    
                    <!-- Placa -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Placa *</label>
                        <input type="text" name="placa" class="form-control" 
                               value="<?php echo $veiculo ? htmlspecialchars($veiculo['placa']) : ''; ?>" 
                               placeholder="ABC-1234" required 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="invalid-feedback">Informe a placa do veículo</div>
                    </div>
                    
                    <!-- Marca -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Marca *</label>
                        <input type="text" name="marca" class="form-control" 
                               value="<?php echo $veiculo ? htmlspecialchars($veiculo['marca']) : ''; ?>" 
                               placeholder="Ex: Toyota" required 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="invalid-feedback">Informe a marca do veículo</div>
                    </div>
                    
                    <!-- Modelo -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Modelo *</label>
                        <input type="text" name="modelo" class="form-control" 
                               value="<?php echo $veiculo ? htmlspecialchars($veiculo['modelo']) : ''; ?>" 
                               placeholder="Ex: Corolla" required 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="invalid-feedback">Informe o modelo do veículo</div>
                    </div>
                    
                    <!-- Ano -->
                    <div class="col-12 col-md-4">
                        <label class="form-label">Ano *</label>
                        <input type="number" name="ano" class="form-control" 
                               value="<?php echo $veiculo ? $veiculo['ano'] : date('Y'); ?>" 
                               min="1900" max="<?php echo date('Y') + 1; ?>" required 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="invalid-feedback">Informe o ano do veículo</div>
                    </div>
                    
                    <!-- Ano Modelo -->
                    <div class="col-12 col-md-4">
                        <label class="form-label">Ano Modelo</label>
                        <input type="number" name="ano_modelo" class="form-control" 
                               value="<?php echo $veiculo ? $veiculo['ano_modelo'] : ''; ?>" 
                               min="1900" max="<?php echo date('Y') + 1; ?>" 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="form-text">Deixe em branco se for igual ao ano</div>
                    </div>
                    
                    <!-- Cor -->
                    <div class="col-12 col-md-4">
                        <label class="form-label">Cor *</label>
                        <input type="text" name="cor" class="form-control" 
                               value="<?php echo $veiculo ? htmlspecialchars($veiculo['cor']) : ''; ?>" 
                               placeholder="Ex: Prata" required 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="invalid-feedback">Informe a cor do veículo</div>
                    </div>
                    
                    <!-- Combustível -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Combustível</label>
                        <select name="combustivel" class="form-select" <?php echo $acao === 'visualizar' ? 'disabled' : ''; ?>>
                            <option value="">Selecione</option>
                            <option value="gasolina" <?php echo ($veiculo && $veiculo['combustivel'] === 'gasolina') ? 'selected' : ''; ?>>Gasolina</option>
                            <option value="etanol" <?php echo ($veiculo && $veiculo['combustivel'] === 'etanol') ? 'selected' : ''; ?>>Etanol</option>
                            <option value="diesel" <?php echo ($veiculo && $veiculo['combustivel'] === 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                            <option value="flex" <?php echo ($veiculo && $veiculo['combustivel'] === 'flex') ? 'selected' : ''; ?>>Flex</option>
                            <option value="gnv" <?php echo ($veiculo && $veiculo['combustivel'] === 'gnv') ? 'selected' : ''; ?>>GNV</option>
                            <option value="eletrico" <?php echo ($veiculo && $veiculo['combustivel'] === 'eletrico') ? 'selected' : ''; ?>>Elétrico</option>
                            <option value="hibrido" <?php echo ($veiculo && $veiculo['combustivel'] === 'hibrido') ? 'selected' : ''; ?>>Híbrido</option>
                        </select>
                    </div>
                    
                    <!-- KM Atual -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Quilometragem Atual</label>
                        <input type="number" name="km_atual" class="form-control" 
                               value="<?php echo $veiculo ? $veiculo['km_atual'] : ''; ?>" 
                               min="0" step="1" 
                               <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>>
                        <div class="form-text">Quilometragem atual do veículo</div>
                    </div>
                    
                    <!-- Observações -->
                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3" 
                                  placeholder="Observações sobre o veículo..." 
                                  <?php echo $acao === 'visualizar' ? 'readonly' : ''; ?>><?php echo $veiculo ? htmlspecialchars($veiculo['observacoes']) : ''; ?></textarea>
                    </div>
                </div>
                
                <!-- Botões -->
                <?php if ($acao !== 'visualizar'): ?>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $veiculo ? 'Atualizar' : 'Cadastrar'; ?>
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

// Função para inativar veículo
function toggleStatus(id) {
    if (confirm('Tem certeza que deseja inativar este veículo?')) {
        fetch('api/veiculos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'acao=toggle_status&id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                LavaJato.showAlert('Veículo inativado com sucesso!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                LavaJato.showAlert(data.erro || 'Erro ao inativar veículo', 'danger');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            LavaJato.showAlert('Erro ao processar requisição', 'danger');
        });
    }
}

// Máscara para placa
document.addEventListener('DOMContentLoaded', function() {
    const placaInput = document.querySelector('input[name="placa"]');
    if (placaInput) {
        placaInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '').toUpperCase();
            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3, 7);
            }
            e.target.value = value;
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 