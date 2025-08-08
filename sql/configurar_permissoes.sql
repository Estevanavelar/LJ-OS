-- =====================================================
-- CONFIGURAÇÃO DE PERMISSÕES COMPLETAS
-- LJ-OS Sistema para Lava Jato
-- =====================================================

-- Limpar permissões existentes para o usuário admin (id = 1)
DELETE FROM permissoes WHERE id_usuario = 1;

-- Inserir permissões completas para o usuário admin
INSERT INTO permissoes (id_usuario, modulo, funcionalidade, ativo) VALUES
-- Dashboard
(1, 'dashboard', 'visualizar', 1),
(1, 'dashboard', 'relatorios', 1),

-- Clientes
(1, 'clientes', 'visualizar', 1),
(1, 'clientes', 'cadastrar', 1),
(1, 'clientes', 'editar', 1),
(1, 'clientes', 'excluir', 1),
(1, 'clientes', 'relatorios', 1),

-- Veículos
(1, 'veiculos', 'visualizar', 1),
(1, 'veiculos', 'cadastrar', 1),
(1, 'veiculos', 'editar', 1),
(1, 'veiculos', 'excluir', 1),
(1, 'veiculos', 'relatorios', 1),

-- Agendamentos
(1, 'agendamentos', 'visualizar', 1),
(1, 'agendamentos', 'cadastrar', 1),
(1, 'agendamentos', 'editar', 1),
(1, 'agendamentos', 'excluir', 1),
(1, 'agendamentos', 'relatorios', 1),

-- Ordens de Serviço
(1, 'ordens_servico', 'visualizar', 1),
(1, 'ordens_servico', 'cadastrar', 1),
(1, 'ordens_servico', 'editar', 1),
(1, 'ordens_servico', 'excluir', 1),
(1, 'ordens_servico', 'relatorios', 1),

-- Serviços
(1, 'servicos', 'visualizar', 1),
(1, 'servicos', 'cadastrar', 1),
(1, 'servicos', 'editar', 1),
(1, 'servicos', 'excluir', 1),
(1, 'servicos', 'relatorios', 1),

-- Estoque
(1, 'estoque', 'visualizar', 1),
(1, 'estoque', 'cadastrar', 1),
(1, 'estoque', 'editar', 1),
(1, 'estoque', 'excluir', 1),
(1, 'estoque', 'relatorios', 1),

-- Financeiro
(1, 'financeiro', 'visualizar', 1),
(1, 'financeiro', 'cadastrar', 1),
(1, 'financeiro', 'editar', 1),
(1, 'financeiro', 'excluir', 1),
(1, 'financeiro', 'relatorios', 1),

-- Orçamentos
(1, 'orcamentos', 'visualizar', 1),
(1, 'orcamentos', 'cadastrar', 1),
(1, 'orcamentos', 'editar', 1),
(1, 'orcamentos', 'excluir', 1),
(1, 'orcamentos', 'relatorios', 1),

-- Cupons
(1, 'cupons', 'visualizar', 1),
(1, 'cupons', 'cadastrar', 1),
(1, 'cupons', 'editar', 1),
(1, 'cupons', 'excluir', 1),
(1, 'cupons', 'relatorios', 1),

-- Relatórios
(1, 'relatorios', 'visualizar', 1),
(1, 'relatorios', 'gerar', 1),
(1, 'relatorios', 'exportar', 1),

-- Usuários
(1, 'usuarios', 'visualizar', 1),
(1, 'usuarios', 'cadastrar', 1),
(1, 'usuarios', 'editar', 1),
(1, 'usuarios', 'excluir', 1),
(1, 'usuarios', 'relatorios', 1),

-- Funcionários
(1, 'funcionarios', 'visualizar', 1),
(1, 'funcionarios', 'cadastrar', 1),
(1, 'funcionarios', 'editar', 1),
(1, 'funcionarios', 'excluir', 1),
(1, 'funcionarios', 'relatorios', 1),

-- Permissões
(1, 'permissoes', 'visualizar', 1),
(1, 'permissoes', 'cadastrar', 1),
(1, 'permissoes', 'editar', 1),
(1, 'permissoes', 'excluir', 1),

-- Configurações
(1, 'configuracoes', 'visualizar', 1),
(1, 'configuracoes', 'editar', 1);

-- =====================================================
-- FIM DO SCRIPT
-- ===================================================== 