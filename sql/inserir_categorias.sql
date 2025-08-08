USE lava_jato_db;

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