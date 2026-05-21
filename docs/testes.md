# 5. Plano de Testes de Software — Log Brasil

## 5.1 Avaliação heurística

Cada integrante preenche a planilha **Avaliação_Heurística.xlsx** (material da disciplina).  
Consolidar em um único arquivo na pasta `docs/testes/heuristica/`.

**Achados já tratados no protótipo:**

- Consistência: menu “Planejar rotas” × “Execução (abertas)”.
- Correspondência: “Perfil de acesso” no cadastro de usuários.
- Feedback: captura GPS visível na tela RF07/RF08.
- Prevenção de erros: entrega sem GPS não é aceita pelo servidor.

## 5.2 Testes com usuário (observação)

Conforme enunciado: **um usuário distinto por integrante** do grupo.

| Integrante | Usuário testado (identificação) | Data | Relatório |
|------------|----------------------------------|------|-----------|
| Integrante 1 | *(nome, idade, relação com o domínio)* | *(dd/mm/aaaa)* | `docs/testes/relatorios/integrante1.docx` |
| Integrante 2 | … | … | `integrante2.docx` |
| Integrante 3 | … | … | `integrante3.docx` |
| … | … | … | … |

> Preencher a tabela acima e anexar os arquivos no formato **Relatório_de_Testes_com_Usuario.docx** fornecido pela disciplina.

### Tarefas (uma por integrante, mínimo)

| # | Tarefa | RF relacionado |
|---|--------|----------------|
| T1 | Cadastrar um pedido com endereço e buscar coordenadas | RF01 |
| T2 | Gerar uma viagem a partir do roteirizador | RF03 |
| T3 | Registrar uma ocorrência na parada (com descrição) | RF06 |
| T4 | Concluir entrega com foto, assinatura e GPS automático | RF07, RF08 |
| T5 | Abrir comprovante no histórico de viagens finalizadas | RF07 |

### Modelo de registro (resumo em Markdown)

Arquivo exemplo: `docs/testes/relatorios/MODELO_RESUMO.md` — use como rascunho antes de transferir ao DOCX oficial.

## 5.3 Consolidação

Após todos os testes, consolidar:

1. Problemas por severidade (crítico / médio / baixo).
2. Ações corretivas (esta entrega: GPS automático, rótulos, RF06/07 explícitos).
3. Pendências para versão final do produto.
