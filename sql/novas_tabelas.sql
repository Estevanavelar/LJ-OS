-- =====================================================
-- ESTRUTURA DO BANCO DE DADOS - NOVAS FUNCIONALIDADES
-- LJ-OS Sistema para Lava Jato
-- =====================================================

-- =====================================================
-- TABELA DE FUNCIONÁRIOS
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
-- TABELA DE CONTROLE DE PRESENÇA
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
-- TABELA DE PERMISSÕES DE USUÁRIOS
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
-- TABELA DE PERMISSÕES DE PERFIS
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
-- TABELA DE ORÇAMENTOS
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
-- TABELA DE ITENS DO ORÇAMENTO
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

-- Adicionar coluna de funcionário nas ordens de serviço
ALTER TABLE ordens_servico 
ADD COLUMN id_funcionario INT,
ADD FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario);

-- Adicionar coluna de perfil nos usuários
ALTER TABLE usuarios 
ADD COLUMN id_perfil INT,
ADD FOREIGN KEY (id_perfil) REFERENCES perfis_acesso(id_perfil);

-- =====================================================
-- INSERIR DADOS INICIAIS
-- =====================================================

-- Inserir perfis padrão
INSERT INTO perfis_acesso (nome, descricao) VALUES
('Administrador', 'Acesso total ao sistema'),
('Gerente', 'Acesso gerencial com algumas restrições'),
('Atendente', 'Acesso básico para atendimento'),
('Lavador', 'Acesso limitado para registro de serviços');

-- Inserir funcionário padrão (administrador)
INSERT INTO funcionarios (nome, cargo, cpf, telefone, email, data_admissao, salario, status) VALUES
('Administrador do Sistema', 'gerente', '000.000.000-00', '(00) 00000-0000', 'admin@lj-os.com', CURDATE(), 0.00, 'ativo');

-- =====================================================
-- ÍNDICES PARA OTIMIZAÇÃO
-- =====================================================

-- Índices para funcionários
CREATE INDEX idx_funcionarios_status ON funcionarios(status);
CREATE INDEX idx_funcionarios_cargo ON funcionarios(cargo);
CREATE INDEX idx_funcionarios_data_admissao ON funcionarios(data_admissao);

-- Índices para presença
CREATE INDEX idx_presenca_data ON presenca_funcionarios(data);
CREATE INDEX idx_presenca_funcionario_data ON presenca_funcionarios(id_funcionario, data);

-- Índices para vendas
CREATE INDEX idx_vendas_data ON vendas(data_venda);
CREATE INDEX idx_vendas_funcionario ON vendas(id_funcionario);
CREATE INDEX idx_vendas_status ON vendas(status);

-- Índices para permissões
CREATE INDEX idx_permissoes_usuario ON permissoes(id_usuario);
CREATE INDEX idx_permissoes_modulo ON permissoes(modulo);
CREATE INDEX idx_permissoes_ativas ON permissoes(ativo);

-- Índices para logs
CREATE INDEX idx_logs_usuario ON logs_acesso(id_usuario);
CREATE INDEX idx_logs_data ON logs_acesso(data_hora);
CREATE INDEX idx_logs_acao ON logs_acesso(acao);

-- Índices para orçamentos
CREATE INDEX idx_orcamentos_cliente ON orcamentos(id_cliente);
CREATE INDEX idx_orcamentos_data ON orcamentos(data_orcamento);
CREATE INDEX idx_orcamentos_status ON orcamentos(status);
CREATE INDEX idx_orcamentos_numero ON orcamentos(numero_orcamento);

-- =====================================================
-- TRIGGERS PARA AUTOMATIZAÇÃO
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

-- Trigger para calcular valor final do orçamento
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

-- Trigger para calcular subtotal em orçamentos_itens
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
-- VIEWS ÚTEIS
-- =====================================================

-- View para funcionários com estatísticas
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

-- View para permissões de usuários
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
-- PROCEDURES ÚTEIS
-- =====================================================

-- Procedure para registrar presença automática
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
        
    SELECT 'Presença registrada com sucesso' as mensagem;
END//
DELIMITER ;

-- Procedure para gerar relatório de produtividade
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