-- Script de criação do banco de dados para LJ-OS Sistema para Lava Jato
-- Sistema completo de gestão para lava jatos

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS lava_jato_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lava_jato_db;

-- Tabela de usuários do sistema
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

-- Tabela de clientes (pessoas físicas e jurídicas)
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

-- Tabela de veículos
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

-- Tabela de categorias de serviços
CREATE TABLE categorias_servicos (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo'
);

-- Tabela de serviços oferecidos
CREATE TABLE servicos (
    id_servico INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NULL,
    nome_servico VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL,
    duracao_estimada INT NOT NULL COMMENT 'Duração em minutos',
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
    vaga INT NULL COMMENT 'Número da vaga onde será realizado o serviço',
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

-- Tabela de ordens de serviço
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

-- Tabela de relacionamento entre ordens de serviço e serviços
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

-- Tabela de movimentações de estoque
CREATE TABLE movimentacoes_estoque (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    tipo_movimentacao ENUM('entrada', 'saida', 'ajuste', 'perda') NOT NULL,
    quantidade INT NOT NULL,
    valor_unitario DECIMAL(10,2) NULL,
    valor_total DECIMAL(10,2) NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(255) NULL,
    documento VARCHAR(100) NULL COMMENT 'Número da nota fiscal, ordem de serviço, etc.',
    usuario_responsavel INT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_produto (id_produto),
    INDEX idx_data_movimentacao (data_movimentacao),
    INDEX idx_tipo_movimentacao (tipo_movimentacao)
);

-- Tabela de relacionamento entre ordens de serviço e produtos vendidos
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

-- Tabela de transações financeiras
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
    id_os INT NULL COMMENT 'Referência à ordem de serviço se aplicável',
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

-- Tabela de orçamentos
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

-- Tabela de itens do orçamento (serviços)
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

-- Tabela de itens do orçamento (produtos)
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

-- Tabela de configurações do sistema
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

-- Inserção de dados iniciais

-- Inserir usuário administrador padrão
INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES 
('Administrador', 'admin@lavajato.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir categorias de serviços padrão
INSERT INTO categorias_servicos (nome_categoria, descricao) VALUES 
('Lavagem Simples', 'Serviços básicos de lavagem externa'),
('Lavagem Completa', 'Serviços completos incluindo enceramento'),
('Higienização', 'Serviços de limpeza interna e higienização'),
('Estética Automotiva', 'Serviços especializados de estética'),
('Serviços Especiais', 'Serviços diferenciados e personalizados');

-- Inserir serviços padrão
INSERT INTO servicos (id_categoria, nome_servico, descricao, preco, duracao_estimada, tipo_veiculo) VALUES 
(1, 'Lavagem Simples Carro', 'Lavagem externa básica para carros', 15.00, 30, 'carro'),
(1, 'Lavagem Simples Moto', 'Lavagem externa básica para motos', 10.00, 20, 'moto'),
(1, 'Lavagem Simples Caminhão', 'Lavagem externa básica para caminhões', 35.00, 60, 'caminhao'),
(2, 'Lavagem Completa Carro', 'Lavagem completa com enceramento para carros', 25.00, 45, 'carro'),
(2, 'Lavagem Completa Moto', 'Lavagem completa com enceramento para motos', 18.00, 35, 'moto'),
(3, 'Higienização Interna', 'Limpeza e higienização do interior do veículo', 30.00, 60, 'todos'),
(4, 'Enceramento', 'Aplicação de cera protetora', 20.00, 30, 'todos'),
(4, 'Polimento', 'Polimento da pintura', 80.00, 120, 'todos');

-- Inserir produtos padrão
INSERT INTO produtos (nome_produto, descricao, categoria, unidade_medida, preco_custo, preco_venda, estoque_atual, estoque_minimo) VALUES 
('Shampoo Automotivo 1L', 'Shampoo concentrado para lavagem', 'Produtos de Limpeza', 'litro', 8.50, 15.00, 50, 10),
('Cera Líquida 500ml', 'Cera líquida para proteção da pintura', 'Produtos de Limpeza', 'unidade', 12.00, 22.00, 30, 5),
('Pano Microfibra', 'Pano de microfibra para secagem', 'Acessórios', 'unidade', 5.00, 12.00, 100, 20),
('Aromatizante', 'Aromatizante para veículos', 'Acessórios', 'unidade', 2.50, 8.00, 200, 50);

-- Inserir configurações padrão
INSERT INTO configuracoes (chave, valor, descricao, tipo, categoria) VALUES 
('nome_empresa', 'LJ-OS Sistema para Lava Jato', 'Nome da empresa', 'texto', 'geral'),
('cnpj_empresa', '00.000.000/0001-00', 'CNPJ da empresa', 'texto', 'geral'),
('endereco_empresa', 'Rua Exemplo, 123 - Centro', 'Endereço da empresa', 'texto', 'geral'),
('telefone_empresa', '(11) 99999-9999', 'Telefone da empresa', 'texto', 'geral'),
('email_empresa', 'contato@lavajato.com', 'E-mail da empresa', 'texto', 'geral'),
('whatsapp_token', '', 'Token da API do WhatsApp', 'texto', 'integracoes'),
('sms_token', '', 'Token da API de SMS', 'texto', 'integracoes'),
('backup_automatico', '1', 'Ativar backup automático', 'boolean', 'sistema'),
('moeda_padrao', 'BRL', 'Moeda padrão do sistema', 'texto', 'financeiro');

-- Cliente teste para área do cliente
INSERT INTO clientes (nome, tipo_pessoa, cpf_cnpj, telefone, email, endereco, cidade, estado, cep, status) VALUES 
('João Silva Teste', 'PF', '123.456.789-00', '(11) 99999-8888', 'joao.teste@email.com', 'Rua Teste, 456 - Bairro Teste', 'São Paulo', 'SP', '01234-567', 'ativo');

-- Veículos do cliente teste
INSERT INTO veiculos (id_cliente, placa, marca, modelo, ano, ano_modelo, cor, combustivel, km_atual, observacoes, status) VALUES 
(1, 'ABC-1234', 'Toyota', 'Corolla', 2020, 2020, 'Prata', 'flex', 45000, 'Veículo em ótimo estado', 'ativo'),
(1, 'XYZ-5678', 'Honda', 'Civic', 2019, 2019, 'Preto', 'flex', 38000, 'Veículo familiar', 'ativo');

-- Agendamentos de teste
INSERT INTO agendamentos (id_cliente, id_veiculo, id_servico, data_agendamento, vaga, valor_estimado, observacoes, status, usuario_cadastro) VALUES 
(1, 1, 2, DATE_ADD(NOW(), INTERVAL 2 DAY), 1, 25.00, 'Cliente solicitou atenção especial', 'confirmado', 1),
(1, 2, 1, DATE_ADD(NOW(), INTERVAL 5 DAY), 2, 15.00, 'Lavagem simples', 'pendente', 1);

-- Ordens de serviço de teste
INSERT INTO ordens_servico (id_cliente, id_veiculo, numero_os, data_criacao, valor_total, status, observacoes, usuario_responsavel) VALUES 
(1, 1, 'OS-2024-001', DATE_SUB(NOW(), INTERVAL 7 DAY), 25.00, 'concluido', 'Serviço realizado com sucesso', 1),
(1, 2, 'OS-2024-002', DATE_SUB(NOW(), INTERVAL 3 DAY), 80.00, 'concluido', 'Polimento completo', 1);

-- Tabelas para área do cliente
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

-- Créditos do cliente teste
INSERT INTO creditos_cliente (id_cliente, tipo, valor, descricao, id_ordem_servico) VALUES 
(1, 'ganho', 2.50, 'Cashback da OS-2024-001 (10%)', 1),
(1, 'ganho', 8.00, 'Cashback da OS-2024-002 (10%)', 2),
(1, 'bonus', 10.00, 'Bônus de primeira compra', NULL); 