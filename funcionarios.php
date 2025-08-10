<?php
/**
 * Sistema de Funcionários
 * LJ-OS Sistema para Lava Jato
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar login
verificarLogin();

// Verificar permissões ANTES de incluir o header
$pdo = getDB();

if (!verificarPermissao('funcionarios')) {
    header('Location: dashboard.php');
    exit;
}

require_once 'includes/header.php';

// Buscar configurações
$stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'nome_empresa' LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);
$nome_empresa = $config['valor'] ?? 'LJ-OS Sistema para Lava Jato';
?>

<div class="container-fluid">
    <!-- Header da página -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users-cog"></i>
            Gestão de Funcionários
        </h1>
        <div>
            <button type="button" class="btn btn-success" onclick="registrarPresenca()">
                <i class="fas fa-clock"></i> Registrar Presença
            </button>
            <button type="button" class="btn btn-primary" onclick="abrirModalFuncionario()">
                <i class="fas fa-plus"></i> Novo Funcionário
            </button>
        </div>
    </div>

    <!-- Cards de estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Funcionários Ativos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="funcionarios-ativos">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Presentes Hoje
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="presentes-hoje">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Carros Atendidos (Hoje)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="carros-atendidos">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Vendas (Mês)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="vendas-mes">R$ 0,00</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Abas de navegação -->
    <ul class="nav nav-tabs" id="funcionariosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="funcionarios-tab" data-bs-toggle="tab" data-bs-target="#funcionarios" type="button" role="tab">
                <i class="fas fa-users"></i> Funcionários
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="presenca-tab" data-bs-toggle="tab" data-bs-target="#presenca" type="button" role="tab">
                <i class="fas fa-clock"></i> Controle de Presença
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="produtividade-tab" data-bs-toggle="tab" data-bs-target="#produtividade" type="button" role="tab">
                <i class="fas fa-chart-line"></i> Produtividade
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="vendas-tab" data-bs-toggle="tab" data-bs-target="#vendas" type="button" role="tab">
                <i class="fas fa-shopping-cart"></i> Vendas por Funcionário
            </button>
        </li>
    </ul>

    <!-- Conteúdo das abas -->
    <div class="tab-content" id="funcionariosTabsContent">
        <!-- Aba Funcionários -->
        <div class="tab-pane fade show active" id="funcionarios" role="tabpanel">
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lista de Funcionários</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabela-funcionarios">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cargo</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Data Admissão</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-funcionarios">
                                <!-- Dados carregados via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aba Controle de Presença -->
        <div class="tab-pane fade" id="presenca" role="tabpanel">
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Controle de Presença</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="filtro_data_presenca" class="form-label">Data</label>
                            <input type="date" class="form-control" id="filtro_data_presenca" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary d-block" onclick="carregarPresenca()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabela-presenca">
                            <thead>
                                <tr>
                                    <th>Funcionário</th>
                                    <th>Entrada</th>
                                    <th>Saída</th>
                                    <th>Total Horas</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-presenca">
                                <!-- Dados carregados via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aba Produtividade -->
        <div class="tab-pane fade" id="produtividade" role="tabpanel">
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Produtividade</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="filtro_funcionario_prod" class="form-label">Funcionário</label>
                            <select class="form-select" id="filtro_funcionario_prod">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_data_inicio_prod" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="filtro_data_inicio_prod">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_data_fim_prod" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="filtro_data_fim_prod">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary d-block" onclick="carregarProdutividade()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabela-produtividade">
                            <thead>
                                <tr>
                                    <th>Funcionário</th>
                                    <th>Carros Atendidos</th>
                                    <th>Horas Trabalhadas</th>
                                    <th>Eficiência</th>
                                    <th>Valor Gerado</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-produtividade">
                                <!-- Dados carregados via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aba Vendas -->
        <div class="tab-pane fade" id="vendas" role="tabpanel">
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Vendas por Funcionário</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="filtro_funcionario_vendas" class="form-label">Funcionário</label>
                            <select class="form-select" id="filtro_funcionario_vendas">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_data_inicio_vendas" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="filtro_data_inicio_vendas">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_data_fim_vendas" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="filtro_data_fim_vendas">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary d-block" onclick="carregarVendas()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabela-vendas">
                            <thead>
                                <tr>
                                    <th>Funcionário</th>
                                    <th>Produtos Vendidos</th>
                                    <th>Valor Total</th>
                                    <th>Comissão</th>
                                    <th>Data</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-vendas">
                                <!-- Dados carregados via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Funcionário -->
<div class="modal fade" id="modalFuncionario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFuncionarioTitle">Novo Funcionário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formFuncionario">
                <div class="modal-body">
                    <input type="hidden" id="funcionario_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="funcionario_nome" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="funcionario_nome" name="nome" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="funcionario_cargo" class="form-label">Cargo *</label>
                                <select class="form-select" id="funcionario_cargo" name="cargo" required>
                                    <option value="">Selecione...</option>
                                    <option value="lavador">Lavador</option>
                                    <option value="atendente">Atendente</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="gerente">Gerente</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="funcionario_cpf" class="form-label">CPF *</label>
                                <input type="text" class="form-control" id="funcionario_cpf" name="cpf" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="funcionario_telefone" class="form-label">Telefone *</label>
                                <input type="text" class="form-control" id="funcionario_telefone" name="telefone" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="funcionario_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="funcionario_email" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="funcionario_data_admissao" class="form-label">Data de Admissão *</label>
                                <input type="date" class="form-control" id="funcionario_data_admissao" name="data_admissao" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="funcionario_salario" class="form-label">Salário Base *</label>
                                <input type="number" class="form-control" id="funcionario_salario" name="salario" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="funcionario_comissao" class="form-label">Comissão (%)</label>
                                <input type="number" class="form-control" id="funcionario_comissao" name="comissao" step="0.01" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="funcionario_endereco" class="form-label">Endereço</label>
                        <textarea class="form-control" id="funcionario_endereco" name="endereco" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="funcionario_observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="funcionario_observacoes" name="observacoes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Funcionário</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Registrar Presença -->
<div class="modal fade" id="modalPresenca" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Presença</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPresenca">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="presenca_funcionario" class="form-label">Funcionário *</label>
                        <select class="form-select" id="presenca_funcionario" name="id_funcionario" required>
                            <option value="">Selecione um funcionário</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="presenca_data" class="form-label">Data *</label>
                        <input type="date" class="form-control" id="presenca_data" name="data" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="presenca_tipo" class="form-label">Tipo *</label>
                        <select class="form-select" id="presenca_tipo" name="tipo" required>
                            <option value="entrada">Entrada</option>
                            <option value="saida">Saída</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="presenca_hora" class="form-label">Hora *</label>
                        <input type="time" class="form-control" id="presenca_hora" name="hora" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Carregar dados iniciais
document.addEventListener('DOMContentLoaded', function() {
    carregarEstatisticas();
    carregarFuncionarios();
    carregarPresenca();
    carregarProdutividade();
    carregarVendas();
    
    // Definir datas padrão
    const hoje = new Date();
    const umMesAtras = new Date();
    umMesAtras.setMonth(umMesAtras.getMonth() - 1);
    
    document.getElementById('filtro_data_inicio_prod').value = umMesAtras.toISOString().split('T')[0];
    document.getElementById('filtro_data_fim_prod').value = hoje.toISOString().split('T')[0];
    document.getElementById('filtro_data_inicio_vendas').value = umMesAtras.toISOString().split('T')[0];
    document.getElementById('filtro_data_fim_vendas').value = hoje.toISOString().split('T')[0];
    document.getElementById('presenca_hora').value = hoje.toTimeString().slice(0, 5);
});

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const response = await fetch('api/funcionarios.php?action=estatisticas');
        const data = await response.json();
        
        if (data.sucesso) {
            document.getElementById('funcionarios-ativos').textContent = data.estatisticas.ativos;
            document.getElementById('presentes-hoje').textContent = data.estatisticas.presentes_hoje;
            document.getElementById('carros-atendidos').textContent = data.estatisticas.carros_atendidos;
            document.getElementById('vendas-mes').textContent = `R$ ${parseFloat(data.estatisticas.vendas_mes).toFixed(2).replace('.', ',')}`;
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Carregar funcionários
async function carregarFuncionarios() {
    try {
        const response = await fetch('api/funcionarios.php?action=listar');
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarFuncionarios(data.funcionarios);
            carregarSelectsFuncionarios(data.funcionarios);
        } else {
            mostrarAlerta('Erro ao carregar funcionários: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar funcionários:', error);
        mostrarAlerta('Erro ao carregar funcionários', 'danger');
    }
}

// Renderizar funcionários
function renderizarFuncionarios(funcionarios) {
    const tbody = document.getElementById('tbody-funcionarios');
    tbody.innerHTML = '';
    
    funcionarios.forEach(funcionario => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${funcionario.nome}</td>
            <td>${getCargoText(funcionario.cargo)}</td>
            <td>${funcionario.telefone}</td>
            <td>${funcionario.email || '-'}</td>
            <td>${getStatusBadge(funcionario.status)}</td>
            <td>${formatarData(funcionario.data_admissao)}</td>
            <td>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="visualizarFuncionario(${funcionario.id_funcionario})" title="Visualizar">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="editarFuncionario(${funcionario.id_funcionario})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="excluirFuncionario(${funcionario.id_funcionario})" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Carregar selects de funcionários
function carregarSelectsFuncionarios(funcionarios) {
    const selects = [
        'presenca_funcionario',
        'filtro_funcionario_prod',
        'filtro_funcionario_vendas'
    ];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">Todos</option>';
        
        funcionarios.forEach(funcionario => {
            const option = document.createElement('option');
            option.value = funcionario.id_funcionario;
            option.textContent = funcionario.nome;
            select.appendChild(option);
        });
    });
}

// Carregar presença
async function carregarPresenca() {
    const data = document.getElementById('filtro_data_presenca').value;
    
    try {
        const response = await fetch(`api/funcionarios.php?action=presenca&data=${data}`);
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarPresenca(data.presencas);
        } else {
            mostrarAlerta('Erro ao carregar presença: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar presença:', error);
        mostrarAlerta('Erro ao carregar presença', 'danger');
    }
}

// Renderizar presença
function renderizarPresenca(presencas) {
    const tbody = document.getElementById('tbody-presenca');
    tbody.innerHTML = '';
    
    presencas.forEach(presenca => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${presenca.funcionario_nome}</td>
            <td>${presenca.entrada || '-'}</td>
            <td>${presenca.saida || '-'}</td>
            <td>${presenca.total_horas || '-'}</td>
            <td>${getStatusPresencaBadge(presenca.status)}</td>
            <td>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="editarPresenca(${presenca.id_presenca})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Carregar produtividade
async function carregarProdutividade() {
    const funcionario = document.getElementById('filtro_funcionario_prod').value;
    const dataInicio = document.getElementById('filtro_data_inicio_prod').value;
    const dataFim = document.getElementById('filtro_data_fim_prod').value;
    
    const params = new URLSearchParams();
    if (funcionario) params.append('funcionario', funcionario);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    
    try {
        const response = await fetch(`api/funcionarios.php?action=produtividade&${params.toString()}`);
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarProdutividade(data.produtividade);
        } else {
            mostrarAlerta('Erro ao carregar produtividade: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar produtividade:', error);
        mostrarAlerta('Erro ao carregar produtividade', 'danger');
    }
}

// Renderizar produtividade
function renderizarProdutividade(produtividade) {
    const tbody = document.getElementById('tbody-produtividade');
    tbody.innerHTML = '';
    
    produtividade.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.funcionario_nome}</td>
            <td>${item.carros_atendidos}</td>
            <td>${item.horas_trabalhadas}</td>
            <td>${item.eficiencia}%</td>
            <td>R$ ${parseFloat(item.valor_gerado).toFixed(2).replace('.', ',')}</td>
            <td>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="verDetalhesProdutividade(${item.id_funcionario})" title="Ver Detalhes">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Carregar vendas
async function carregarVendas() {
    const funcionario = document.getElementById('filtro_funcionario_vendas').value;
    const dataInicio = document.getElementById('filtro_data_inicio_vendas').value;
    const dataFim = document.getElementById('filtro_data_fim_vendas').value;
    
    const params = new URLSearchParams();
    if (funcionario) params.append('funcionario', funcionario);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    
    try {
        const response = await fetch(`api/funcionarios.php?action=vendas&${params.toString()}`);
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarVendas(data.vendas);
        } else {
            mostrarAlerta('Erro ao carregar vendas: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar vendas:', error);
        mostrarAlerta('Erro ao carregar vendas', 'danger');
    }
}

// Renderizar vendas
function renderizarVendas(vendas) {
    const tbody = document.getElementById('tbody-vendas');
    tbody.innerHTML = '';
    
    vendas.forEach(venda => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${venda.funcionario_nome}</td>
            <td>${venda.produtos_vendidos}</td>
            <td>R$ ${parseFloat(venda.valor_total).toFixed(2).replace('.', ',')}</td>
            <td>R$ ${parseFloat(venda.comissao).toFixed(2).replace('.', ',')}</td>
            <td>${formatarData(venda.data)}</td>
            <td>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="verDetalhesVenda(${venda.id_venda})" title="Ver Detalhes">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Abrir modal funcionário
function abrirModalFuncionario() {
    document.getElementById('modalFuncionarioTitle').textContent = 'Novo Funcionário';
    document.getElementById('formFuncionario').reset();
    document.getElementById('funcionario_id').value = '';
    document.getElementById('funcionario_data_admissao').value = new Date().toISOString().split('T')[0];
    
    const modal = new bootstrap.Modal(document.getElementById('modalFuncionario'));
    modal.show();
}

// Registrar presença
function registrarPresenca() {
    const modal = new bootstrap.Modal(document.getElementById('modalPresenca'));
    modal.show();
}

// Salvar funcionário
document.getElementById('formFuncionario').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const funcionarioId = document.getElementById('funcionario_id').value;
    
    try {
        const response = await fetch('api/funcionarios.php', {
            method: funcionarioId ? 'PUT' : 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            mostrarAlerta('Funcionário salvo com sucesso!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalFuncionario')).hide();
            carregarFuncionarios();
            carregarEstatisticas();
        } else {
            mostrarAlerta('Erro ao salvar funcionário: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar funcionário:', error);
        mostrarAlerta('Erro ao salvar funcionário', 'danger');
    }
});

// Salvar presença
document.getElementById('formPresenca').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/funcionarios.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            mostrarAlerta('Presença registrada com sucesso!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalPresenca')).hide();
            carregarPresenca();
            carregarEstatisticas();
        } else {
            mostrarAlerta('Erro ao registrar presença: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao registrar presença:', error);
        mostrarAlerta('Erro ao registrar presença', 'danger');
    }
});

// Funções utilitárias
function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR');
}

function getCargoText(cargo) {
    const cargos = {
        'lavador': 'Lavador',
        'atendente': 'Atendente',
        'supervisor': 'Supervisor',
        'gerente': 'Gerente'
    };
    return cargos[cargo] || cargo;
}

function getStatusBadge(status) {
    const badges = {
        'ativo': '<span class="badge bg-success">Ativo</span>',
        'inativo': '<span class="badge bg-danger">Inativo</span>',
        'ferias': '<span class="badge bg-warning">Férias</span>',
        'licenca': '<span class="badge bg-info">Licença</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Desconhecido</span>';
}

function getStatusPresencaBadge(status) {
    const badges = {
        'presente': '<span class="badge bg-success">Presente</span>',
        'ausente': '<span class="badge bg-danger">Ausente</span>',
        'atraso': '<span class="badge bg-warning">Atraso</span>',
        'saida_antecipada': '<span class="badge bg-info">Saída Antecipada</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Desconhecido</span>';
}

// Event listeners
document.getElementById('filtro_data_presenca').addEventListener('change', carregarPresenca);
document.getElementById('filtro_funcionario_prod').addEventListener('change', carregarProdutividade);
document.getElementById('filtro_funcionario_vendas').addEventListener('change', carregarVendas);
</script>

<?php require_once 'includes/footer.php'; ?> 