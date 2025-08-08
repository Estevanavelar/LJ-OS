-- Criar tabelas do módulo financeiro
USE lava_jato_db;

-- Tabela de categorias financeiras
CREATE TABLE IF NOT EXISTS categorias_financeiras (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('receita', 'despesa', 'ambos') NOT NULL DEFAULT 'ambos',
    descricao TEXT NULL,
    cor VARCHAR(7) DEFAULT '#007bff',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de movimentações financeiras
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
    id_os INT NULL,
    documento VARCHAR(100) NULL,
    observacoes TEXT NULL,
    usuario_responsavel INT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_financeiras(id_categoria) ON DELETE SET NULL,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE SET NULL,
    FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- Inserir categorias padrão
INSERT INTO categorias_financeiras (nome, tipo, descricao, cor) VALUES
('Lavagem de Veículos', 'receita', 'Receitas provenientes de serviços de lavagem', '#28a745'),
('Venda de Produtos', 'receita', 'Receitas provenientes da venda de produtos', '#17a2b8'),
('Serviços Especiais', 'receita', 'Receitas de serviços diferenciados', '#ffc107'),
('Outras Receitas', 'receita', 'Outras receitas diversas', '#6f42c1'),
('Fornecedores', 'despesa', 'Pagamentos a fornecedores de produtos', '#dc3545'),
('Funcionários', 'despesa', 'Salários, comissões e benefícios', '#fd7e14'),
('Aluguel', 'despesa', 'Aluguel do estabelecimento', '#e83e8c'),
('Contas Públicas', 'despesa', 'Água, luz, telefone, internet', '#6c757d'),
('Manutenção', 'despesa', 'Manutenção de equipamentos e instalações', '#495057'),
('Marketing', 'despesa', 'Publicidade e propaganda', '#20c997'),
('Impostos', 'despesa', 'Impostos e taxas', '#343a40'),
('Outras Despesas', 'despesa', 'Outras despesas diversas', '#6c757d'); 