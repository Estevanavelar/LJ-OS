-- =====================================================
-- TABELAS DO MÓDULO FINANCEIRO
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
    cor VARCHAR(7) DEFAULT '#007bff' COMMENT 'Cor em hexadecimal para identificação visual',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_status (status)
);

-- =====================================================
-- TABELA DE MOVIMENTAÇÕES FINANCEIRAS
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
    id_os INT NULL COMMENT 'Referência à ordem de serviço se aplicável',
    documento VARCHAR(100) NULL COMMENT 'Número da nota fiscal, recibo, etc.',
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
-- INSERIR CATEGORIAS PADRÃO
-- =====================================================

-- Categorias de Receita
INSERT INTO categorias_financeiras (nome, tipo, descricao, cor) VALUES
('Lavagem de Veículos', 'receita', 'Receitas provenientes de serviços de lavagem', '#28a745'),
('Venda de Produtos', 'receita', 'Receitas provenientes da venda de produtos', '#17a2b8'),
('Serviços Especiais', 'receita', 'Receitas de serviços diferenciados', '#ffc107'),
('Outras Receitas', 'receita', 'Outras receitas diversas', '#6f42c1');

-- Categorias de Despesa
INSERT INTO categorias_financeiras (nome, tipo, descricao, cor) VALUES
('Fornecedores', 'despesa', 'Pagamentos a fornecedores de produtos', '#dc3545'),
('Funcionários', 'despesa', 'Salários, comissões e benefícios', '#fd7e14'),
('Aluguel', 'despesa', 'Aluguel do estabelecimento', '#e83e8c'),
('Contas Públicas', 'despesa', 'Água, luz, telefone, internet', '#6c757d'),
('Manutenção', 'despesa', 'Manutenção de equipamentos e instalações', '#495057'),
('Marketing', 'despesa', 'Publicidade e propaganda', '#20c997'),
('Impostos', 'despesa', 'Impostos e taxas', '#343a40'),
('Outras Despesas', 'despesa', 'Outras despesas diversas', '#6c757d');

-- =====================================================
-- ÍNDICES ADICIONAIS PARA OTIMIZAÇÃO
-- =====================================================

-- Índices para consultas frequentes
CREATE INDEX idx_movimentacoes_tipo_data ON movimentacoes_financeiras(tipo, data_movimentacao);
CREATE INDEX idx_movimentacoes_status_data ON movimentacoes_financeiras(status, data_movimentacao);
CREATE INDEX idx_movimentacoes_categoria_tipo ON movimentacoes_financeiras(id_categoria, tipo);

-- =====================================================
-- TRIGGERS PARA AUTOMATIZAÇÃO
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
-- VIEWS ÚTEIS PARA RELATÓRIOS
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

-- View para movimentações com detalhes das categorias
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
-- PROCEDURES ÚTEIS
-- =====================================================

-- Procedure para gerar relatório financeiro por período
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