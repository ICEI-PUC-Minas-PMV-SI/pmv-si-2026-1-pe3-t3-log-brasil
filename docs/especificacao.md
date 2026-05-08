# 3. DOCUMENTO DE ESPECIFICAÇÃO DE REQUISITOS DE SOFTWARE

## 3.1 Objetivos deste documento
Descrever e especificar as necessidades do processo de gestão de entregas que devem ser atendidas pelo projeto Log Brasil – Sistema de Gestão de Entregas, visando otimizar o controle logístico, o acompanhamento das entregas e a organização das operações de transporte.

## 3.2 Escopo do produto

### 3.2.1 Nome do produto e seus componentes principais
O produto será denominado Log Brasil – Sistema de Gestão de Entregas. Trata-se de uma aplicação voltada ao apoio das operações logísticas, sendo composta por módulos responsáveis pelo cadastro de clientes, pedidos e veículos, além de um módulo de controle das entregas. O sistema permitirá registrar, organizar e acompanhar as informações relacionadas às atividades de distribuição, oferecendo uma estrutura simples e eficiente para o gerenciamento das operações.

### 3.2.2 Missão do produto
A missão do Log Brasil é proporcionar maior controle, organização e eficiência nas operações de entrega, por meio da centralização das informações e do acompanhamento das atividades logísticas. O sistema busca reduzir falhas operacionais, facilitar o acesso aos dados e apoiar o usuário na gestão das entregas, contribuindo para um processo mais ágil, seguro e estruturado.

### 3.2.3 Limites do produto
O Log Brasil não contempla controle financeiro, faturamento, integração com sistemas externos, nem rastreamento em tempo real. Para manter valor operacional sem a complexidade do GPS em tempo real, o escopo inclui check-in geográfico opcional: ao confirmar a entrega (por exemplo, acionando “entregue”), o sistema pode registrar, nesse instante, as coordenadas geográficas fornecidas pelo navegador ou pelo dispositivo — um único ponto no momento do registro, não acompanhamento da rota.

Além disso, o produto é voltado ao cadastro e ao gerenciamento das entregas em escopo controlado, não atendendo múltiplas empresas ou operações logísticas complexas.

### 3.2.4 Benefícios do produto

| #  | Benefício                                     | Valor para o Cliente |
|----|-----------------------------------------------|----------------------|
| 1  | Cadastro rápido de clientes e pedidos         | Essencial            |
| 2  | Consulta ágil de informações                  | Essencial            |
| 3  | Controle eficiente das entregas               | Essencial            |
| 4  | Organização das operações logísticas          | Recomendável         |
| 5  | Redução de erros no processo                  | Essencial            |
| 6  | Aumento da produtividade operacional          | Recomendável         |
| 7  | Visibilidade do andamento das entregas        | Essencial            |
| 8  | Centralização dos dados logísticos            | Essencial            |
| 9  | Suporte à tomada de decisão                   | Recomendável         |
| 10 | Melhoria na qualidade do serviço              | Recomendável         |

## 3.3 Descrição geral do produto

### 3.3.1 Requisitos Funcionais

| Código | Requisito funcional | Descrição |
|--------|---------------------|-----------|
| RF1 | Manter cadastros mestre (CRUD) | Incluir, alterar, excluir e consultar clientes, pedidos, veículos e motoristas, com regras de permissão por perfil de usuário. |
| RF2 | Gerenciar entregas | Registrar e controlar as entregas vinculadas aos fretes. |
| RF3 | Atualizar status e check-in geográfico | Permitir atualizar o status das entregas (pendente, em andamento, concluída, atrasada, cancelada, conforme tabela de transições); ao marcar entrega como concluída, permitir check-in geográfico opcional. |
| RF4 | Registrar ocorrências | Registrar ocorrências associadas às entregas, com tipo, descrição e data/hora, podendo acionar regras de impacto sobre o status. |
| RF5 | Gerar relatórios | Gerar relatórios básicos das entregas com filtros (período, cliente, status). |
| RF6 | Planejar rota | Após vínculo dos pedidos ao frete, definir a sequência de paradas (ordem da rota) para apoio operacional. |
| RF7 | Registrar comprovante de entrega | Registrar evidências de conclusão (nome do recebedor, data/hora, observações, assinatura do cliente). |
| RF8 | Consultar painel operacional | Exibir visão consolidada de fretes e entregas com filtros por período, cliente e status. |
| RF9 | Atribuir motorista e veículo ao frete | Vincular motorista e veículo ao frete antes da saída para entrega (depende de frete com pedidos e, ordinariamente, de rota definida.) |


### 3.3.2 Requisitos Não Funcionais

| Código | Requisito Não Funcional | Descrição |
|--------|--------------------|-----------|
| RNF1 | Acesso Web | Ser acessado via navegador web |
| RNF2 | Interface Simples | Possuir interface intuitiva e de fácil utilização |
| RNF3 | Segurança de Acesso | Garantir segurança por meio de login e senha |
| RNF4 | Armazenamento Seguro | Armazenar os dados de forma segura em banco de dados |
| RNF5 | Desempenho | Responder a consultas principais (lista de pedidos, fretes e entregas) em um tempo de até dois segundos para uso operacional |
| RNF6 | Compatibilidade de Navegadores | Ser compatível com os principais navegadores (Chrome, Edge, etc.)|
| RNF7 | Usabilidade | Permitir execução das tarefas principais com fluxo claro e linguagem objetiva |
| RNF8 | Manutenibilidade | Permitir fácil manutenção e atualização |
| RNF9 | Backup de Dados | Possuir backup periódico dos dados |
| RNF10 | Integridade de Dados | Garantir integridade das informações armazenadas |
| RNF11 | Responsividade | Responsivo para diferentes tamanhos de tela |
| RNF12 | Registro de Logs | Registrar logs básicos de operações realizadas |

### 3.3.3 Usuários 

| Ator | Descrição |
|------|-----------|
| Administrador | Usuário responsável pelo gerenciamento geral do sistema, incluindo cadastro e manutenção de dados. Possui acesso completo. |
| Operador Logístico | Usuário responsável pelo cadastro de pedidos, clientes e controle das entregas. |
| Motorista | Usuário responsável por visualizar suas entregas e atualizar o status das mesmas. |
| Gestor | Usuário responsável por acompanhar as operações e analisar relatórios das entregas. |
| Cliente | Usuário que pode consultar o status de suas entregas. |
| Operador de monitoramento | Usuario responsavel por acompanhar entregas em andamento, tratar excecoes e apoiar replanejamento. |

## 3.4 Modelagem do Sistema

### 3.4.1 Diagrama de Casos de Uso

O diagrama da Figura 1 representa as principais interações entre os atores do Log Brasil e o sistema. O **Administrador** concentra funções de configuração e cadastros gerais. O **Operador logístico** trata de pedidos, fretes, **vínculo de pedidos ao frete**, **definição da sequência da rota** e **atribuição de motorista e veículo**. O **Motorista** executa a rota, atualiza status, realiza check-in geográfico na conclusão (quando habilitado) e registra ocorrências e comprovantes. O **Operador de monitoramento** acompanha o painel e apoia exceções. O **Gestor** consulta painéis e relatórios. O **Cliente** (quando previsto no escopo) consulta o status de seus pedidos.


Os casos de uso estão agrupados de forma lógica: cadastros base (clientes, pedidos, veículos, motoristas), operação (frete, rota, atribuição, entrega, status, ocorrências, comprovante), consulta (painel) e gestão (relatórios).

#### Figura 1: Diagrama de Casos de Uso do Sistema Log Brasil.

![Diagrama de Casos de Uso](../src/img/diagrama_casos_de_uso.png)

### 3.4.2 Descrições de Casos de Uso

#### Dependências — Montagem do frete, rota e atribuição
Para evitar ambiguidade entre vincular pedidos ao frete, definir rota (sequência) e atribuir motorista/veículo:

| Ordem sugerida | Caso de uso (resumo) | Dependência |
|----------------|----------------------|-------------|
| 1 | Criar frete e vincular pedidos (inclusão dos pedidos no frete). | Pedidos cadastrados e aptos. |
| 2 | Gerar / ajustar rota: ordenar as entregas (paradas) dentro do frete. | Frete já existente com pedidos vinculados (não há sequência de rota sem carga alocada ao frete). |
| 3 | Atribuir motorista e veículo ao frete. | Frete criado; em operação típica, rota já definida — a saída só ocorre com recurso alocado, salvo exceção operacional documentada. |
“A rota” no sistema é a ordenação das paradas desse frete; “vincular frete” no sentido operacional corresponde a associar pedidos ao frete e gerar as entregas correspondentes. O caso de uso [CSU01](#csu01) cobre criação do frete, vínculo de pedidos e definição da sequência; [CSU02](#csu02) trata somente da atribuição de motorista e veículo, que depende da existência do frete e utiliza a rota já ordenada como referência de execução.

#### Manter cadastros mestre (CSU00)

**Sumário:** O Administrador ou o Operador logístico (conforme permissões) realiza operações de **CRUD** sobre as entidades mestras do sistema.

**Atores primários:** Administrador; Operador logístico.

**Escopo das operações:**

| Entidade | Criar | Consultar | Alterar | Excluir / inativar |
|----------|-------|-----------|---------|----------------------|
| Cliente | Sim | Sim | Sim | Sim (se sem dependências bloqueantes) |
| Pedido | Sim | Sim | Sim | Sim (conforme situação no processo) |
| Veículo | Sim | Sim | Sim | Sim ou inativação |
| Motorista | Sim | Sim | Sim | Sim ou inativação |

**Pré-condições:** Usuário autenticado com perfil autorizado.

**Pós-condições:** Os cadastros refletem o estado atual desejado; exclusões respeitam integridade referencial (ex.: não remover pedido já vinculado a frete ativo sem regra de negócio específica).

**Requisitos relacionados:** RF1.

#### Gerenciar fretes, vincular pedidos e planejar rota (CSU01)

**Sumário:** O Operador Logístico cria um frete, associa pedidos ao frete (vínculo operacional) e define a sequência de entregas (rota) para apoio operacional. Corresponde, em conjunto, ao que o diagrama pode mostrar como vinculação ao frete e geração de rota — aqui descrito de forma encadeada no mesmo fluxo de montagem do frete.

**Ator primário:** Operador Logístico.

**Ator secundário:** Sistema.

**Pós-condições:** O frete existe, contém pedidos vinculados e possui ordem de rota definida (ou pendente de ajuste posterior).

**Requisitos relacionados:** RF2, RF6.

#### Atribuir motorista e veículo ao frete (CSU02)

**Sumário:** O Operador Logístico vincula motorista e veículo ao frete antes da saída para entrega.

**Ator primário:** Operador Logístico.

**Pré-condições:** Frete cadastrado; motorista e veículo cadastrados e aptos ao uso.

**Fluxo principal:**

1. O Operador Logístico localiza o frete desejado.
2. O Operador Logístico solicita atribuição de recursos.
3. O Sistema exibe listas de motoristas e veículos disponíveis conforme filtros.
4. O Operador Logístico seleciona um motorista e um veículo e confirma.
5. O Sistema valida consistência (ex.: veículo ativo, motorista habilitado) e grava a atribuição.

**Pós-condições:** O frete fica associado a motorista e veículo para execução.

#### Atualizar status de entrega (CSU03)

**Sumário:** O Motorista (ou Operador de monitoramento, conforme permissão) altera o status das entregas do frete (por exemplo: pendente, em andamento, concluída, atrasada).

**Atores primários:** Motorista; Operador de monitoramento.

**Pré-condições:** Usuário autenticado; entrega vinculada a frete atribuído ao motorista (quando aplicável).

**Fluxo principal:**

1. O ator acessa a lista de entregas do frete ou do dia.
2. O ator seleciona uma entrega e solicita alteração de status.
3. O Sistema exibe os status permitidos para aquela entrega.
4. O ator escolhe o novo status e confirma.
5. O Sistema valida a transição, registra data/hora e atualiza o painel.

**Fluxo alternativo – Transição inválida:** O Sistema informa que a mudança não é permitida e mantém o status anterior.

**Pós-condições:** O status da entrega reflete o estado atual da operação.

#### Registrar ocorrência na entrega (CSU04)

**Sumário:** O Motorista ou o Operador de monitoramento registra um imprevisto relacionado à entrega.

**Atores primários:** Motorista; Operador de monitoramento.

**Pré-condições:** Entrega identificável no sistema.

**Fluxo principal:**

1. O ator seleciona a entrega e solicita registro de ocorrência.
2. O Sistema apresenta formulário (tipo, descrição, data/hora).
3. O ator preenche e confirma.
4. O Sistema armazena a ocorrência e a associa à entrega.

**Pós-condições:** A ocorrência fica disponível para consulta no histórico da entrega e em relatórios.

#### Gerar relatório de entregas (CSU05)

**Sumário:** O Gestor gera relatório consolidado de entregas com filtros por período, cliente e status.

**Ator primário:** Gestor.

**Pré-condições:** Usuário autenticado com perfil de Gestor.

**Fluxo principal:**

1. O Gestor acessa a funcionalidade de relatórios.
2. O Gestor define filtros (período, cliente, status).
3. O Sistema processa e exibe o relatório ou exportação disponível.
4. O Gestor pode ajustar filtros e repetir a consulta.

**Pós-condições:** Os dados apresentados correspondem aos critérios informados.

### 3.4.3 Diagrama de Classes

A Figura 2 apresenta o modelo conceitual principal do domínio. Um **Cliente** realiza vários **Pedidos**. Vários pedidos podem ser agrupados em um **Frete**, que possui **Motorista** e **Veículo** atribuídos. Cada vínculo pedido–frete em execução é representado por uma **Entrega**, que possui **status**, pode registrar várias **Ocorrencia** e um **ComprovanteEntrega** quando concluída. Essa estrutura atende aos requisitos de cadastro, fretes, rotas, atribuição, status, ocorrências e comprovante descritos na Seção 3.3.1.

#### Figura 2: Diagrama de Classes do Sistema.
 
 ![Diagrama de Classes do Sistema](../src/img/diagrama_classes_dos_sistema.png)

### 3.4.4 Descrições das Classes

| # | Nome | Descrição |
|---|------|-----------|
| 1 | Cliente | Representa o contratante ou destinatário cadastrado, com dados de identificação, contato e endereço utilizados nos pedidos e entregas. |
| 2 | Pedido | Solicitação de transporte com origem, destino, prioridade e situação no processo logístico antes ou durante a alocação a um frete. |
| 3 | Veiculo | Meio de transporte disponível para execução de fretes, com identificação, tipo, capacidade e indicador de uso. |
| 4 | Motorista | Profissional responsável pela condução e execução das entregas do frete, com dados cadastrais e situação ativa ou inativa. |
| 5 | Frete | Agrupamento operacional de entregas com sequência de rota planejada e vínculo opcional a motorista e veículo. |
| 6 | Entrega | Instância operacional de um pedido dentro de um frete, com status ao longo do ciclo (pendente, em andamento, concluída, atrasada, cancelada). |
| 7 | Ocorrencia | Registro de imprevistos ou observações ligados a uma entrega (tipo, descrição e momento). |
| 8 | ComprovanteEntrega | Evidência de conclusão da entrega, com nome do recebedor, data/hora e observações, quando aplicável. |
