USE lava_jato_db;

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