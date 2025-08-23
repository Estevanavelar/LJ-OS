-- ========================================
-- SCHEMA COMPLETO DO SISTEMA LJ-OS
-- Baseado no PRD e schema Prisma
-- ========================================

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso TEXT CHECK(nivel_acesso IN ('ADMIN', 'GERENTE', 'ATENDENTE', 'FUNCIONARIO')) DEFAULT 'FUNCIONARIO',
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    foto_perfil VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    observacoes TEXT NULL
);

-- Tabela de Clientes
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(150) NOT NULL,
    tipo_pessoa TEXT CHECK(tipo_pessoa IN ('PF', 'PJ')) NOT NULL DEFAULT 'PF',
    cpf_cnpj VARCHAR(20) UNIQUE NOT NULL,
    rg_ie VARCHAR(20) NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NULL,
    endereco TEXT NULL,
    cep VARCHAR(10) NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(2) NULL,
    data_nascimento DATE NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO',
    observacoes TEXT NULL,
    programa_fidelidade BOOLEAN DEFAULT 0,
    pontos_fidelidade INTEGER DEFAULT 0
);

-- Tabela de Veículos
CREATE TABLE IF NOT EXISTS veiculos (
    id_veiculo INTEGER PRIMARY KEY AUTOINCREMENT,
    id_cliente INTEGER NOT NULL,
    placa VARCHAR(10) UNIQUE NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    ano INTEGER NOT NULL,
    ano_modelo INTEGER NULL,
    cor VARCHAR(30) NOT NULL,
    combustivel TEXT CHECK(combustivel IN ('GASOLINA', 'ETANOL', 'DIESEL', 'FLEX', 'GNV', 'ELETRICO', 'HIBRIDO')) NULL,
    km_atual INTEGER NULL,
    observacoes TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO',
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
);

-- Tabela de Categorias de Serviços
CREATE TABLE IF NOT EXISTS categorias_servicos (
    id_categoria INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO'
);

-- Tabela de Serviços
CREATE TABLE IF NOT EXISTS servicos (
    id_servico INTEGER PRIMARY KEY AUTOINCREMENT,
    id_categoria INTEGER NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL,
    duracao_estimada INTEGER NOT NULL, -- em minutos
    tipo_veiculo TEXT CHECK(tipo_veiculo IN ('CARRO', 'MOTO', 'CAMINHAO', 'TODOS')) DEFAULT 'TODOS',
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_servicos(id_categoria) ON DELETE SET NULL
);

-- Tabela de Agendamentos
CREATE TABLE IF NOT EXISTS agendamentos (
    id_agendamento INTEGER PRIMARY KEY AUTOINCREMENT,
    id_cliente INTEGER NOT NULL,
    id_veiculo INTEGER NOT NULL,
    id_servico INTEGER NOT NULL,
    data_agendamento DATETIME NOT NULL,
    hora_entrega_estimada DATETIME NULL,
    status TEXT CHECK(status IN ('AGENDADO', 'CONFIRMADO', 'EM_ANDAMENTO', 'CONCLUIDO', 'CANCELADO')) DEFAULT 'AGENDADO',
    observacoes TEXT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo),
    FOREIGN KEY (id_servico) REFERENCES servicos(id_servico)
);

-- Tabela de Funcionários
CREATE TABLE IF NOT EXISTS funcionarios (
    id_funcionario INTEGER PRIMARY KEY AUTOINCREMENT,
    id_usuario INTEGER UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    rg VARCHAR(20) NULL,
    data_nascimento DATE NULL,
    data_admissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cargo VARCHAR(100) NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    salario DECIMAL(10,2) NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    endereco TEXT NULL,
    foto VARCHAR(255) NULL,
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Tabela de Ordens de Serviço
CREATE TABLE IF NOT EXISTS ordens_servico (
    id_os INTEGER PRIMARY KEY AUTOINCREMENT,
    id_cliente INTEGER NOT NULL,
    id_veiculo INTEGER NOT NULL,
    id_funcionario INTEGER NULL,
    numero_os VARCHAR(20) UNIQUE NOT NULL,
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP NULL,
    status TEXT CHECK(status IN ('ABERTA', 'EM_ANDAMENTO', 'CONCLUIDA', 'CANCELADA')) DEFAULT 'ABERTA',
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    valor_final DECIMAL(10,2) DEFAULT 0.00,
    observacoes TEXT NULL,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo),
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario)
);

-- Tabela de Itens da Ordem de Serviço
CREATE TABLE IF NOT EXISTS itens_ordem_servico (
    id_item INTEGER PRIMARY KEY AUTOINCREMENT,
    id_os INTEGER NOT NULL,
    id_servico INTEGER NOT NULL,
    quantidade INTEGER DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    valor_total DECIMAL(10,2) NOT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE CASCADE,
    FOREIGN KEY (id_servico) REFERENCES servicos(id_servico)
);

-- Tabela de Categorias de Produtos
CREATE TABLE IF NOT EXISTS categorias_produtos (
    id_categoria INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO'
);

-- Tabela de Produtos
CREATE TABLE IF NOT EXISTS produtos (
    id_produto INTEGER PRIMARY KEY AUTOINCREMENT,
    id_categoria INTEGER NULL,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    codigo_barras VARCHAR(50) UNIQUE NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    marca VARCHAR(100) NULL,
    modelo VARCHAR(100) NULL,
    tamanho VARCHAR(50) NULL,
    preco_custo DECIMAL(10,2) NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL,
    margem DECIMAL(5,2) NOT NULL,
    estoque_minimo INTEGER DEFAULT 0,
    estoque_maximo INTEGER NULL,
    unidade_medida VARCHAR(20) NOT NULL,
    localizacao VARCHAR(100) NULL,
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_produtos(id_categoria) ON DELETE SET NULL
);

-- Tabela de Movimentações de Estoque
CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    id_movimentacao INTEGER PRIMARY KEY AUTOINCREMENT,
    id_produto INTEGER NOT NULL,
    id_funcionario INTEGER NULL,
    tipo TEXT CHECK(tipo IN ('ENTRADA', 'SAIDA', 'TRANSFERENCIA', 'AJUSTE')) NOT NULL,
    quantidade INTEGER NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    motivo VARCHAR(200) NOT NULL,
    observacoes TEXT NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto),
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario)
);

-- Tabela de Fornecedores
CREATE TABLE IF NOT EXISTS fornecedores (
    id_fornecedor INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(150) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    inscricao_estadual VARCHAR(20) NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NULL,
    endereco TEXT NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(2) NULL,
    cep VARCHAR(10) NULL,
    contato VARCHAR(100) NULL,
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Categorias Financeiras
CREATE TABLE IF NOT EXISTS categorias_financeiras (
    id_categoria INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    tipo TEXT CHECK(tipo IN ('RECEITA', 'DESPESA')) NOT NULL,
    descricao TEXT NULL,
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO'
);

-- Tabela de Receitas
CREATE TABLE IF NOT EXISTS receitas (
    id_receita INTEGER PRIMARY KEY AUTOINCREMENT,
    id_categoria INTEGER NULL,
    id_os INTEGER NULL,
    descricao VARCHAR(200) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_recebimento DATETIME NOT NULL,
    forma_pagamento TEXT CHECK(forma_pagamento IN ('DINHEIRO', 'CARTAO_CREDITO', 'CARTAO_DEBITO', 'PIX', 'TRANSFERENCIA', 'BOLETO')) NOT NULL,
    observacoes TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_financeiras(id_categoria) ON DELETE SET NULL,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE SET NULL
);

-- Tabela de Despesas
CREATE TABLE IF NOT EXISTS despesas (
    id_despesa INTEGER PRIMARY KEY AUTOINCREMENT,
    id_categoria INTEGER NOT NULL,
    descricao VARCHAR(200) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_vencimento DATETIME NOT NULL,
    data_pagamento DATETIME NULL,
    forma_pagamento TEXT CHECK(forma_pagamento IN ('DINHEIRO', 'CARTAO_CREDITO', 'CARTAO_DEBITO', 'PIX', 'TRANSFERENCIA', 'BOLETO')) NULL,
    observacoes TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_financeiras(id_categoria)
);

-- Tabela de Pagamentos
CREATE TABLE IF NOT EXISTS pagamentos (
    id_pagamento INTEGER PRIMARY KEY AUTOINCREMENT,
    id_os INTEGER NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    forma_pagamento TEXT CHECK(forma_pagamento IN ('DINHEIRO', 'CARTAO_CREDITO', 'CARTAO_DEBITO', 'PIX', 'TRANSFERENCIA', 'BOLETO')) NOT NULL,
    data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT NULL,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE CASCADE
);

-- Tabela de Cupons
CREATE TABLE IF NOT EXISTS cupons (
    id_cupom INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descricao VARCHAR(200) NOT NULL,
    tipo TEXT CHECK(tipo IN ('VALOR_FIXO', 'PERCENTUAL')) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    percentual DECIMAL(5,2) NULL,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    uso_maximo INTEGER NULL,
    uso_atual INTEGER DEFAULT 0,
    status TEXT CHECK(status IN ('ATIVO', 'INATIVO')) DEFAULT 'ATIVO',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Cupons por Cliente
CREATE TABLE IF NOT EXISTS cupons_clientes (
    id_cupom_cliente INTEGER PRIMARY KEY AUTOINCREMENT,
    id_cupom INTEGER NOT NULL,
    id_cliente INTEGER NOT NULL,
    data_uso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valor_desconto DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_cupom) REFERENCES cupons(id_cupom),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

-- Tabela de Logs de Auditoria
CREATE TABLE IF NOT EXISTS logs_auditoria (
    id_log INTEGER PRIMARY KEY AUTOINCREMENT,
    id_usuario INTEGER NOT NULL,
    acao VARCHAR(100) NOT NULL,
    tabela VARCHAR(100) NOT NULL,
    registro_id INTEGER NULL,
    dados_anteriores TEXT NULL,
    dados_novos TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Tabela de Configurações
CREATE TABLE IF NOT EXISTS configuracoes (
    id_configuracao INTEGER PRIMARY KEY AUTOINCREMENT,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- ÍNDICES PARA OTIMIZAÇÃO
-- ========================================

-- Índices para Clientes
CREATE INDEX IF NOT EXISTS idx_clientes_cpf_cnpj ON clientes(cpf_cnpj);
CREATE INDEX IF NOT EXISTS idx_clientes_telefone ON clientes(telefone);
CREATE INDEX IF NOT EXISTS idx_clientes_status ON clientes(status);
CREATE INDEX IF NOT EXISTS idx_clientes_data_cadastro ON clientes(data_cadastro);

-- Índices para Veículos
CREATE INDEX IF NOT EXISTS idx_veiculos_placa ON veiculos(placa);
CREATE INDEX IF NOT EXISTS idx_veiculos_id_cliente ON veiculos(id_cliente);
CREATE INDEX IF NOT EXISTS idx_veiculos_marca_modelo ON veiculos(marca, modelo);

-- Índices para Agendamentos
CREATE INDEX IF NOT EXISTS idx_agendamentos_data ON agendamentos(data_agendamento);
CREATE INDEX IF NOT EXISTS idx_agendamentos_status ON agendamentos(status);
CREATE INDEX IF NOT EXISTS idx_agendamentos_cliente_veiculo ON agendamentos(id_cliente, id_veiculo);

-- Índices para Ordens de Serviço
CREATE INDEX IF NOT EXISTS idx_os_numero ON ordens_servico(numero_os);
CREATE INDEX IF NOT EXISTS idx_os_status ON ordens_servico(status);
CREATE INDEX IF NOT EXISTS idx_os_data_abertura ON ordens_servico(data_abertura);
CREATE INDEX IF NOT EXISTS idx_os_id_cliente ON ordens_servico(id_cliente);

-- Índices para Produtos
CREATE INDEX IF NOT EXISTS idx_produtos_codigo ON produtos(codigo);
CREATE INDEX IF NOT EXISTS idx_produtos_codigo_barras ON produtos(codigo_barras);
CREATE INDEX IF NOT EXISTS idx_produtos_id_categoria ON produtos(id_categoria);
CREATE INDEX IF NOT EXISTS idx_produtos_status ON produtos(status);

-- Índices para Movimentações de Estoque
CREATE INDEX IF NOT EXISTS idx_mov_estoque_id_produto ON movimentacoes_estoque(id_produto);
CREATE INDEX IF NOT EXISTS idx_mov_estoque_tipo ON movimentacoes_estoque(tipo);
CREATE INDEX IF NOT EXISTS idx_mov_estoque_data ON movimentacoes_estoque(data_movimentacao);

-- Índices para Receitas
CREATE INDEX IF NOT EXISTS idx_receitas_data ON receitas(data_recebimento);
CREATE INDEX IF NOT EXISTS idx_receitas_id_categoria ON receitas(id_categoria);
CREATE INDEX IF NOT EXISTS idx_receitas_forma_pagamento ON receitas(forma_pagamento);

-- Índices para Despesas
CREATE INDEX IF NOT EXISTS idx_despesas_data_vencimento ON despesas(data_vencimento);
CREATE INDEX IF NOT EXISTS idx_despesas_id_categoria ON despesas(id_categoria);
CREATE INDEX IF NOT EXISTS idx_despesas_status ON despesas(status);

-- Índices para Logs de Auditoria
CREATE INDEX IF NOT EXISTS idx_logs_id_usuario ON logs_auditoria(id_usuario);
CREATE INDEX IF NOT EXISTS idx_logs_acao ON logs_auditoria(acao);
CREATE INDEX IF NOT EXISTS idx_logs_tabela ON logs_auditoria(tabela);
CREATE INDEX IF NOT EXISTS idx_logs_data_acao ON logs_auditoria(data_acao);

-- ========================================
-- DADOS INICIAIS
-- ========================================

-- Usuário administrador padrão
INSERT OR IGNORE INTO usuarios (nome, email, senha, nivel_acesso, status) VALUES 
('Administrador', 'admin@lj-os.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 'ATIVO');

-- Categorias de serviços padrão
INSERT OR IGNORE INTO categorias_servicos (nome, descricao) VALUES 
('Lavagem', 'Serviços de limpeza de veículos'),
('Polimento', 'Serviços de polimento e acabamento'),
('Detalhamento', 'Serviços de detalhamento completo'),
('Manutenção', 'Serviços de manutenção básica');

-- Serviços padrão
INSERT OR IGNORE INTO servicos (id_categoria, nome, descricao, preco, duracao_estimada) VALUES 
(1, 'Lavagem Simples', 'Lavagem externa do veículo', 25.00, 30),
(1, 'Lavagem Completa', 'Lavagem externa e interna', 45.00, 60),
(2, 'Polimento Simples', 'Polimento básico da pintura', 80.00, 90),
(2, 'Polimento Completo', 'Polimento completo com cera', 150.00, 180),
(3, 'Detalhamento Básico', 'Limpeza interna completa', 120.00, 120),
(3, 'Detalhamento Premium', 'Detalhamento completo com produtos premium', 250.00, 240);

-- Categorias financeiras padrão
INSERT OR IGNORE INTO categorias_financeiras (nome, tipo, descricao) VALUES 
('Vendas de Serviços', 'RECEITA', 'Receitas provenientes de serviços'),
('Vendas de Produtos', 'RECEITA', 'Receitas provenientes de produtos'),
('Salários', 'DESPESA', 'Despesas com pessoal'),
('Aluguel', 'DESPESA', 'Despesas com locação'),
('Água e Luz', 'DESPESA', 'Despesas com serviços públicos'),
('Produtos de Limpeza', 'DESPESA', 'Despesas com produtos de limpeza');

-- Configurações padrão do sistema
INSERT OR IGNORE INTO configuracoes (chave, valor, categoria, descricao) VALUES 
('empresa_nome', 'LJ-OS Sistema', 'empresa', 'Nome da empresa'),
('empresa_telefone', '(11) 99999-9999', 'empresa', 'Telefone da empresa'),
('empresa_endereco', 'Rua Exemplo, 123 - São Paulo/SP', 'empresa', 'Endereço da empresa'),
('empresa_cnpj', '00.000.000/0000-00', 'empresa', 'CNPJ da empresa'),
('empresa_email', 'contato@lj-os.com', 'empresa', 'Email da empresa'),
('sistema_timezone', 'America/Sao_Paulo', 'sistema', 'Timezone do sistema'),
('sistema_locale', 'pt_BR', 'sistema', 'Idioma do sistema'),
('sistema_debug', 'true', 'sistema', 'Modo debug do sistema');
