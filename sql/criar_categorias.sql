USE lava_jato_db;

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