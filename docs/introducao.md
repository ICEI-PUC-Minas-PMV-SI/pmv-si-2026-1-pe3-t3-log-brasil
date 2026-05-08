# 1. INTRODUÇÃO
A logística é um dos pilares fundamentais para o funcionamento eficiente das organizações, especialmente no setor de transporte de cargas. Com o aumento da demanda por entregas rápidas, rastreabilidade e controle de custos, torna-se essencial a utilização de sistemas que auxiliem na gestão das operações logísticas. Nesse contexto, destaca-se o Transportation Management System (TMS), responsável por planejar, executar e monitorar atividades relacionadas ao transporte de mercadorias.
O presente trabalho propõe o desenvolvimento de um sistema TMS com foco na organização de rotas, controle de fretes, emissão de documentos fiscais e acompanhamento de entregas, buscando otimizar processos e reduzir falhas operacionais.

## 1.1. Problema
Muitas transportadoras e empresas de pequeno e médio porte ainda realizam o controle de fretes, rotas e documentos de forma manual ou utilizando sistemas não integrados. Isso pode gerar problemas como: erros na emissão de documentos, falta de controle, sobre prazos de entrega, dificuldade no acompanhamento de cargas, custos elevados com rotas mal planejadas, falta de integração com sistemas fiscais e logísticos.

Diante disso, surge a necessidade de um sistema centralizado que permita maior controle, automação e segurança nas operações de transporte.

## 1.2. Objetivos do trabalho
Desenvolver um sistema TMS capaz de gerenciar operações de transporte de cargas, promovendo maior organização, controle e eficiência logística.

Objetivos Específicos: 

1. Permitir o cadastro de clientes, motoristas e veículos;

2. Registrar e gerenciar fretes;

3. Auxiliar na definição de rotas;

4. Controlar status de entrega;

5. Organizar informações relacionadas a documentos de transporte;

6. Gerar relatórios para apoio à tomada de decisão.

## 1.3. Justificativa
A crescente competitividade no setor logístico exige que as empresas adotem soluções tecnológicas para melhorar seus processos. Um sistema TMS contribui diretamente para a redução de custos operacionais, aumento da produtividade e melhoria no controle das informações. Além disso, o desenvolvimento deste projeto possibilita a aplicação prática de conceitos estudados em sala de aula, como modelagem de sistemas, banco de dados, integração de informações e lógica de programação, proporcionando aprendizado técnico e visão estratégica sobre a área de logística e tecnologia da informação.

## 1.4. Público alvo
O sistema atende diferentes perfis, alinhados aos atores definidos na modelagem (Diagrama de Casos de Uso):

**Motorista**: responsável por executar as entregas e atualizar o status no sistema. Geralmente utiliza dispositivos móveis e precisa de fluxos rápidos (ex.: iniciar rota, confirmar entrega, registrar ocorrência).

**Operador Logístico**: atua na operação interna, organizando cadastros base (clientes, pedidos, veículos, motoristas), criando fretes, planejando a sequência de entregas (rota) e atribuindo motorista e veículo.

**Operador de monitoramento**: acompanha entregas em andamento, registra/trata exceções (ocorrências) e apoia replanejamento quando necessário.

**Gestor**: acompanha indicadores e relatórios para tomada de decisão (painel operacional, relatórios por período/cliente/status).

**Administrador**: realiza a administração do sistema e manutenção de cadastros/configurações quando aplicável.

**Cliente** (quando previsto no escopo): consulta o status do pedido/entrega.

Dessa forma, o sistema deve considerar diferentes níveis de acesso e complexidade de uso, garantindo que cada perfil utilize as funcionalidades necessárias de forma clara, eficiente e adequada às suas atividades dentro da operação logística.
