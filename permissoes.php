<?php
/**
 * Sistema de Permissões
 * LJ-OS Sistema para Lava Jato
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar login
verificarLogin();

// Verificar permissões ANTES de incluir o header
$pdo = getDB();

if (!verificarPermissao('permissoes')) {
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
            <i class="fas fa-shield-alt"></i>
            Sistema de Permissões
        </h1>
        <button type="button" class="btn btn-primary" onclick="abrirModalPermissao()">
            <i class="fas fa-plus"></i> Nova Permissão
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
                                Usuários Ativos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="usuarios-ativos">0</div>
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
                                Perfis Criados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="perfis-criados">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tag fa-2x text-gray-300"></i>
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
                                Permissões Ativas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="permissoes-ativas">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-key fa-2x text-gray-300"></i>
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
                                Módulos Protegidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="modulos-protegidos">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-lock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Abas de navegação -->
    <ul class="nav nav-tabs" id="permissoesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button" role="tab">
                <i class="fas fa-users"></i> Usuários e Permissões
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="perfis-tab" data-bs-toggle="tab" data-bs-target="#perfis" type="button" role="tab">
                <i class="fas fa-user-tag"></i> Perfis de Acesso
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="modulos-tab" data-bs-toggle="tab" data-bs-target="#modulos" type="button" role="tab">
                <i class="fas fa-cogs"></i> Módulos do Sistema
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">
                <i class="fas fa-history"></i> Logs de Acesso
            </button>
        </li>
    </ul>

    <!-- Conteúdo das abas -->
    <div class="tab-content" id="permissoesTabsContent">
        <!-- Aba Usuários e Permissões -->
        <div class="tab-pane fade show active" id="usuarios" role="tabpanel">
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Usuários e Suas Permissões</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabela-usuarios-permissoes">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Email</th>
                                    <th>Perfil</th>
                                    <th>Status</th>
                                    <th>Último Acesso</th>
                                    <th>Permissões</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-usuarios-permissoes">
                                <!-- Dados carregados via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aba Perfis de Acesso -->
        <div class="tab-pane fade" id="perfis" role="tabpanel">
            <div class="card shadow mt-3">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Perfis de Acesso</h6>
                    <button type="button" class="btn btn-success btn-sm" onclick="abrirModalPerfil()">
                        <i class="fas fa-plus"></i> Novo Perfil
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabela-perfis">
                            <thead>
                                <tr>
                                    <th>Nome do Perfil</th>
                                    <th>Descrição</th>
                                    <th>Usuários</th>
                                    <th>Permissões</th>
                                    <th>Data Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-perfis">
                                <!-- Dados carregados via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aba Módulos do Sistema -->
        <div class="tab-pane fade" id="modulos" role="tabpanel">
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Módulos e Funcionalidades</h6>
                </div>
                <div class="card-body">
                    <div class="row" id="modulos-container">
                        <!-- Módulos carregados via JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Aba Logs de Acesso -->
        <div class="tab-pane fade" id="logs" role="tabpanel">
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Logs de Acesso e Ações</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="filtro_usuario_log" class="form-label">Usuário</label>
                            <select class="form-select" id="filtro_usuario_log">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_acao_log" class="form-label">Ação</label>
                            <select class="form-select" id="filtro_acao_log">
                                <option value="">Todas</option>
                                <option value="login">Login</option>
                                <option value="logout">Logout</option>
                                <option value="criar">Criar</option>
                                <option value="editar">Editar</option>
                                <option value="excluir">Excluir</option>
                                <option value="visualizar">Visualizar</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filtro_data_inicio_log" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="filtro_data_inicio_log">
                        </div>
                        <div class="col-md-2">
                            <label for="filtro_data_fim_log" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="filtro_data_fim_log">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary d-block" onclick="filtrarLogs()">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabela-logs">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Usuário</th>
                                    <th>Ação</th>
                                    <th>Módulo</th>
                                    <th>IP</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-logs">
                                <!-- Dados carregados via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Permissão -->
<div class="modal fade" id="modalPermissao" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPermissaoTitle">Configurar Permissões</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPermissao">
                <div class="modal-body">
                    <input type="hidden" id="permissao_usuario_id" name="usuario_id">
                    
                    <div class="form-group">
                        <label for="permissao_usuario" class="form-label">Usuário *</label>
                        <select class="form-select" id="permissao_usuario" name="usuario" required>
                            <option value="">Selecione um usuário</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="permissao_perfil" class="form-label">Perfil de Acesso</label>
                        <select class="form-select" id="permissao_perfil" name="perfil">
                            <option value="">Selecione um perfil (opcional)</option>
                        </select>
                    </div>

                    <hr>

                    <h6>Permissões por Módulo</h6>
                    <div id="permissoes-modulos">
                        <!-- Permissões carregadas via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Permissões</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Perfil -->
<div class="modal fade" id="modalPerfil" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPerfilTitle">Novo Perfil de Acesso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPerfil">
                <div class="modal-body">
                    <input type="hidden" id="perfil_id" name="id">
                    
                    <div class="form-group">
                        <label for="perfil_nome" class="form-label">Nome do Perfil *</label>
                        <input type="text" class="form-control" id="perfil_nome" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label for="perfil_descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="perfil_descricao" name="descricao" rows="3"></textarea>
                    </div>

                    <hr>

                    <h6>Permissões do Perfil</h6>
                    <div id="permissoes-perfil">
                        <!-- Permissões carregadas via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Perfil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Módulos do sistema
const modulos = [
    {
        id: 'dashboard',
        nome: 'Dashboard',
        descricao: 'Painel principal do sistema',
        funcionalidades: ['visualizar']
    },
    {
        id: 'clientes',
        nome: 'Clientes',
        descricao: 'Gestão de clientes',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    },
    {
        id: 'veiculos',
        nome: 'Veículos',
        descricao: 'Gestão de veículos',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    },
    {
        id: 'agendamentos',
        nome: 'Agendamentos',
        descricao: 'Sistema de agendamentos',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    },
    {
        id: 'ordens_servico',
        nome: 'Ordens de Serviço',
        descricao: 'Gestão de ordens de serviço',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    },
    {
        id: 'estoque',
        nome: 'Controle de Estoque',
        descricao: 'Gestão de produtos e estoque',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    },
    {
        id: 'financeiro',
        nome: 'Módulo Financeiro',
        descricao: 'Controle financeiro',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    },
    {
        id: 'relatorios',
        nome: 'Relatórios',
        descricao: 'Geração de relatórios',
        funcionalidades: ['visualizar', 'exportar']
    },
    {
        id: 'usuarios',
        nome: 'Usuários',
        descricao: 'Gestão de usuários',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    },
    {
        id: 'funcionarios',
        nome: 'Funcionários',
        descricao: 'Gestão de funcionários',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    },
    {
        id: 'orcamentos',
        nome: 'Orçamentos',
        descricao: 'Sistema de orçamentos',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    },
    {
        id: 'permissoes',
        nome: 'Permissões',
        descricao: 'Controle de permissões',
        funcionalidades: ['criar', 'editar', 'excluir', 'visualizar']
    }
];

// Carregar dados iniciais
document.addEventListener('DOMContentLoaded', function() {
    carregarEstatisticas();
    carregarUsuariosPermissoes();
    carregarPerfis();
    carregarModulos();
    carregarLogs();
    
    // Definir datas padrão
    const hoje = new Date();
    const umaSemanaAtras = new Date();
    umaSemanaAtras.setDate(umaSemanaAtras.getDate() - 7);
    
    document.getElementById('filtro_data_inicio_log').value = umaSemanaAtras.toISOString().split('T')[0];
    document.getElementById('filtro_data_fim_log').value = hoje.toISOString().split('T')[0];
});

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const response = await fetch('api/permissoes.php?action=estatisticas');
        const data = await response.json();
        
        if (data.sucesso) {
            document.getElementById('usuarios-ativos').textContent = data.estatisticas.usuarios_ativos;
            document.getElementById('perfis-criados').textContent = data.estatisticas.perfis_criados;
            document.getElementById('permissoes-ativas').textContent = data.estatisticas.permissoes_ativas;
            document.getElementById('modulos-protegidos').textContent = data.estatisticas.modulos_protegidos;
        }
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Carregar usuários e permissões
async function carregarUsuariosPermissoes() {
    try {
        const response = await fetch('api/permissoes.php?action=usuarios');
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarUsuariosPermissoes(data.usuarios);
            carregarSelectUsuarios(data.usuarios);
        } else {
            mostrarAlerta('Erro ao carregar usuários: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        mostrarAlerta('Erro ao carregar usuários', 'danger');
    }
}

// Renderizar usuários e permissões
function renderizarUsuariosPermissoes(usuarios) {
    const tbody = document.getElementById('tbody-usuarios-permissoes');
    tbody.innerHTML = '';
    
    usuarios.forEach(usuario => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${usuario.nome}</td>
            <td>${usuario.email}</td>
            <td>${usuario.perfil || '-'}</td>
            <td>${getStatusBadge(usuario.status)}</td>
            <td>${usuario.ultimo_acesso ? formatarDataHora(usuario.ultimo_acesso) : 'Nunca'}</td>
            <td>${usuario.total_permissoes} permissões</td>
            <td>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="configurarPermissoes(${usuario.id_usuario})" title="Configurar Permissões">
                    <i class="fas fa-cog"></i>
                </button>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="verPermissoes(${usuario.id_usuario})" title="Ver Permissões">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Carregar perfis
async function carregarPerfis() {
    try {
        const response = await fetch('api/permissoes.php?action=perfis');
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarPerfis(data.perfis);
            carregarSelectPerfis(data.perfis);
        } else {
            mostrarAlerta('Erro ao carregar perfis: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar perfis:', error);
        mostrarAlerta('Erro ao carregar perfis', 'danger');
    }
}

// Renderizar perfis
function renderizarPerfis(perfis) {
    const tbody = document.getElementById('tbody-perfis');
    tbody.innerHTML = '';
    
    perfis.forEach(perfil => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${perfil.nome}</td>
            <td>${perfil.descricao || '-'}</td>
            <td>${perfil.total_usuarios} usuários</td>
            <td>${perfil.total_permissoes} permissões</td>
            <td>${formatarData(perfil.data_criacao)}</td>
            <td>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="editarPerfil(${perfil.id_perfil})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="excluirPerfil(${perfil.id_perfil})" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Carregar módulos
function carregarModulos() {
    const container = document.getElementById('modulos-container');
    container.innerHTML = '';
    
    modulos.forEach(modulo => {
        const div = document.createElement('div');
        div.className = 'col-md-6 col-lg-4 mb-3';
        div.innerHTML = `
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">${modulo.nome}</h6>
                </div>
                <div class="card-body">
                    <p class="card-text">${modulo.descricao}</p>
                    <div class="d-flex flex-wrap gap-1">
                        ${modulo.funcionalidades.map(func => `
                            <span class="badge bg-primary">${func}</span>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
        container.appendChild(div);
    });
}

// Carregar logs
async function carregarLogs() {
    try {
        const response = await fetch('api/permissoes.php?action=logs');
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarLogs(data.logs);
            carregarSelectUsuariosLogs(data.usuarios);
        } else {
            mostrarAlerta('Erro ao carregar logs: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar logs:', error);
        mostrarAlerta('Erro ao carregar logs', 'danger');
    }
}

// Renderizar logs
function renderizarLogs(logs) {
    const tbody = document.getElementById('tbody-logs');
    tbody.innerHTML = '';
    
    logs.forEach(log => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatarDataHora(log.data_hora)}</td>
            <td>${log.usuario_nome}</td>
            <td>${getAcaoBadge(log.acao)}</td>
            <td>${log.modulo || '-'}</td>
            <td>${log.ip}</td>
            <td>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="verDetalhesLog(${log.id_log})" title="Ver Detalhes">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Abrir modal permissão
function abrirModalPermissao() {
    document.getElementById('modalPermissaoTitle').textContent = 'Configurar Permissões';
    document.getElementById('formPermissao').reset();
    document.getElementById('permissao_usuario_id').value = '';
    
    carregarPermissoesModulos();
    
    const modal = new bootstrap.Modal(document.getElementById('modalPermissao'));
    modal.show();
}

// Configurar permissões de usuário
function configurarPermissoes(usuarioId) {
    document.getElementById('permissao_usuario_id').value = usuarioId;
    document.getElementById('permissao_usuario').value = usuarioId;
    
    carregarPermissoesUsuario(usuarioId);
    
    const modal = new bootstrap.Modal(document.getElementById('modalPermissao'));
    modal.show();
}

// Carregar permissões de módulos
function carregarPermissoesModulos() {
    const container = document.getElementById('permissoes-modulos');
    container.innerHTML = '';
    
    modulos.forEach(modulo => {
        const div = document.createElement('div');
        div.className = 'card mb-3';
        div.innerHTML = `
            <div class="card-header">
                <h6 class="mb-0">${modulo.nome}</h6>
                <small class="text-muted">${modulo.descricao}</small>
            </div>
            <div class="card-body">
                <div class="row">
                    ${modulo.funcionalidades.map(func => `
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="perm_${modulo.id}_${func}" 
                                       name="permissoes[${modulo.id}][${func}]" 
                                       value="1">
                                <label class="form-check-label" for="perm_${modulo.id}_${func}">
                                    ${func.charAt(0).toUpperCase() + func.slice(1)}
                                </label>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        container.appendChild(div);
    });
}

// Carregar permissões de usuário
async function carregarPermissoesUsuario(usuarioId) {
    try {
        const response = await fetch(`api/permissoes.php?action=permissoes_usuario&id=${usuarioId}`);
        const data = await response.json();
        
        if (data.sucesso) {
            // Marcar checkboxes baseado nas permissões existentes
            data.permissoes.forEach(permissao => {
                const checkbox = document.getElementById(`perm_${permissao.modulo}_${permissao.funcionalidade}`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
    } catch (error) {
        console.error('Erro ao carregar permissões do usuário:', error);
    }
}

// Abrir modal perfil
function abrirModalPerfil() {
    document.getElementById('modalPerfilTitle').textContent = 'Novo Perfil de Acesso';
    document.getElementById('formPerfil').reset();
    document.getElementById('perfil_id').value = '';
    
    carregarPermissoesPerfil();
    
    const modal = new bootstrap.Modal(document.getElementById('modalPerfil'));
    modal.show();
}

// Carregar permissões de perfil
function carregarPermissoesPerfil() {
    const container = document.getElementById('permissoes-perfil');
    container.innerHTML = '';
    
    modulos.forEach(modulo => {
        const div = document.createElement('div');
        div.className = 'card mb-3';
        div.innerHTML = `
            <div class="card-header">
                <h6 class="mb-0">${modulo.nome}</h6>
                <small class="text-muted">${modulo.descricao}</small>
            </div>
            <div class="card-body">
                <div class="row">
                    ${modulo.funcionalidades.map(func => `
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="perfil_${modulo.id}_${func}" 
                                       name="permissoes[${modulo.id}][${func}]" 
                                       value="1">
                                <label class="form-check-label" for="perfil_${modulo.id}_${func}">
                                    ${func.charAt(0).toUpperCase() + func.slice(1)}
                                </label>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        container.appendChild(div);
    });
}

// Salvar permissões
document.getElementById('formPermissao').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/permissoes.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            mostrarAlerta('Permissões salvas com sucesso!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalPermissao')).hide();
            carregarUsuariosPermissoes();
            carregarEstatisticas();
        } else {
            mostrarAlerta('Erro ao salvar permissões: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar permissões:', error);
        mostrarAlerta('Erro ao salvar permissões', 'danger');
    }
});

// Salvar perfil
document.getElementById('formPerfil').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/permissoes.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            mostrarAlerta('Perfil salvo com sucesso!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalPerfil')).hide();
            carregarPerfis();
            carregarEstatisticas();
        } else {
            mostrarAlerta('Erro ao salvar perfil: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar perfil:', error);
        mostrarAlerta('Erro ao salvar perfil', 'danger');
    }
});

// Filtrar logs
async function filtrarLogs() {
    const usuario = document.getElementById('filtro_usuario_log').value;
    const acao = document.getElementById('filtro_acao_log').value;
    const dataInicio = document.getElementById('filtro_data_inicio_log').value;
    const dataFim = document.getElementById('filtro_data_fim_log').value;
    
    const params = new URLSearchParams();
    if (usuario) params.append('usuario', usuario);
    if (acao) params.append('acao', acao);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    
    try {
        const response = await fetch(`api/permissoes.php?action=filtrar_logs&${params.toString()}`);
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarLogs(data.logs);
        } else {
            mostrarAlerta('Erro ao filtrar logs: ' + data.erro, 'danger');
        }
    } catch (error) {
        console.error('Erro ao filtrar logs:', error);
        mostrarAlerta('Erro ao filtrar logs', 'danger');
    }
}

// Funções utilitárias
function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR');
}

function formatarDataHora(data) {
    return new Date(data).toLocaleString('pt-BR');
}

function getStatusBadge(status) {
    const badges = {
        'ativo': '<span class="badge bg-success">Ativo</span>',
        'inativo': '<span class="badge bg-danger">Inativo</span>',
        'bloqueado': '<span class="badge bg-warning">Bloqueado</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Desconhecido</span>';
}

function getAcaoBadge(acao) {
    const badges = {
        'login': '<span class="badge bg-success">Login</span>',
        'logout': '<span class="badge bg-secondary">Logout</span>',
        'criar': '<span class="badge bg-primary">Criar</span>',
        'editar': '<span class="badge bg-warning">Editar</span>',
        'excluir': '<span class="badge bg-danger">Excluir</span>',
        'visualizar': '<span class="badge bg-info">Visualizar</span>',
        'exportar': '<span class="badge bg-dark">Exportar</span>'
    };
    return badges[acao] || `<span class="badge bg-secondary">${acao}</span>`;
}

function carregarSelectUsuarios(usuarios) {
    const select = document.getElementById('permissao_usuario');
    select.innerHTML = '<option value="">Selecione um usuário</option>';
    
    usuarios.forEach(usuario => {
        const option = document.createElement('option');
        option.value = usuario.id_usuario;
        option.textContent = usuario.nome;
        select.appendChild(option);
    });
}

function carregarSelectPerfis(perfis) {
    const select = document.getElementById('permissao_perfil');
    select.innerHTML = '<option value="">Selecione um perfil (opcional)</option>';
    
    perfis.forEach(perfil => {
        const option = document.createElement('option');
        option.value = perfil.id_perfil;
        option.textContent = perfil.nome;
        select.appendChild(option);
    });
}

function carregarSelectUsuariosLogs(usuarios) {
    const select = document.getElementById('filtro_usuario_log');
    select.innerHTML = '<option value="">Todos</option>';
    
    usuarios.forEach(usuario => {
        const option = document.createElement('option');
        option.value = usuario.id_usuario;
        option.textContent = usuario.nome;
        select.appendChild(option);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?> 