# 📋 PRD - LJ-OS Sistema para Lava Jato
## Parte 3: Especificações Funcionais e Casos de Uso

---

## 🔄 **16. FLUXOS DE TRABALHO PRINCIPAIS**

### **16.1 Fluxo de Atendimento ao Cliente**

#### **16.1.1 Processo de Chegada**
```
1. Cliente chega ao lava jato
2. Recepcionista identifica cliente (CPF/placa)
3. Sistema busca histórico e veículos
4. Cliente escolhe serviço
5. Sistema calcula preço e tempo estimado
6. Confirmação do agendamento
7. Geração de ordem de serviço
8. Atribuição ao funcionário
```

#### **16.1.2 Processo de Execução**
```
1. Funcionário recebe OS
2. Inicia serviço (marca hora início)
3. Executa serviço conforme especificações
4. Marca conclusão (marca hora fim)
5. Sistema calcula tempo real vs estimado
6. Funcionário registra observações
7. Sistema atualiza status da OS
8. Cliente é notificado da conclusão
```

#### **16.1.3 Processo de Entrega**
```
1. Cliente retorna para buscar veículo
2. Recepcionista confirma conclusão
3. Sistema gera comprovante
4. Cliente paga serviço
5. Sistema registra pagamento
6. Atualiza pontos de fidelidade
7. Gera recibo fiscal
8. Cliente sai com veículo
```

### **16.2 Fluxo de Agendamentos**

#### **16.2.1 Criação de Agendamento**
```
1. Cliente solicita agendamento (presencial/telefone/online)
2. Recepcionista verifica disponibilidade
3. Sistema mostra horários disponíveis
4. Cliente escolhe data/hora
5. Sistema confirma disponibilidade
6. Recepcionista confirma dados do cliente
7. Sistema cria agendamento
8. Envia confirmação (WhatsApp/SMS)
```

#### **16.2.2 Gestão de Horários**
```
1. Sistema verifica capacidade diária
2. Calcula tempo estimado por serviço
3. Reserva horário no calendário
4. Atualiza disponibilidade em tempo real
5. Gerencia conflitos de horário
6. Sugere horários alternativos
7. Notifica funcionários sobre agenda
8. Monitora cumprimento de horários
```

---

## 👥 **17. MÓDULO DE GESTÃO DE CLIENTES**

### **17.1 Funcionalidades Principais**

#### **17.1.1 Cadastro de Clientes**
- **Dados pessoais**: Nome, CPF/CNPJ, RG/IE, telefone, email
- **Endereço completo**: CEP, cidade, estado, endereço
- **Informações adicionais**: Data nascimento, observações
- **Programa de fidelidade**: Ativação, pontos acumulados
- **Documentos**: Upload de RG, CNH, comprovantes

#### **17.1.2 Gestão de Veículos**
- **Cadastro múltiplo**: Vários veículos por cliente
- **Dados técnicos**: Marca, modelo, ano, cor, combustível
- **Informações operacionais**: KM atual, observações
- **Fotos**: Upload de imagens do veículo
- **Histórico**: Todos os serviços realizados

#### **17.1.3 Sistema de Fidelidade**
- **Pontos por serviço**: Acumulação automática
- **Níveis de fidelidade**: Bronze, Prata, Ouro, Diamante
- **Benefícios**: Descontos, serviços gratuitos
- **Relatórios**: Análise de comportamento
- **Campanhas**: Promoções personalizadas

### **17.2 Casos de Uso**

#### **17.2.1 UC001 - Cadastrar Novo Cliente**
**Ator**: Recepcionista
**Pré-condições**: Usuário logado com permissão de cliente
**Fluxo principal**:
1. Recepcionista acessa "Novo Cliente"
2. Sistema exibe formulário de cadastro
3. Recepcionista preenche dados obrigatórios
4. Sistema valida informações
5. Sistema cria cliente no banco
6. Sistema exibe mensagem de sucesso
7. Sistema redireciona para lista de clientes

**Pós-condições**: Cliente cadastrado e disponível para agendamentos

#### **17.2.2 UC002 - Buscar Cliente Existente**
**Ator**: Recepcionista
**Pré-condições**: Usuário logado
**Fluxo principal**:
1. Recepcionista acessa busca de clientes
2. Sistema exibe campo de busca
3. Recepcionista digita CPF, placa ou nome
4. Sistema realiza busca em tempo real
5. Sistema exibe resultados filtrados
6. Recepcionista seleciona cliente
7. Sistema exibe perfil completo do cliente

**Pós-condições**: Cliente encontrado e selecionado

---

## 🚗 **18. MÓDULO DE CONTROLE DE VEÍCULOS**

### **18.1 Funcionalidades Principais**

#### **18.1.1 Cadastro de Veículos**
- **Identificação**: Placa, chassi, renavam
- **Características**: Marca, modelo, ano, cor
- **Especificações**: Combustível, transmissão, motor
- **Documentação**: IPVA, licenciamento, seguro
- **Fotos**: Imagens externas e internas

#### **18.1.2 Histórico de Serviços**
- **Timeline completa**: Todos os serviços realizados
- **Detalhes técnicos**: Produtos utilizados, funcionário
- **Custos**: Valores pagos por serviço
- **Observações**: Problemas identificados, recomendações
- **Próximos serviços**: Manutenções programadas

#### **18.1.3 Gestão de Documentos**
- **Documentos obrigatórios**: IPVA, licenciamento
- **Alertas**: Vencimento próximo de documentos
- **Histórico**: Versões anteriores de documentos
- **Notificações**: Lembretes automáticos
- **Relatórios**: Status da documentação

### **18.2 Casos de Uso**

#### **18.2.1 UC003 - Cadastrar Novo Veículo**
**Ator**: Recepcionista
**Pré-condições**: Cliente já cadastrado no sistema
**Fluxo principal**:
1. Recepcionista acessa perfil do cliente
2. Sistema exibe lista de veículos
3. Recepcionista clica em "Novo Veículo"
4. Sistema exibe formulário de veículo
5. Recepcionista preenche dados do veículo
6. Sistema valida placa (única)
7. Sistema salva veículo
8. Sistema exibe mensagem de sucesso

**Pós-condições**: Veículo cadastrado e associado ao cliente

---

## 📅 **19. MÓDULO DE AGENDAMENTOS**

### **19.1 Funcionalidades Principais**

#### **19.1.1 Calendário Interativo**
- **Visualização**: Mês, semana, dia
- **Agendamentos**: Exibição por horário
- **Status visual**: Cores diferentes por status
- **Drag & Drop**: Reorganização de horários
- **Filtros**: Por funcionário, serviço, cliente

#### **19.1.2 Gestão de Horários**
- **Disponibilidade**: Horários de funcionamento
- **Capacidade**: Número de veículos simultâneos
- **Tempo estimado**: Por tipo de serviço
- **Intervalos**: Tempo entre agendamentos
- **Bloqueios**: Horários indisponíveis

#### **19.1.3 Notificações Automáticas**
- **Confirmação**: 24h antes do agendamento
- **Lembrete**: 2h antes do horário
- **Atraso**: Notificação de atraso
- **Cancelamento**: Confirmação de cancelamento
- **Reagendamento**: Sugestões de novos horários

### **19.2 Casos de Uso**

#### **19.2.1 UC004 - Criar Agendamento**
**Ator**: Recepcionista
**Pré-condições**: Cliente e veículo cadastrados
**Fluxo principal**:
1. Recepcionista acessa calendário
2. Sistema exibe calendário interativo
3. Recepcionista seleciona data e horário
4. Sistema verifica disponibilidade
5. Recepcionista seleciona cliente e veículo
6. Recepcionista escolhe serviço
7. Sistema calcula preço e tempo
8. Recepcionista confirma agendamento
9. Sistema cria agendamento
10. Sistema envia confirmação

**Pós-condições**: Agendamento criado e confirmado

---

## 🔧 **20. MÓDULO DE ORDENS DE SERVIÇO**

### **20.1 Funcionalidades Principais**

#### **20.1.1 Criação de OS**
- **Numeração automática**: Sequencial único
- **Dados do cliente**: Nome, telefone, endereço
- **Dados do veículo**: Placa, marca, modelo, cor
- **Serviços solicitados**: Lista com preços
- **Observações**: Especificações especiais
- **Data e hora**: Abertura e previsão de conclusão

#### **20.1.2 Acompanhamento de Status**
- **Status em tempo real**: Aberta, em andamento, concluída
- **Timeline de progresso**: Etapas do serviço
- **Funcionário responsável**: Atribuição e acompanhamento
- **Tempo real vs estimado**: Comparação de prazos
- **Alertas**: Atrasos e problemas

#### **20.1.3 Cálculos Automáticos**
- **Valor dos serviços**: Soma automática
- **Descontos**: Aplicação de cupons e fidelidade
- **Impostos**: Cálculo de ICMS, PIS, COFINS
- **Valor final**: Total a pagar
- **Formas de pagamento**: Dinheiro, cartão, PIX

### **20.2 Casos de Uso**

#### **20.2.1 UC005 - Criar Ordem de Serviço**
**Ator**: Recepcionista
**Pré-condições**: Cliente e veículo selecionados
**Fluxo principal**:
1. Recepcionista acessa "Nova OS"
2. Sistema exibe formulário de OS
3. Sistema preenche dados do cliente automaticamente
4. Recepcionista seleciona serviços
5. Sistema calcula valores automaticamente
6. Recepcionista adiciona observações
7. Recepcionista confirma criação
8. Sistema gera número de OS
9. Sistema imprime OS
10. Sistema atribui ao funcionário

**Pós-condições**: OS criada e impressa

---

## 📦 **21. MÓDULO DE CONTROLE DE ESTOQUE**

### **21.1 Funcionalidades Principais**

#### **21.1.1 Gestão de Produtos**
- **Cadastro completo**: Nome, descrição, categoria
- **Códigos**: Código interno, código de barras
- **Especificações**: Marca, modelo, tamanho
- **Preços**: Custo, preço de venda, margem
- **Fornecedores**: Dados de contato e preços

#### **21.1.2 Controle de Quantidade**
- **Estoque atual**: Quantidade disponível
- **Estoque mínimo**: Ponto de reabastecimento
- **Estoque máximo**: Capacidade de armazenamento
- **Unidade de medida**: Peças, litros, metros
- **Localização**: Prateleira, setor, depósito

#### **21.1.3 Movimentações**
- **Entrada**: Compras, devoluções, transferências
- **Saída**: Vendas, consumo, perdas
- **Transferências**: Entre setores, filiais
- **Ajustes**: Inventários, correções
- **Histórico**: Rastreamento completo

### **21.2 Casos de Uso**

#### **21.2.1 UC006 - Registrar Entrada de Produto**
**Ator**: Funcionário de estoque
**Pré-condições**: Produto cadastrado no sistema
**Fluxo principal**:
1. Funcionário acessa "Entrada de Produto"
2. Sistema exibe formulário de entrada
3. Funcionário seleciona produto
4. Funcionário informa quantidade
5. Funcionário informa preço unitário
6. Funcionário seleciona fornecedor
7. Sistema calcula valor total
8. Funcionário confirma entrada
9. Sistema atualiza estoque
10. Sistema registra movimentação

**Pós-condições**: Estoque atualizado e movimentação registrada

---

## 💰 **22. MÓDULO FINANCEIRO**

### **22.1 Funcionalidades Principais**

#### **22.1.1 Controle de Receitas**
- **Vendas de serviços**: Registro automático das OS
- **Vendas de produtos**: Estoque e acessórios
- **Receitas extras**: Taxas, multas, comissões
- **Formas de pagamento**: Dinheiro, cartão, PIX, transferência
- **Parcelamento**: Controle de recebimentos futuros

#### **22.1.2 Controle de Despesas**
- **Custos operacionais**: Aluguel, água, energia, telefone
- **Custos de pessoal**: Salários, encargos, benefícios
- **Custos de produtos**: Compras de estoque
- **Custos administrativos**: Contabilidade, marketing
- **Custos de manutenção**: Equipamentos, veículos

#### **22.1.3 Relatórios Financeiros**
- **Fluxo de caixa**: Entradas e saídas por período
- **DRE**: Demonstrativo de resultados
- **Balanço**: Ativo, passivo e patrimônio
- **Análise de rentabilidade**: Por serviço, período, funcionário
- **Projeções**: Previsão de receitas e despesas

### **22.2 Casos de Uso**

#### **22.2.1 UC007 - Registrar Receita de Serviço**
**Ator**: Recepcionista
**Pré-condições**: OS concluída e cliente presente
**Fluxo principal**:
1. Recepcionista acessa OS concluída
2. Sistema exibe valor total a pagar
3. Recepcionista confirma forma de pagamento
4. Sistema registra receita
5. Sistema atualiza fluxo de caixa
6. Sistema gera recibo
7. Sistema imprime comprovante
8. Sistema atualiza pontos de fidelidade

**Pós-condições**: Receita registrada e cliente atendido

---

## 👨‍💼 **23. MÓDULO DE GESTÃO DE FUNCIONÁRIOS**

### **23.1 Funcionalidades Principais**

#### **23.1.1 Cadastro de Funcionários**
- **Dados pessoais**: Nome, CPF, RG, data nascimento
- **Dados profissionais**: Cargo, departamento, data admissão
- **Contatos**: Telefone, email, endereço
- **Documentos**: CTPS, PIS, título de eleitor
- **Fotos**: Foto de perfil e documentos

#### **23.1.2 Controle de Acesso**
- **Usuário do sistema**: Login e senha
- **Nível de acesso**: Admin, gerente, atendente, funcionário
- **Permissões granulares**: Por módulo e função
- **Histórico de acesso**: Logs de login e logout
- **Bloqueio temporário**: Por inatividade ou tentativas

#### **23.1.3 Gestão de Performance**
- **Metas**: Objetivos por período
- **Avaliações**: Desempenho e comportamento
- **Treinamentos**: Cursos e certificações
- **Promoções**: Evolução na carreira
- **Comissões**: Cálculo baseado em vendas

### **23.2 Casos de Uso**

#### **23.2.1 UC008 - Cadastrar Novo Funcionário**
**Ator**: Administrador
**Pré-condições**: Usuário logado com permissão de admin
**Fluxo principal**:
1. Administrador acessa "Novo Funcionário"
2. Sistema exibe formulário de cadastro
3. Administrador preenche dados pessoais
4. Administrador define cargo e departamento
5. Administrador configura permissões
6. Sistema cria usuário do sistema
7. Sistema envia credenciais por email
8. Sistema exibe mensagem de sucesso

**Pós-condições**: Funcionário cadastrado e usuário criado

---

## 🛡️ **24. SISTEMA DE PERMISSÕES**

### **24.1 Estrutura de Permissões**

#### **24.1.1 Níveis de Acesso**
- **Admin**: Acesso total ao sistema
- **Gerente**: Gestão de equipe e relatórios
- **Atendente**: Clientes, agendamentos e OS
- **Funcionário**: Apenas suas atividades

#### **24.1.2 Permissões Granulares**
- **Leitura**: Visualizar dados
- **Escrita**: Criar e editar
- **Exclusão**: Remover registros
- **Aprovação**: Autorizar ações
- **Relatórios**: Gerar e exportar

#### **24.1.3 Recursos Protegidos**
- **Módulos**: Clientes, veículos, agendamentos
- **Funcionalidades**: Relatórios, configurações
- **Dados sensíveis**: Financeiro, funcionários
- **Sistema**: Configurações, backups

### **24.2 Casos de Uso**

#### **24.2.1 UC009 - Configurar Permissões de Usuário**
**Ator**: Administrador
**Pré-condições**: Usuário e funcionário cadastrados
**Fluxo principal**:
1. Administrador acessa "Permissões"
2. Sistema exibe lista de usuários
3. Administrador seleciona usuário
4. Sistema exibe módulos disponíveis
5. Administrador marca permissões
6. Sistema valida configuração
7. Sistema salva permissões
8. Sistema exibe mensagem de sucesso

**Pós-condições**: Permissões configuradas e ativas

---

**📋 Esta é a terceira parte do PRD. Continue para a próxima parte que abordará os requisitos de implementação, testes e documentação.**
