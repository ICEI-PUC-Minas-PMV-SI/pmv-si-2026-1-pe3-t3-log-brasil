# 3. DOCUMENTO DE ESPECIFICAÇÃO DE REQUISITOS DE SOFTWARE

## 3.1 Objetivos deste documento
Descrever e especificar as necessidades do processo de gestão de entregas que devem ser atendidas pelo projeto Log Brasil – Sistema de Gestão de Entregas, visando otimizar o controle logístico, o acompanhamento das entregas e a organização das operações de transporte.

## 3.2 Escopo do produto

### 3.2.1 Nome do produto e seus componentes principais
O produto será denominado Log Brasil – Sistema de Gestão de Entregas. Trata-se de uma aplicação voltada ao apoio das operações logísticas, sendo composta por módulos responsáveis pelo cadastro de clientes, pedidos e veículos, além de um módulo de controle das entregas. O sistema permitirá registrar, organizar e acompanhar as informações relacionadas às atividades de distribuição, oferecendo uma estrutura simples e eficiente para o gerenciamento das operações.

### 3.2.2 Missão do produto
A missão do Log Brasil é proporcionar maior controle, organização e eficiência nas operações de entrega, por meio da centralização das informações e do acompanhamento das atividades logísticas. O sistema busca reduzir falhas operacionais, facilitar o acesso aos dados e apoiar o usuário na gestão das entregas, contribuindo para um processo mais ágil, seguro e estruturado.
### 3.2.3 Limites do produto
O Log Brasil não contempla controle financeiro, faturamento, integração com sistemas externos ou rastreamento em tempo real via GPS. O sistema é voltado apenas para o cadastro e gerenciamento básico das entregas, não atendendo múltiplas empresas ou operações logísticas complexas.

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

| Código | Requisito Funcional | Descrição |
|--------|--------------------|-----------|
| RF1 | Gerenciar Clientes | Permitir inclusão, alteração, exclusão e consulta de clientes |
| RF2 | Gerenciar Pedidos | Permitir inclusão, alteração, exclusão e consulta de pedidos |
| RF3 | Gerenciar Veículos | Permitir inclusão, alteração, exclusão e consulta de veículos |
| RF4 | Gerenciar Entregas | Permitir registrar e controlar as entregas realizadas |
| RF5 | Gerenciar Motoristas | Permitir inclusão, alteração, exclusão e consulta de Motoristas/Entregadores |
| RF6 | Atualizar Status de Entrega | Permitir atualizar o status das entregas (pendente, em andamento, concluída) |
| RF7 | Consultar Entregas | Permitir visualizar e consultar entregas cadastradas |
| RF8 | Registrar Ocorrências | Permitir registrar ocorrências relacionadas às entregas |
| RF9 | Gerar Relatórios | Permitir a geração de relatórios básicos das entregas |
| RF10 | Planejar rotas | Sugerir e registrar sequencia de entregas por frete para apoio operacional. |
| RF11 | Registrar comprovante de entrega | Permitir registro de evidencias (nome do recebedor, data/hora e observacoes). |
| RF12 | Consultar painel operacional | Exibir visao consolidada de fretes e entregas com filtros por periodo, cliente e status. |
| RF13 | Atribuir recursos de transporte | Vincular motorista e veiculo ao frete antes da saida para entrega. |
| RF14 | Consultar painel operacional | Exibir visao consolidada de fretes e entregas com filtros por periodo, cliente e status. |


### 3.3.2 Requisitos Não Funcionais

| Código | Requisito Não Funcional |
|--------|------------------------|
| RNF1 | Acesso Web | Ser acessado via navegador web |
| RNF2 | Interface Simples | Possuir interface simples e de fácil utilização |
| RNF3 | Segurança de Acesso | Garantir segurança por meio de login e senha |
| RNF4 | Armazenamento Seguro | Armazenar os dados de forma segura em banco de dados |
| RNF5 | Desempenho | Responder a consultas principais (lista de pedidos, fretes e entregas) em tempo adequado para uso operacional |
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
Como observado no diagrama de casos de uso da Figura 1, a secretária poderá gerenciar as matrículas e professores no sistema, enquanto o coordenador, além dessas funções, poderá gerenciar os cursos de aperfeiçoamento.

#### Figura 1: Diagrama de Casos de Uso do Sistema.

![dcu](https://github.com/user-attachments/assets/41f6b731-b44e-43aa-911f-423ad6198f47)
 
### 3.4.2 Descrições de Casos de Uso

Cada caso de uso deve ter a sua descrição representada nesta seção. Exemplo:

#### Gerenciar Professor (CSU01)

Sumário: A Secretária realiza a gestão (inclusão, remoção, alteração e consulta) dos dados sobre professores.

Ator Primário: Secretária.

Ator Secundário: Coordenador.

Pré-condições: A Secretária deve ser validada pelo Sistema.

Fluxo Principal:

1) 	A Secretária requisita manutenção de professores.
2) 	O Sistema apresenta as operações que podem ser realizadas: inclusão de um novo professor, alteração de um professor, a exclusão de um professor e a consulta de dados de um professor.
3) 	A Secretária seleciona a operação desejada: Inclusão, Exclusão, Alteração ou Consulta, ou opta por finalizar o caso de uso.
4) 	Se a Secretária desejar continuar com a gestão de professores, o caso de uso retorna ao passo 2; caso contrário o caso de uso termina.

Fluxo Alternativo (3): Inclusão

a)	A Secretária requisita a inclusão de um professor. <br>
b)	O Sistema apresenta uma janela solicitando o CPF do professor a ser cadastrado. <br>
c)	A Secretária fornece o dado solicitado. <br>
d)	O Sistema verifica se o professor já está cadastrado. Se sim, o Sistema reporta o fato e volta ao início; caso contrário, apresenta um formulário em branco para que os detalhes do professor (Código, Nome, Endereço, CEP, Estado, Cidade, Bairro, Telefone, Identidade, Sexo, Fax, CPF, Data do Cadastro e Observação) sejam incluídos. <br>
e)	A Secretária fornece os detalhes do novo professor. <br>
f)	O Sistema verifica a validade dos dados. Se os dados forem válidos, inclui o novo professor e a grade listando os professores cadastrados é atualizada; caso contrário, o Sistema reporta o fato, solicita novos dados e repete a verificação. <br>

Fluxo Alternativo (3): Remoção

a)	A Secretária seleciona um professor e requisita ao Sistema que o remova. <br>
b)	Se o professor pode ser removido, o Sistema realiza a remoção; caso contrário, o Sistema reporta o fato. <br>

Fluxo Alternativo (3): Alteração

a)	A Secretária altera um ou mais dos detalhes do professor e requisita sua atualização. <br>
b)	O Sistema verifica a validade dos dados e, se eles forem válidos, altera os dados na lista de professores, caso contrário, o erro é reportado. <br>
 
Fluxo Alternativo (3): Consulta

a)	A Secretária opta por pesquisar pelo nome ou código e solicita a consulta sobre a lista de professores. <br>
b)	O Sistema apresenta uma lista professores. <br>
c)	A Secretária seleciona o professor. <br>
d)	O Sistema apresenta os detalhes do professor no formulário de professores. <br>

Pós-condições: Um professor foi inserido ou removido, seus dados foram alterados ou apresentados na tela.

### 3.4.3 Diagrama de Classes 

A Figura 2 mostra o diagrama de classes do sistema. A Matrícula deve conter a identificação do funcionário responsável pelo registro, bem com os dados do aluno e turmas. Para uma disciplina podemos ter diversas turmas, mas apenas um professor responsável por ela.

#### Figura 2: Diagrama de Classes do Sistema.
 
![image](https://github.com/user-attachments/assets/abc7591a-b46f-4ea2-b8f0-c116b60eb24e)


### 3.4.4 Descrições das Classes 

| # | Nome | Descrição |
|--------------------|------------------------------------|----------------------------------------|
| 1	|	Aluno |	Cadastro de informações relativas aos alunos. |
| 2	| Curso |	Cadastro geral de cursos de aperfeiçoamento. |
| 3 |	Matrícula |	Cadastro de Matrículas de alunos nos cursos. |
| 4 |	Turma |	Cadastro de turmas.
| 5	|	Professor |	Cadastro geral de professores que ministram as disciplinas. |
| ... |	... |	... |
