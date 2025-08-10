<?php
/**
 * Sistema de Orçamentos
 * LJ-OS Sistema para Lava Jato
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar login
verificarLogin();

// Verificar permissões ANTES de incluir o header
$pdo = getDB();

if (!verificarPermissao('orcamentos')) {
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
            <i class="fas fa-file-invoice-dollar"></i>
            Sistema de Orçamentos
        </h1>
        <button type="button" class="btn btn-primary" onclick="abrirModalOrcamento()">
            <i class="fas fa-plus"></i> Novo Orçamento
        </button>
    </div>

    <!-- Cards de estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Orçamentos Hoje
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="orcamentos-hoje">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                Aprovados (Mês)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="orcamentos-aprovados">0</div>
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
                                Pendentes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="orcamentos-pendentes">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Valor Total (Mês)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="valor-total">R$ 0,00</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="filtro_status" class="form-label">Status</label>
                    <select class="form-select" id="filtro_status">
                        <option value="">Todos</option>
                        <option value="pendente">Pendente</option>
                        <option value="aprovado">Aprovado</option>
                        <option value="rejeitado">Rejeitado</option>
                        <option value="expirado">Expirado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtro_cliente" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="filtro_cliente" placeholder="Nome do cliente">
                </div>
                <div class="col-md-2">
                    <label for="filtro_data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="filtro_data_inicio">
                </div>
                <div class="col-md-2">
                    <label for="filtro_data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="filtro_data_fim">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-primary" onclick="filtrarOrcamentos()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="limparFiltros()">
                            <i class="fas fa-times"></i> Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de orçamentos -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Orçamentos</h6>
            <div>
                <button type="button" class="btn btn-success btn-sm" onclick="exportarOrcamentos()">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tabela-orcamentos" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nº Orçamento</th>
                            <th>Cliente</th>
                            <th>Veículo</th>
                            <th>Data</th>
                            <th>Validade</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-orcamentos">
                        <!-- Dados carregados via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Orçamento -->
<div class="modal fade" id="modalOrcamento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalOrcamentoTitle">Novo Orçamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formOrcamento">
                <div class="modal-body">
                    <input type="hidden" id="orcamento_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="orcamento_cliente" class="form-label">Cliente *</label>
                                <select class="form-select" id="orcamento_cliente" name="id_cliente" required>
                                    <option value="">Selecione um cliente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="orcamento_veiculo" class="form-label">Veículo *</label>
                                <select class="form-select" id="orcamento_veiculo" name="id_veiculo" required>
                                    <option value="">Selecione um veículo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="orcamento_data" class="form-label">Data do Orçamento *</label>
                                <input type="date" class="form-control" id="orcamento_data" name="data_orcamento" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="orcamento_validade" class="form-label">Validade (dias) *</label>
                                <input type="number" class="form-control" id="orcamento_validade" name="validade_dias" value="7" min="1" max="30" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="orcamento_observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="orcamento_observacoes" name="observacoes" rows="3"></textarea>
                    </div>

                    <hr>

                    <h6>Itens do Orçamento</h6>
                    <div id="itens-orcamento">
                        <div class="row item-orcamento">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Tipo</label>
                                    <select class="form-select item-tipo" name="itens[0][tipo]">
                                        <option value="servico">Serviço</option>
                                        <option value="produto">Produto</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Item</label>
                                    <select class="form-select item-id" name="itens[0][id]">
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Qtd</label>
                                    <input type="number" class="form-control item-quantidade" name="itens[0][quantidade]" value="1" min="1">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Valor</label>
                                    <input type="text" class="form-control item-valor" name="itens[0][valor]" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="adicionarItem()">
                        <i class="fas fa-plus"></i> Adicionar Item
                    </button>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="form-group">
                                <label class="form-label">Subtotal</label>
                                <input type="text" class="form-control" id="orcamento_subtotal" readonly>
                            </div>
                            <div class="form-group">
                                <label for="orcamento_desconto" class="form-label">Desconto (%)</label>
                                <input type="number" class="form-control" id="orcamento_desconto" name="desconto" value="0" min="0" max="100" step="0.01">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Total</label>
                                <input type="text" class="form-control" id="orcamento_total" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Orçamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Visualizar Orçamento -->
<div class="modal fade" id="modalVisualizarOrcamento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Visualizar Orçamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="conteudo-orcamento">
                <!-- Conteúdo carregado via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="gerarPDF()">
                    <i class="fas fa-file-pdf"></i> Gerar PDF
                </button>
                <button type="button" class="btn btn-primary" onclick="aprovarOrcamento()">
                    <i class="fas fa-check"></i> Aprovar
                </button>
                <button type="button" class="btn btn-danger" onclick="rejeitarOrcamento()">
                    <i class="fas fa-times"></i> Rejeitar
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
let orcamentoAtual = null;
let servicos = [];
let produtos = [];

// Carregar dados iniciais
document.addEventListener('DOMContentLoaded', function() {
    carregarEstatisticas();
    carregarOrcamentos();
    carregarClientes();
    carregarServicos();
    carregarProdutos();
    
    // Definir data atual
    document.getElementById('orcamento_data').value = new Date().toISOString().split('T')[0];
});

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const response = await fetch('api/orcamentos.php?action=estatisticas');
        const data = await response.json();
        
        if (data.sucesso) {
            document.getElementById('orcamentos-hoje').textContent = data.estatisticas.hoje;
            document.getElementById('orcamentos-aprovados').textContent = data.estatisticas.aprovados_mes;
            document.getElementById('orcamentos-pendentes').textContent = data.estatisticas.pendentes;
            document.getElementById('valor-total').textContent = `R$ ${parseFloat(data.estatisticas.valor_total).toFixed(2).replace('.', ',')}`;
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Carregar orçamentos
async function carregarOrcamentos() {
    try {
        const response = await fetch('api/orcamentos.php?action=listar');
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarOrcamentos(data.orcamentos);
        } else {
            mostrarAlerta('Erro ao carregar orçamentos: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar orçamentos:', error);
        mostrarAlerta('Erro ao carregar orçamentos', 'danger');
    }
}

// Renderizar orçamentos na tabela
function renderizarOrcamentos(orcamentos) {
    const tbody = document.getElementById('tbody-orcamentos');
    tbody.innerHTML = '';
    
    orcamentos.forEach(orcamento => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>#${orcamento.numero_orcamento}</td>
            <td>${orcamento.cliente_nome}</td>
            <td>${orcamento.veiculo_placa} - ${orcamento.veiculo_modelo}</td>
            <td>${formatarData(orcamento.data_orcamento)}</td>
            <td>${formatarData(orcamento.data_validade)}</td>
            <td>R$ ${parseFloat(orcamento.valor_total).toFixed(2).replace('.', ',')}</td>
            <td>${getStatusBadge(orcamento.status)}</td>
            <td>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="visualizarOrcamento(${orcamento.id_orcamento})" title="Visualizar">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="editarOrcamento(${orcamento.id_orcamento})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="excluirOrcamento(${orcamento.id_orcamento})" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Carregar clientes
async function carregarClientes() {
    try {
        const response = await fetch('api/clientes.php?action=listar');
        const data = await response.json();
        
        if (data.sucesso) {
            const select = document.getElementById('orcamento_cliente');
            select.innerHTML = '<option value="">Selecione um cliente</option>';
            
            data.clientes.forEach(cliente => {
                const option = document.createElement('option');
                option.value = cliente.id_cliente;
                option.textContent = cliente.nome;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar clientes:', error);
    }
}

// Carregar serviços
async function carregarServicos() {
    try {
        const response = await fetch('api/servicos.php?action=listar');
        const data = await response.json();
        
        if (data.sucesso) {
            servicos = data.servicos;
        }
    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
    }
}

// Carregar produtos
async function carregarProdutos() {
    try {
        const response = await fetch('api/estoque.php?action=listar');
        const data = await response.json();
        
        if (data.sucesso) {
            produtos = data.produtos;
        }
    } catch (error) {
        console.error('Erro ao carregar produtos:', error);
    }
}

// Abrir modal de orçamento
function abrirModalOrcamento() {
    document.getElementById('modalOrcamentoTitle').textContent = 'Novo Orçamento';
    document.getElementById('formOrcamento').reset();
    document.getElementById('orcamento_id').value = '';
    document.getElementById('orcamento_data').value = new Date().toISOString().split('T')[0];
    
    // Limpar itens
    const itensContainer = document.getElementById('itens-orcamento');
    itensContainer.innerHTML = '';
    adicionarItem();
    
    const modal = new bootstrap.Modal(document.getElementById('modalOrcamento'));
    modal.show();
}

// Adicionar item ao orçamento
function adicionarItem() {
    const container = document.getElementById('itens-orcamento');
    const itemIndex = container.children.length;
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'row item-orcamento mb-2';
    itemDiv.innerHTML = `
        <div class="col-md-4">
            <div class="form-group">
                <label class="form-label">Tipo</label>
                <select class="form-select item-tipo" name="itens[${itemIndex}][tipo]" onchange="atualizarItens(${itemIndex})">
                    <option value="servico">Serviço</option>
                    <option value="produto">Produto</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="form-label">Item</label>
                <select class="form-select item-id" name="itens[${itemIndex}][id]" onchange="atualizarValor(${itemIndex})">
                    <option value="">Selecione...</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="form-label">Qtd</label>
                <input type="number" class="form-control item-quantidade" name="itens[${itemIndex}][quantidade]" value="1" min="1" onchange="calcularTotal()">
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group">
                <label class="form-label">Valor</label>
                <input type="text" class="form-control item-valor" name="itens[${itemIndex}][valor]" readonly>
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removerItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
    atualizarItens(itemIndex);
}

// Atualizar itens disponíveis
function atualizarItens(index) {
    const itemDiv = document.querySelectorAll('.item-orcamento')[index];
    const tipoSelect = itemDiv.querySelector('.item-tipo');
    const itemSelect = itemDiv.querySelector('.item-id');
    
    itemSelect.innerHTML = '<option value="">Selecione...</option>';
    
    if (tipoSelect.value === 'servico') {
        servicos.forEach(servico => {
            const option = document.createElement('option');
            option.value = servico.id_servico;
            option.textContent = servico.nome_servico;
            option.dataset.preco = servico.preco;
            itemSelect.appendChild(option);
        });
    } else if (tipoSelect.value === 'produto') {
        produtos.forEach(produto => {
            const option = document.createElement('option');
            option.value = produto.id_produto;
            option.textContent = produto.nome_produto;
            option.dataset.preco = produto.preco_venda;
            itemSelect.appendChild(option);
        });
    }
}

// Atualizar valor do item
function atualizarValor(index) {
    const itemDiv = document.querySelectorAll('.item-orcamento')[index];
    const itemSelect = itemDiv.querySelector('.item-id');
    const valorInput = itemDiv.querySelector('.item-valor');
    
    if (itemSelect.value) {
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        const preco = selectedOption.dataset.preco;
        valorInput.value = parseFloat(preco).toFixed(2);
        calcularTotal();
    } else {
        valorInput.value = '';
        calcularTotal();
    }
}

// Calcular total
function calcularTotal() {
    let subtotal = 0;
    
    document.querySelectorAll('.item-orcamento').forEach(item => {
        const quantidade = parseFloat(item.querySelector('.item-quantidade').value) || 0;
        const valor = parseFloat(item.querySelector('.item-valor').value) || 0;
        subtotal += quantidade * valor;
    });
    
    const desconto = parseFloat(document.getElementById('orcamento_desconto').value) || 0;
    const valorDesconto = (subtotal * desconto) / 100;
    const total = subtotal - valorDesconto;
    
    document.getElementById('orcamento_subtotal').value = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    document.getElementById('orcamento_total').value = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

// Remover item
function removerItem(button) {
    button.closest('.item-orcamento').remove();
    calcularTotal();
}

// Salvar orçamento
document.getElementById('formOrcamento').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const orcamentoId = document.getElementById('orcamento_id').value;
    
    try {
        const response = await fetch('api/orcamentos.php', {
            method: orcamentoId ? 'PUT' : 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            mostrarAlerta('Orçamento salvo com sucesso!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalOrcamento')).hide();
            carregarOrcamentos();
            carregarEstatisticas();
        } else {
            mostrarAlerta('Erro ao salvar orçamento: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar orçamento:', error);
        mostrarAlerta('Erro ao salvar orçamento', 'danger');
    }
});

// Visualizar orçamento
async function visualizarOrcamento(id) {
    try {
        const response = await fetch(`api/orcamentos.php?action=visualizar&id=${id}`);
        const data = await response.json();
        
        if (data.sucesso) {
            orcamentoAtual = data.orcamento;
            renderizarVisualizacaoOrcamento(data.orcamento);
            
            const modal = new bootstrap.Modal(document.getElementById('modalVisualizarOrcamento'));
            modal.show();
        } else {
            mostrarAlerta('Erro ao carregar orçamento: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao visualizar orçamento:', error);
        mostrarAlerta('Erro ao visualizar orçamento', 'danger');
    }
}

// Renderizar visualização do orçamento
function renderizarVisualizacaoOrcamento(orcamento) {
    const conteudo = document.getElementById('conteudo-orcamento');
    
    conteudo.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Informações do Cliente</h6>
                <p><strong>Nome:</strong> ${orcamento.cliente_nome}</p>
                <p><strong>Telefone:</strong> ${orcamento.cliente_telefone}</p>
                <p><strong>Email:</strong> ${orcamento.cliente_email}</p>
            </div>
            <div class="col-md-6">
                <h6>Informações do Veículo</h6>
                <p><strong>Placa:</strong> ${orcamento.veiculo_placa}</p>
                <p><strong>Marca/Modelo:</strong> ${orcamento.veiculo_marca} ${orcamento.veiculo_modelo}</p>
                <p><strong>Ano:</strong> ${orcamento.veiculo_ano}</p>
                <p><strong>Cor:</strong> ${orcamento.veiculo_cor}</p>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-6">
                <h6>Informações do Orçamento</h6>
                <p><strong>Número:</strong> #${orcamento.numero_orcamento}</p>
                <p><strong>Data:</strong> ${formatarData(orcamento.data_orcamento)}</p>
                <p><strong>Validade:</strong> ${formatarData(orcamento.data_validade)}</p>
                <p><strong>Status:</strong> ${getStatusText(orcamento.status)}</p>
            </div>
            <div class="col-md-6">
                <h6>Valores</h6>
                <p><strong>Subtotal:</strong> R$ ${parseFloat(orcamento.subtotal).toFixed(2).replace('.', ',')}</p>
                <p><strong>Desconto:</strong> ${orcamento.desconto}%</p>
                <p><strong>Total:</strong> R$ ${parseFloat(orcamento.valor_total).toFixed(2).replace('.', ',')}</p>
            </div>
        </div>
        
        <hr>
        
        <h6>Itens do Orçamento</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Item</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${orcamento.itens.map(item => `
                        <tr>
                            <td>${item.tipo === 'servico' ? 'Serviço' : 'Produto'}</td>
                            <td>${item.nome}</td>
                            <td>${item.quantidade}</td>
                            <td>R$ ${parseFloat(item.valor_unitario).toFixed(2).replace('.', ',')}</td>
                            <td>R$ ${parseFloat(item.valor_total).toFixed(2).replace('.', ',')}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        ${orcamento.observacoes ? `
            <hr>
            <h6>Observações</h6>
            <p>${orcamento.observacoes}</p>
        ` : ''}
    `;
}

// Aprovar orçamento
async function aprovarOrcamento() {
    if (!orcamentoAtual) return;
    try {
        const response = await fetch('api/orcamentos.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=aprovar&id=${orcamentoAtual.id_orcamento}`
        });
        const data = await response.json();
        if (data.sucesso) {
            mostrarAlerta('Orçamento aprovado com sucesso!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalVisualizarOrcamento')).hide();
            carregarOrcamentos();
            carregarEstatisticas();
        } else {
            mostrarAlerta('Erro ao aprovar orçamento: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao aprovar orçamento:', error);
        mostrarAlerta('Erro ao aprovar orçamento', 'danger');
    }
}

// Rejeitar orçamento
async function rejeitarOrcamento() {
    if (!orcamentoAtual) return;
    try {
        const response = await fetch('api/orcamentos.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=rejeitar&id=${orcamentoAtual.id_orcamento}`
        });
        const data = await response.json();
        if (data.sucesso) {
            mostrarAlerta('Orçamento rejeitado com sucesso!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalVisualizarOrcamento')).hide();
            carregarOrcamentos();
            carregarEstatisticas();
        } else {
            mostrarAlerta('Erro ao rejeitar orçamento: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao rejeitar orçamento:', error);
        mostrarAlerta('Erro ao rejeitar orçamento', 'danger');
    }
}

// Gerar PDF
function gerarPDF() {
    if (!orcamentoAtual) return;
    
    window.open(`api/orcamentos.php?action=pdf&id=${orcamentoAtual.id_orcamento}`, '_blank');
}

// Filtrar orçamentos
async function filtrarOrcamentos() {
    const status = document.getElementById('filtro_status').value;
    const cliente = document.getElementById('filtro_cliente').value;
    const dataInicio = document.getElementById('filtro_data_inicio').value;
    const dataFim = document.getElementById('filtro_data_fim').value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (cliente) params.append('cliente', cliente);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    
    try {
        const response = await fetch(`api/orcamentos.php?action=filtrar&${params.toString()}`);
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarOrcamentos(data.orcamentos);
        } else {
            mostrarAlerta('Erro ao filtrar orçamentos: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao filtrar orçamentos:', error);
        mostrarAlerta('Erro ao filtrar orçamentos', 'danger');
    }
}

// Limpar filtros
function limparFiltros() {
    document.getElementById('filtro_status').value = '';
    document.getElementById('filtro_cliente').value = '';
    document.getElementById('filtro_data_inicio').value = '';
    document.getElementById('filtro_data_fim').value = '';
    carregarOrcamentos();
}

// Exportar orçamentos
function exportarOrcamentos() {
    const status = document.getElementById('filtro_status').value;
    const cliente = document.getElementById('filtro_cliente').value;
    const dataInicio = document.getElementById('filtro_data_inicio').value;
    const dataFim = document.getElementById('filtro_data_fim').value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (cliente) params.append('cliente', cliente);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    
    window.open(`api/orcamentos.php?action=exportar&${params.toString()}`, '_blank');
}

// Funções utilitárias
function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR');
}

function getStatusBadge(status) {
    const badges = {
        'pendente': '<span class="badge bg-warning">Pendente</span>',
        'aprovado': '<span class="badge bg-success">Aprovado</span>',
        'rejeitado': '<span class="badge bg-danger">Rejeitado</span>',
        'expirado': '<span class="badge bg-secondary">Expirado</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Desconhecido</span>';
}

function getStatusText(status) {
    const textos = {
        'pendente': 'Pendente',
        'aprovado': 'Aprovado',
        'rejeitado': 'Rejeitado',
        'expirado': 'Expirado'
    };
    return textos[status] || 'Desconhecido';
}

// Event listeners
document.getElementById('orcamento_desconto').addEventListener('input', calcularTotal);
</script>

<?php require_once 'includes/footer.php'; ?> 