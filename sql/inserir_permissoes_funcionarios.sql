USE lava_jato_db;

-- Inserir permissões para o módulo de funcionários para o usuário administrador (ID 1)
INSERT INTO permissoes (id_usuario, modulo, funcionalidade, ativo) VALUES
(1, 'funcionarios', 'visualizar', 1),
(1, 'funcionarios', 'cadastrar', 1),
(1, 'funcionarios', 'editar', 1),
(1, 'funcionarios', 'excluir', 1),
(1, 'funcionarios', 'relatorios', 1);

-- Verificar se as permissões foram inseridas
SELECT * FROM permissoes WHERE modulo = 'funcionarios'; 