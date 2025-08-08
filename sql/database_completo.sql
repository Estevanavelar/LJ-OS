-- Script de criaÃ§Ã£o do banco de dados para LJ-OS Sistema para Lava Jato
-- Sistema completo de gestÃ£o para lava jatos

-- CriaÃ§Ã£o do banco de dados
CREATE DATABASE IF NOT EXISTS lava_jato_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lava_jato_db;

-- Tabela de usuÃ¡rios do sistema
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'gerente', 'atendente', 'funcionario') DEFAULT 'funcionario',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    foto_perfil VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    observacoes TEXT NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Tabela de clientes (pessoas fÃ­sicas e jurÃ­dicas)
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    tipo_pessoa ENUM('PF', 'PJ') NOT NULL DEFAULT 'PF',
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
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    observacoes TEXT NULL,
    programa_fidelidade BOOLEAN DEFAULT FALSE,
    pontos_fidelidade INT DEFAULT 0,
    INDEX idx_cpf_cnpj (cpf_cnpj),
    INDEX idx_telefone (telefone),
    INDEX idx_status (status)
);

-- Tabela de veÃ­culos
CREATE TABLE veiculos (
    id_veiculo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    placa VARCHAR(10) UNIQUE NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    ano INT NOT NULL,
    ano_modelo INT NULL,
    cor VARCHAR(30) NOT NULL,
    combustivel ENUM('gasolina', 'etanol', 'diesel', 'flex', 'gnv', 'eletrico', 'hibrido') NULL,
    km_atual INT NULL,
    observacoes TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    INDEX idx_placa (placa),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de categorias de serviÃ§os
CREATE TABLE categorias_servicos (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo'
);

-- Tabela de serviÃ§os oferecidos
CREATE TABLE servicos (
    id_servico INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NULL,
    nome_servico VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL,
    duracao_estimada INT NOT NULL COMMENT 'DuraÃ§Ã£o em minutos',
    tipo_veiculo ENUM('carro', 'moto', 'caminhao', 'todos') DEFAULT 'todos',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_servicos(id_categoria) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_tipo_veiculo (tipo_veiculo)
);

-- Tabela de agendamentos
CREATE TABLE agendamentos (
    id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_veiculo INT NOT NULL,
    id_servico INT NOT NULL,
    data_agendamento DATETIME NOT NULL,
    hora_entrega_estimada DATETIME NULL,
    vaga INT NULL COMMENT 'NÃºmero da vaga onde serÃ¡ realizado o serviÃ§o',
    status ENUM('pendente', 'confirmado', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'pendente',
    valor_estimado DECIMAL(10,2) NULL,
    observacoes TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_cadastro INT NULL,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo) ON DELETE CASCADE,
    FOREIGN KEY (id_servico) REFERENCES servicos(id_servico) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_cadastro) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_data_agendamento (data_agendamento),
    INDEX idx_status (status),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de ordens de serviÃ§o
CREATE TABLE ordens_servico (
    id_os INT AUTO_INCREMENT PRIMARY KEY,
    codigo_os VARCHAR(20) UNIQUE NOT NULL,
    id_cliente INT NOT NULL,
    id_veiculo INT NOT NULL,
    id_agendamento INT NULL,
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_inicio DATETIME NULL,
    data_conclusao DATETIME NULL,
    status ENUM('aberta', 'em_andamento', 'finalizada', 'cancelada') DEFAULT 'aberta',
    valor_servicos DECIMAL(10,2) DEFAULT 0.00,
    valor_produtos DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    acrescimo DECIMAL(10,2) DEFAULT 0.00,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    forma_pagamento ENUM('dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'transferencia', 'cheque') NULL,
    vaga INT NULL,
    km_veiculo INT NULL,
    observacoes TEXT NULL,
    checklist_avarias JSON NULL COMMENT 'Dados do checklist de avarias em formato JSON',
    usuario_abertura INT NULL,
    usuario_conclusao INT NULL,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT,
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo) ON DELETE RESTRICT,
    FOREIGN KEY (id_agendamento) REFERENCES agendamentos(id_agendamento) ON DELETE SET NULL,
    FOREIGN KEY (usuario_abertura) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (usuario_conclusao) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_codigo_os (codigo_os),
    INDEX idx_status (status),
    INDEX idx_data_abertura (data_abertura),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de relacionamento entre ordens de serviÃ§o e serviÃ§os
CREATE TABLE os_servicos (
    id_os_servico INT AUTO_INCREMENT PRIMARY KEY,
    id_os INT NOT NULL,
    id_servico INT NOT NULL,
    quantidade INT DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE CASCADE,
    FOREIGN KEY (id_servico) REFERENCES servicos(id_servico) ON DELETE RESTRICT,
    INDEX idx_os (id_os)
);

-- Tabela de produtos para estoque e venda
CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    codigo_produto VARCHAR(50) UNIQUE NULL,
    nome_produto VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    categoria VARCHAR(100) NULL,
    unidade_medida ENUM('unidade', 'litro', 'kg', 'metro', 'pacote') DEFAULT 'unidade',
    preco_custo DECIMAL(10,2) DEFAULT 0.00,
    preco_venda DECIMAL(10,2) NOT NULL,
    estoque_atual INT DEFAULT 0,
    estoque_minimo INT DEFAULT 0,
    estoque_maximo INT DEFAULT 0,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo_produto (codigo_produto),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria)
);

-- Tabela de movimentaÃ§Ãµes de estoque
CREATE TABLE movimentacoes_estoque (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    tipo_movimentacao ENUM('entrada', 'saida', 'ajuste', 'perda') NOT NULL,
    quantidade INT NOT NULL,
    valor_unitario DECIMAL(10,2) NULL,
    valor_total DECIMAL(10,2) NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(255) NULL,
    documento VARCHAR(100) NULL COMMENT 'NÃºmero da nota fiscal, ordem de serviÃ§o, etc.',
    usuario_responsavel INT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_produto (id_produto),
    INDEX idx_data_movimentacao (data_movimentacao),
    INDEX idx_tipo_movimentacao (tipo_movimentacao)
);

-- Tabela de relacionamento entre ordens de serviÃ§o e produtos vendidos
CREATE TABLE os_produtos (
    id_os_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_os INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE RESTRICT,
    INDEX idx_os (id_os)
);

-- Tabela de cupons de desconto
CREATE TABLE cupons_desconto (
    id_cupom INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nome_cupom VARCHAR(100) NOT NULL,
    tipo_desconto ENUM('porcentagem', 'valor_fixo') NOT NULL,
    valor_desconto DECIMAL(10,2) NOT NULL,
    valor_minimo_compra DECIMAL(10,2) DEFAULT 0.00,
    data_inicio DATE NOT NULL,
    data_validade DATE NOT NULL,
    usos_maximos INT DEFAULT 0 COMMENT '0 = ilimitado',
    usos_atuais INT DEFAULT 0,
    status ENUM('ativo', 'inativo', 'expirado') DEFAULT 'ativo',
    aplicavel_a ENUM('servicos', 'produtos', 'ambos') DEFAULT 'ambos',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_data_validade (data_validade)
);

-- Tabela de uso de cupons
CREATE TABLE cupons_utilizados (
    id_uso INT AUTO_INCREMENT PRIMARY KEY,
    id_cupom INT NOT NULL,
    id_os INT NOT NULL,
    valor_desconto_aplicado DECIMAL(10,2) NOT NULL,
    data_uso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cupom) REFERENCES cupons_desconto(id_cupom) ON DELETE CASCADE,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE CASCADE,
    INDEX idx_cupom (id_cupom),
    INDEX idx_os (id_os)
);

-- Tabela de transaÃ§Ãµes financeiras
CREATE TABLE financeiro (
    id_transacao INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('receita', 'despesa') NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_transacao DATE NOT NULL,
    data_vencimento DATE NULL,
    forma_pagamento VARCHAR(50) NULL,
    status ENUM('pendente', 'pago', 'cancelado', 'vencido') DEFAULT 'pendente',
    id_os INT NULL COMMENT 'ReferÃªncia Ã  ordem de serviÃ§o se aplicÃ¡vel',
    documento VARCHAR(100) NULL,
    observacoes TEXT NULL,
    usuario_responsavel INT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE SET NULL,
    FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_data_transacao (data_transacao),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria)
);

-- Tabela de orÃ§amentos
CREATE TABLE orcamentos (
    id_orcamento INT AUTO_INCREMENT PRIMARY KEY,
    numero_orcamento VARCHAR(20) UNIQUE NOT NULL,
    id_cliente INT NOT NULL,
    id_veiculo INT NULL,
    data_orcamento DATE NOT NULL,
    data_validade DATE NOT NULL,
    valor_servicos DECIMAL(10,2) DEFAULT 0.00,
    valor_produtos DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    acrescimo DECIMAL(10,2) DEFAULT 0.00,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pendente', 'aprovado', 'rejeitado', 'expirado') DEFAULT 'pendente',
    observacoes TEXT NULL,
    usuario_responsavel INT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo) ON DELETE SET NULL,
    FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_numero_orcamento (numero_orcamento),
    INDEX idx_status (status),
    INDEX idx_data_orcamento (data_orcamento),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de itens do orÃ§amento (serviÃ§os)
CREATE TABLE orcamento_servicos (
    id_orcamento_servico INT AUTO_INCREMENT PRIMARY KEY,
    id_orcamento INT NOT NULL,
    id_servico INT NOT NULL,
    quantidade INT DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_orcamento) REFERENCES orcamentos(id_orcamento) ON DELETE CASCADE,
    FOREIGN KEY (id_servico) REFERENCES servicos(id_servico) ON DELETE RESTRICT,
    INDEX idx_orcamento (id_orcamento)
);

-- Tabela de itens do orÃ§amento (produtos)
CREATE TABLE orcamento_produtos (
    id_orcamento_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_orcamento INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_orcamento) REFERENCES orcamentos(id_orcamento) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE RESTRICT,
    INDEX idx_orcamento (id_orcamento)
);

-- Tabela de notas fiscais
CREATE TABLE notas_fiscais (
    id_nf INT AUTO_INCREMENT PRIMARY KEY,
    numero_nf VARCHAR(50) UNIQUE NOT NULL,
    serie_nf VARCHAR(10) NOT NULL,
    id_os INT NULL,
    id_cliente INT NOT NULL,
    tipo_nf ENUM('NFe', 'NFCe', 'NFSe') NOT NULL,
    data_emissao DATETIME NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    status ENUM('emitida', 'cancelada', 'inutilizada') DEFAULT 'emitida',
    chave_acesso VARCHAR(44) NULL,
    caminho_xml VARCHAR(255) NULL,
    caminho_pdf VARCHAR(255) NULL,
    observacoes TEXT NULL,
    usuario_emissao INT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE SET NULL,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_emissao) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_numero_nf (numero_nf),
    INDEX idx_status (status),
    INDEX idx_data_emissao (data_emissao),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de configuraÃ§Ãµes do sistema
CREATE TABLE configuracoes (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NULL,
    descricao TEXT NULL,
    tipo ENUM('texto', 'numero', 'boolean', 'json') DEFAULT 'texto',
    categoria VARCHAR(50) NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de logs do sistema
CREATE TABLE logs_sistema (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    acao VARCHAR(255) NOT NULL,
    tabela_afetada VARCHAR(100) NULL,
    id_registro INT NULL,
    dados_anteriores JSON NULL,
    dados_novos JSON NULL,
    ip_usuario VARCHAR(45) NULL,
    user_agent TEXT NULL,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_usuario (id_usuario),
    INDEX idx_data_acao (data_acao),
    INDEX idx_tabela_afetada (tabela_afetada)
);

-- InserÃ§Ã£o de dados iniciais

-- Inserir usuÃ¡rio administrador padrÃ£o
INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES 
('Administrador', 'admin@lavajato.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir categorias de serviÃ§os padrÃ£o
INSERT INTO categorias_servicos (nome_categoria, descricao) VALUES 
('Lavagem Simples', 'ServiÃ§os bÃ¡sicos de lavagem externa'),
('Lavagem Completa', 'ServiÃ§os completos incluindo enceramento'),
('HigienizaÃ§Ã£o', 'ServiÃ§os de limpeza interna e higienizaÃ§Ã£o'),
('EstÃ©tica Automotiva', 'ServiÃ§os especializados de estÃ©tica'),
('ServiÃ§os Especiais', 'ServiÃ§os diferenciados e personalizados');

-- Inserir serviÃ§os padrÃ£o
INSERT INTO servicos (id_categoria, nome_servico, descricao, preco, duracao_estimada, tipo_veiculo) VALUES 
(1, 'Lavagem Simples Carro', 'Lavagem externa bÃ¡sica para carros', 15.00, 30, 'carro'),
(1, 'Lavagem Simples Moto', 'Lavagem externa bÃ¡sica para motos', 10.00, 20, 'moto'),
(1, 'Lavagem Simples CaminhÃ£o', 'Lavagem externa bÃ¡sica para caminhÃµes', 35.00, 60, 'caminhao'),
(2, 'Lavagem Completa Carro', 'Lavagem completa com enceramento para carros', 25.00, 45, 'carro'),
(2, 'Lavagem Completa Moto', 'Lavagem completa com enceramento para motos', 18.00, 35, 'moto'),
(3, 'HigienizaÃ§Ã£o Interna', 'Limpeza e higienizaÃ§Ã£o do interior do veÃ­culo', 30.00, 60, 'todos'),
(4, 'Enceramento', 'AplicaÃ§Ã£o de cera protetora', 20.00, 30, 'todos'),
(4, 'Polimento', 'Polimento da pintura', 80.00, 120, 'todos');

-- Inserir produtos padrÃ£o
INSERT INTO produtos (nome_produto, descricao, categoria, unidade_medida, preco_custo, preco_venda, estoque_atual, estoque_minimo) VALUES 
('Shampoo Automotivo 1L', 'Shampoo concentrado para lavagem', 'Produtos de Limpeza', 'litro', 8.50, 15.00, 50, 10),
('Cera LÃ­quida 500ml', 'Cera lÃ­quida para proteÃ§Ã£o da pintura', 'Produtos de Limpeza', 'unidade', 12.00, 22.00, 30, 5),
('Pano Microfibra', 'Pano de microfibra para secagem', 'AcessÃ³rios', 'unidade', 5.00, 12.00, 100, 20),
('Aromatizante', 'Aromatizante para veÃ­culos', 'AcessÃ³rios', 'unidade', 2.50, 8.00, 200, 50);

-- Inserir configuraÃ§Ãµes padrÃ£o
INSERT INTO configuracoes (chave, valor, descricao, tipo, categoria) VALUES 
('nome_empresa', 'LJ-OS Sistema para Lava Jato', 'Nome da empresa', 'texto', 'geral'),
('cnpj_empresa', '00.000.000/0001-00', 'CNPJ da empresa', 'texto', 'geral'),
('endereco_empresa', 'Rua Exemplo, 123 - Centro', 'EndereÃ§o da empresa', 'texto', 'geral'),
('telefone_empresa', '(11) 99999-9999', 'Telefone da empresa', 'texto', 'geral'),
('email_empresa', 'contato@lavajato.com', 'E-mail da empresa', 'texto', 'geral'),
('whatsapp_token', '', 'Token da API do WhatsApp', 'texto', 'integracoes'),
('sms_token', '', 'Token da API de SMS', 'texto', 'integracoes'),
('backup_automatico', '1', 'Ativar backup automÃ¡tico', 'boolean', 'sistema'),
('moeda_padrao', 'BRL', 'Moeda padrÃ£o do sistema', 'texto', 'financeiro');

-- Cliente teste para Ã¡rea do cliente
INSERT INTO clientes (nome, tipo_pessoa, cpf_cnpj, telefone, email, endereco, cidade, estado, cep, status) VALUES 
('JoÃ£o Silva Teste', 'PF', '123.456.789-00', '(11) 99999-8888', 'joao.teste@email.com', 'Rua Teste, 456 - Bairro Teste', 'SÃ£o Paulo', 'SP', '01234-567', 'ativo');

-- VeÃ­culos do cliente teste
INSERT INTO veiculos (id_cliente, placa, marca, modelo, ano, ano_modelo, cor, combustivel, km_atual, observacoes, status) VALUES 
(1, 'ABC-1234', 'Toyota', 'Corolla', 2020, 2020, 'Prata', 'flex', 45000, 'VeÃ­culo em Ã³timo estado', 'ativo'),
(1, 'XYZ-5678', 'Honda', 'Civic', 2019, 2019, 'Preto', 'flex', 38000, 'VeÃ­culo familiar', 'ativo');

-- Agendamentos de teste
INSERT INTO agendamentos (id_cliente, id_veiculo, id_servico, data_agendamento, vaga, valor_estimado, observacoes, status, usuario_cadastro) VALUES 
(1, 1, 2, DATE_ADD(NOW(), INTERVAL 2 DAY), 1, 25.00, 'Cliente solicitou atenÃ§Ã£o especial', 'confirmado', 1),
(1, 2, 1, DATE_ADD(NOW(), INTERVAL 5 DAY), 2, 15.00, 'Lavagem simples', 'pendente', 1);

-- Ordens de serviÃ§o de teste
INSERT INTO ordens_servico (id_cliente, id_veiculo, numero_os, data_criacao, valor_total, status, observacoes, usuario_responsavel) VALUES 
(1, 1, 'OS-2024-001', DATE_SUB(NOW(), INTERVAL 7 DAY), 25.00, 'concluido', 'ServiÃ§o realizado com sucesso', 1),
(1, 2, 'OS-2024-002', DATE_SUB(NOW(), INTERVAL 3 DAY), 80.00, 'concluido', 'Polimento completo', 1);

-- Tabelas para Ã¡rea do cliente
CREATE TABLE logs_acesso_cliente (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    ip_acesso VARCHAR(45),
    user_agent TEXT,
    data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
);

CREATE TABLE creditos_cliente (
    id_credito INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    tipo ENUM('ganho', 'resgate', 'bonus') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    descricao TEXT,
    id_ordem_servico INT NULL,
    data_credito TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_ordem_servico) REFERENCES ordens_servico(id_os) ON DELETE SET NULL
);

CREATE TABLE avaliacoes_servico (
    id_avaliacao INT PRIMARY KEY AUTO_INCREMENT,
    id_ordem_servico INT NOT NULL,
    id_cliente INT NOT NULL,
    nota INT(1) NOT NULL CHECK (nota >= 1 AND nota <= 5),
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ordem_servico) REFERENCES ordens_servico(id_os) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
);

-- CrÃ©ditos do cliente teste
INSERT INTO creditos_cliente (id_cliente, tipo, valor, descricao, id_ordem_servico) VALUES 
(1, 'ganho', 2.50, 'Cashback da OS-2024-001 (10%)', 1),
(1, 'ganho', 8.00, 'Cashback da OS-2024-002 (10%)', 2),
(1, 'bonus', 10.00, 'BÃ´nus de primeira compra', NULL); 
-- =====================================================
-- ESTRUTURA DO BANCO DE DADOS - NOVAS FUNCIONALIDADES
-- LJ-OS Sistema para Lava Jato
-- =====================================================

-- =====================================================
-- TABELA DE FUNCIONÃRIOS
-- =====================================================
CREATE TABLE IF NOT EXISTS funcionarios (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cargo ENUM('lavador', 'atendente', 'supervisor', 'gerente') NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    data_admissao DATE NOT NULL,
    salario DECIMAL(10,2) DEFAULT 0.00,
    comissao DECIMAL(5,2) DEFAULT 0.00,
    endereco TEXT,
    observacoes TEXT,
    status ENUM('ativo', 'inativo', 'ferias', 'licenca') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABELA DE CONTROLE DE PRESENÃ‡A
-- =====================================================
CREATE TABLE IF NOT EXISTS presenca_funcionarios (
    id_presenca INT AUTO_INCREMENT PRIMARY KEY,
    id_funcionario INT NOT NULL,
    data DATE NOT NULL,
    hora TIME NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    observacoes TEXT,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario) ON DELETE CASCADE,
    UNIQUE KEY unique_presenca (id_funcionario, data, tipo)
);

-- =====================================================
-- TABELA DE VENDAS
-- =====================================================
CREATE TABLE IF NOT EXISTS vendas (
    id_venda INT AUTO_INCREMENT PRIMARY KEY,
    id_funcionario INT NOT NULL,
    id_cliente INT,
    data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    forma_pagamento ENUM('dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'transferencia') NOT NULL,
    status ENUM('pendente', 'paga', 'cancelada') DEFAULT 'pendente',
    observacoes TEXT,
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

-- =====================================================
-- TABELA DE PRODUTOS VENDIDOS
-- =====================================================
CREATE TABLE IF NOT EXISTS vendas_produtos (
    id_venda_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_venda INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venda) REFERENCES vendas(id_venda) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
);

-- =====================================================
-- TABELA DE PERFIS DE ACESSO
-- =====================================================
CREATE TABLE IF NOT EXISTS perfis_acesso (
    id_perfil INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABELA DE PERMISSÃ•ES DE USUÃRIOS
-- =====================================================
CREATE TABLE IF NOT EXISTS permissoes (
    id_permissao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    funcionalidade VARCHAR(50) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    UNIQUE KEY unique_permissao (id_usuario, modulo, funcionalidade)
);

-- =====================================================
-- TABELA DE PERMISSÃ•ES DE PERFIS
-- =====================================================
CREATE TABLE IF NOT EXISTS permissoes_perfil (
    id_permissao_perfil INT AUTO_INCREMENT PRIMARY KEY,
    id_perfil INT NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    funcionalidade VARCHAR(50) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_perfil) REFERENCES perfis_acesso(id_perfil) ON DELETE CASCADE,
    UNIQUE KEY unique_permissao_perfil (id_perfil, modulo, funcionalidade)
);

-- =====================================================
-- TABELA DE LOGS DE ACESSO
-- =====================================================
CREATE TABLE IF NOT EXISTS logs_acesso (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    acao VARCHAR(50) NOT NULL,
    modulo VARCHAR(50),
    descricao TEXT,
    ip VARCHAR(45),
    user_agent TEXT,
    dados_adicionais JSON,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- =====================================================
-- TABELA DE ORÃ‡AMENTOS
-- =====================================================
CREATE TABLE IF NOT EXISTS orcamentos (
    id_orcamento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_veiculo INT,
    id_funcionario INT,
    numero_orcamento VARCHAR(20) UNIQUE NOT NULL,
    data_orcamento DATE NOT NULL,
    validade_ate DATE NOT NULL,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    valor_final DECIMAL(10,2) DEFAULT 0.00,
    observacoes TEXT,
    status ENUM('pendente', 'aprovado', 'rejeitado', 'expirado', 'convertido') DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo),
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario)
);

-- =====================================================
-- TABELA DE ITENS DO ORÃ‡AMENTO
-- =====================================================
CREATE TABLE IF NOT EXISTS orcamentos_itens (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_orcamento INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    FOREIGN KEY (id_orcamento) REFERENCES orcamentos(id_orcamento) ON DELETE CASCADE
);

-- =====================================================
-- ADICIONAR COLUNAS NAS TABELAS EXISTENTES
-- =====================================================

-- Adicionar coluna de funcionÃ¡rio nas ordens de serviÃ§o
ALTER TABLE ordens_servico 
ADD COLUMN id_funcionario INT,
ADD FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario);

-- Adicionar coluna de perfil nos usuÃ¡rios
ALTER TABLE usuarios 
ADD COLUMN id_perfil INT,
ADD FOREIGN KEY (id_perfil) REFERENCES perfis_acesso(id_perfil);

-- =====================================================
-- INSERIR DADOS INICIAIS
-- =====================================================

-- Inserir perfis padrÃ£o
INSERT INTO perfis_acesso (nome, descricao) VALUES
('Administrador', 'Acesso total ao sistema'),
('Gerente', 'Acesso gerencial com algumas restriÃ§Ãµes'),
('Atendente', 'Acesso bÃ¡sico para atendimento'),
('Lavador', 'Acesso limitado para registro de serviÃ§os');

-- Inserir funcionÃ¡rio padrÃ£o (administrador)
INSERT INTO funcionarios (nome, cargo, cpf, telefone, email, data_admissao, salario, status) VALUES
('Administrador do Sistema', 'gerente', '000.000.000-00', '(00) 00000-0000', 'admin@lj-os.com', CURDATE(), 0.00, 'ativo');

-- =====================================================
-- ÃNDICES PARA OTIMIZAÃ‡ÃƒO
-- =====================================================

-- Ãndices para funcionÃ¡rios
CREATE INDEX idx_funcionarios_status ON funcionarios(status);
CREATE INDEX idx_funcionarios_cargo ON funcionarios(cargo);
CREATE INDEX idx_funcionarios_data_admissao ON funcionarios(data_admissao);

-- Ãndices para presenÃ§a
CREATE INDEX idx_presenca_data ON presenca_funcionarios(data);
CREATE INDEX idx_presenca_funcionario_data ON presenca_funcionarios(id_funcionario, data);

-- Ãndices para vendas
CREATE INDEX idx_vendas_data ON vendas(data_venda);
CREATE INDEX idx_vendas_funcionario ON vendas(id_funcionario);
CREATE INDEX idx_vendas_status ON vendas(status);

-- Ãndices para permissÃµes
CREATE INDEX idx_permissoes_usuario ON permissoes(id_usuario);
CREATE INDEX idx_permissoes_modulo ON permissoes(modulo);
CREATE INDEX idx_permissoes_ativas ON permissoes(ativo);

-- Ãndices para logs
CREATE INDEX idx_logs_usuario ON logs_acesso(id_usuario);
CREATE INDEX idx_logs_data ON logs_acesso(data_hora);
CREATE INDEX idx_logs_acao ON logs_acesso(acao);

-- Ãndices para orÃ§amentos
CREATE INDEX idx_orcamentos_cliente ON orcamentos(id_cliente);
CREATE INDEX idx_orcamentos_data ON orcamentos(data_orcamento);
CREATE INDEX idx_orcamentos_status ON orcamentos(status);
CREATE INDEX idx_orcamentos_numero ON orcamentos(numero_orcamento);

-- =====================================================
-- TRIGGERS PARA AUTOMATIZAÃ‡ÃƒO
-- =====================================================

-- Trigger para calcular subtotal em vendas_produtos
DELIMITER //
CREATE TRIGGER calcular_subtotal_venda_produto
BEFORE INSERT ON vendas_produtos
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.quantidade * NEW.preco_unitario;
END//

CREATE TRIGGER atualizar_subtotal_venda_produto
BEFORE UPDATE ON vendas_produtos
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.quantidade * NEW.preco_unitario;
END//
DELIMITER ;

-- Trigger para atualizar valor total da venda
DELIMITER //
CREATE TRIGGER atualizar_valor_total_venda
AFTER INSERT ON vendas_produtos
FOR EACH ROW
BEGIN
    UPDATE vendas 
    SET valor_total = (
        SELECT SUM(subtotal) 
        FROM vendas_produtos 
        WHERE id_venda = NEW.id_venda
    )
    WHERE id_venda = NEW.id_venda;
END//

CREATE TRIGGER atualizar_valor_total_venda_update
AFTER UPDATE ON vendas_produtos
FOR EACH ROW
BEGIN
    UPDATE vendas 
    SET valor_total = (
        SELECT SUM(subtotal) 
        FROM vendas_produtos 
        WHERE id_venda = NEW.id_venda
    )
    WHERE id_venda = NEW.id_venda;
END//

CREATE TRIGGER atualizar_valor_total_venda_delete
AFTER DELETE ON vendas_produtos
FOR EACH ROW
BEGIN
    UPDATE vendas 
    SET valor_total = (
        SELECT COALESCE(SUM(subtotal), 0) 
        FROM vendas_produtos 
        WHERE id_venda = OLD.id_venda
    )
    WHERE id_venda = OLD.id_venda;
END//
DELIMITER ;

-- Trigger para calcular valor final do orÃ§amento
DELIMITER //
CREATE TRIGGER calcular_valor_final_orcamento
BEFORE INSERT ON orcamentos
FOR EACH ROW
BEGIN
    SET NEW.valor_final = NEW.valor_total - NEW.desconto;
END//

CREATE TRIGGER atualizar_valor_final_orcamento
BEFORE UPDATE ON orcamentos
FOR EACH ROW
BEGIN
    SET NEW.valor_final = NEW.valor_total - NEW.desconto;
END//
DELIMITER ;

-- Trigger para calcular subtotal em orÃ§amentos_itens
DELIMITER //
CREATE TRIGGER calcular_subtotal_orcamento_item
BEFORE INSERT ON orcamentos_itens
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.quantidade * NEW.preco_unitario;
END//

CREATE TRIGGER atualizar_subtotal_orcamento_item
BEFORE UPDATE ON orcamentos_itens
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.quantidade * NEW.preco_unitario;
END//
DELIMITER ;

-- =====================================================
-- VIEWS ÃšTEIS
-- =====================================================

-- View para funcionÃ¡rios com estatÃ­sticas
CREATE VIEW vw_funcionarios_estatisticas AS
SELECT 
    f.*,
    COUNT(DISTINCT p.data) as dias_trabalhados,
    COUNT(os.id_os) as total_ordens_servico,
    SUM(os.valor_total) as valor_gerado_total,
    AVG(os.valor_total) as ticket_medio
FROM funcionarios f
LEFT JOIN presenca_funcionarios p ON f.id_funcionario = p.id_funcionario AND p.tipo = 'entrada'
LEFT JOIN ordens_servico os ON f.id_funcionario = os.id_funcionario AND os.status = 'finalizada'
WHERE f.status = 'ativo'
GROUP BY f.id_funcionario;

-- View para permissÃµes de usuÃ¡rios
CREATE VIEW vw_permissoes_usuarios AS
SELECT 
    u.id_usuario,
    u.nome as usuario_nome,
    u.email,
    p.nome as perfil_nome,
    perm.modulo,
    perm.funcionalidade,
    perm.ativo
FROM usuarios u
LEFT JOIN perfis_acesso p ON u.id_perfil = p.id_perfil
LEFT JOIN permissoes perm ON u.id_usuario = perm.id_usuario
WHERE u.status = 'ativo';

-- View para vendas com detalhes
CREATE VIEW vw_vendas_detalhadas AS
SELECT 
    v.*,
    f.nome as funcionario_nome,
    c.nome as cliente_nome,
    COUNT(vp.id_produto) as total_produtos,
    SUM(vp.quantidade) as total_itens
FROM vendas v
LEFT JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
LEFT JOIN vendas_produtos vp ON v.id_venda = vp.id_venda
GROUP BY v.id_venda;

-- =====================================================
-- PROCEDURES ÃšTEIS
-- =====================================================

-- Procedure para registrar presenÃ§a automÃ¡tica
DELIMITER //
CREATE PROCEDURE RegistrarPresenca(
    IN p_id_funcionario INT,
    IN p_tipo ENUM('entrada', 'saida')
)
BEGIN
    DECLARE v_data_atual DATE;
    DECLARE v_hora_atual TIME;
    
    SET v_data_atual = CURDATE();
    SET v_hora_atual = CURTIME();
    
    INSERT INTO presenca_funcionarios (id_funcionario, data, hora, tipo)
    VALUES (p_id_funcionario, v_data_atual, v_hora_atual, p_tipo)
    ON DUPLICATE KEY UPDATE 
        hora = v_hora_atual,
        data_registro = CURRENT_TIMESTAMP;
        
    SELECT 'PresenÃ§a registrada com sucesso' as mensagem;
END//
DELIMITER ;

-- Procedure para gerar relatÃ³rio de produtividade
DELIMITER //
CREATE PROCEDURE RelatorioProdutividade(
    IN p_data_inicio DATE,
    IN p_data_fim DATE,
    IN p_id_funcionario INT
)
BEGIN
    SELECT 
        f.nome as funcionario,
        COUNT(DISTINCT p.data) as dias_trabalhados,
        COUNT(os.id_os) as ordens_finalizadas,
        SUM(os.valor_total) as valor_gerado,
        AVG(os.valor_total) as ticket_medio,
        SUM(TIMESTAMPDIFF(HOUR, os.data_abertura, os.data_fechamento)) as horas_trabalhadas
    FROM funcionarios f
    LEFT JOIN presenca_funcionarios p ON f.id_funcionario = p.id_funcionario 
        AND p.data BETWEEN p_data_inicio AND p_data_fim
        AND p.tipo = 'entrada'
    LEFT JOIN ordens_servico os ON f.id_funcionario = os.id_funcionario 
        AND DATE(os.data_abertura) BETWEEN p_data_inicio AND p_data_fim
        AND os.status = 'finalizada'
    WHERE f.status = 'ativo'
        AND (p_id_funcionario IS NULL OR f.id_funcionario = p_id_funcionario)
    GROUP BY f.id_funcionario, f.nome
    ORDER BY valor_gerado DESC;
END//
DELIMITER ;

-- =====================================================
-- FIM DA ESTRUTURA
-- ===================================================== 
-- =====================================================
-- TABELAS DO MÃ“DULO FINANCEIRO
-- LJ-OS Sistema para Lava Jato
-- =====================================================

-- =====================================================
-- TABELA DE CATEGORIAS FINANCEIRAS
-- =====================================================
CREATE TABLE IF NOT EXISTS categorias_financeiras (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('receita', 'despesa', 'ambos') NOT NULL DEFAULT 'ambos',
    descricao TEXT NULL,
    cor VARCHAR(7) DEFAULT '#007bff' COMMENT 'Cor em hexadecimal para identificaÃ§Ã£o visual',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_status (status)
);

-- =====================================================
-- TABELA DE MOVIMENTAÃ‡Ã•ES FINANCEIRAS
-- =====================================================
CREATE TABLE IF NOT EXISTS movimentacoes_financeiras (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('receita', 'despesa') NOT NULL,
    id_categoria INT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_movimentacao DATE NOT NULL,
    data_vencimento DATE NULL,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'transferencia', 'boleto', 'cheque') NULL,
    status ENUM('pendente', 'pago', 'cancelado', 'vencido') DEFAULT 'pago',
    id_os INT NULL COMMENT 'ReferÃªncia Ã  ordem de serviÃ§o se aplicÃ¡vel',
    documento VARCHAR(100) NULL COMMENT 'NÃºmero da nota fiscal, recibo, etc.',
    observacoes TEXT NULL,
    usuario_responsavel INT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_financeiras(id_categoria) ON DELETE SET NULL,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE SET NULL,
    FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_data_movimentacao (data_movimentacao),
    INDEX idx_status (status),
    INDEX idx_categoria (id_categoria),
    INDEX idx_os (id_os)
);

-- =====================================================
-- INSERIR CATEGORIAS PADRÃƒO
-- =====================================================

-- Categorias de Receita
INSERT INTO categorias_financeiras (nome, tipo, descricao, cor) VALUES
('Lavagem de VeÃ­culos', 'receita', 'Receitas provenientes de serviÃ§os de lavagem', '#28a745'),
('Venda de Produtos', 'receita', 'Receitas provenientes da venda de produtos', '#17a2b8'),
('ServiÃ§os Especiais', 'receita', 'Receitas de serviÃ§os diferenciados', '#ffc107'),
('Outras Receitas', 'receita', 'Outras receitas diversas', '#6f42c1');

-- Categorias de Despesa
INSERT INTO categorias_financeiras (nome, tipo, descricao, cor) VALUES
('Fornecedores', 'despesa', 'Pagamentos a fornecedores de produtos', '#dc3545'),
('FuncionÃ¡rios', 'despesa', 'SalÃ¡rios, comissÃµes e benefÃ­cios', '#fd7e14'),
('Aluguel', 'despesa', 'Aluguel do estabelecimento', '#e83e8c'),
('Contas PÃºblicas', 'despesa', 'Ãgua, luz, telefone, internet', '#6c757d'),
('ManutenÃ§Ã£o', 'despesa', 'ManutenÃ§Ã£o de equipamentos e instalaÃ§Ãµes', '#495057'),
('Marketing', 'despesa', 'Publicidade e propaganda', '#20c997'),
('Impostos', 'despesa', 'Impostos e taxas', '#343a40'),
('Outras Despesas', 'despesa', 'Outras despesas diversas', '#6c757d');

-- =====================================================
-- ÃNDICES ADICIONAIS PARA OTIMIZAÃ‡ÃƒO
-- =====================================================

-- Ãndices para consultas frequentes
CREATE INDEX idx_movimentacoes_tipo_data ON movimentacoes_financeiras(tipo, data_movimentacao);
CREATE INDEX idx_movimentacoes_status_data ON movimentacoes_financeiras(status, data_movimentacao);
CREATE INDEX idx_movimentacoes_categoria_tipo ON movimentacoes_financeiras(id_categoria, tipo);

-- =====================================================
-- TRIGGERS PARA AUTOMATIZAÃ‡ÃƒO
-- =====================================================

-- Trigger para atualizar data_atualizacao
DELIMITER //
CREATE TRIGGER atualizar_data_movimentacao
BEFORE UPDATE ON movimentacoes_financeiras
FOR EACH ROW
BEGIN
    SET NEW.data_atualizacao = CURRENT_TIMESTAMP;
END//
DELIMITER ;

-- =====================================================
-- VIEWS ÃšTEIS PARA RELATÃ“RIOS
-- =====================================================

-- View para resumo financeiro mensal
CREATE VIEW vw_resumo_financeiro_mensal AS
SELECT 
    DATE_FORMAT(data_movimentacao, '%Y-%m') as mes_ano,
    SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as total_receitas,
    SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as total_despesas,
    SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) - SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as saldo,
    COUNT(CASE WHEN tipo = 'receita' THEN 1 END) as qtd_receitas,
    COUNT(CASE WHEN tipo = 'despesa' THEN 1 END) as qtd_despesas
FROM movimentacoes_financeiras
WHERE status = 'pago'
GROUP BY DATE_FORMAT(data_movimentacao, '%Y-%m')
ORDER BY mes_ano DESC;

-- View para movimentaÃ§Ãµes com detalhes das categorias
CREATE VIEW vw_movimentacoes_detalhadas AS
SELECT 
    mf.*,
    cf.nome as categoria_nome,
    cf.cor as categoria_cor,
    u.nome as usuario_nome,
    os.codigo_os
FROM movimentacoes_financeiras mf
LEFT JOIN categorias_financeiras cf ON mf.id_categoria = cf.id_categoria
LEFT JOIN usuarios u ON mf.usuario_responsavel = u.id_usuario
LEFT JOIN ordens_servico os ON mf.id_os = os.id_os
ORDER BY mf.data_movimentacao DESC;

-- =====================================================
-- PROCEDURES ÃšTEIS
-- =====================================================

-- Procedure para gerar relatÃ³rio financeiro por perÃ­odo
DELIMITER //
CREATE PROCEDURE RelatorioFinanceiro(
    IN p_data_inicio DATE,
    IN p_data_fim DATE,
    IN p_tipo VARCHAR(10)
)
BEGIN
    SELECT 
        cf.nome as categoria,
        cf.cor,
        SUM(mf.valor) as total,
        COUNT(*) as quantidade,
        AVG(mf.valor) as valor_medio
    FROM movimentacoes_financeiras mf
    LEFT JOIN categorias_financeiras cf ON mf.id_categoria = cf.id_categoria
    WHERE mf.data_movimentacao BETWEEN p_data_inicio AND p_data_fim
        AND mf.status = 'pago'
        AND (p_tipo = 'ambos' OR mf.tipo = p_tipo)
    GROUP BY cf.id_categoria, cf.nome, cf.cor
    ORDER BY total DESC;
END//
DELIMITER ;

-- Procedure para calcular fluxo de caixa
DELIMITER //
CREATE PROCEDURE FluxoCaixa(
    IN p_data_inicio DATE,
    IN p_data_fim DATE
)
BEGIN
    SELECT 
        data_movimentacao,
        SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as receitas,
        SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as despesas,
        SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END) as saldo_dia
    FROM movimentacoes_financeiras
    WHERE data_movimentacao BETWEEN p_data_inicio AND p_data_fim
        AND status = 'pago'
    GROUP BY data_movimentacao
    ORDER BY data_movimentacao;
END//
DELIMITER ;

-- =====================================================
-- FIM DAS TABELAS FINANCEIRO
-- ===================================================== 
