# 4. Projeto de Design de Interação — Log Brasil

## 4.1 Personas

*(Substituir pelos nomes e perfis reais de cada integrante do grupo — um persona por integrante.)*

**Persona 1 — Ana, coordenadora de expedição**  
Gestora de 38 anos; organiza pedidos e gera viagens no painel web; precisa de clareza entre “planejar rotas” e “acompanhar execução”.

**Persona 2 — Carlos, motorista**  
Motorista de 42 anos; usa smartphone no campo; precisa registrar ocorrência e comprovante com poucos toques e feedback claro.

**Persona 3 — Marina, cliente final**  
Consulta status da entrega pelo portal com CPF.

## 4.2 Mapa de empatia

*(Inserir mapa por persona — template Canva/RD Station conforme orientação da disciplina.)*

## 4.3 Protótipos de alta fidelidade

Protótipo implementado em PHP (`resources/views/`), acessível em ambiente local/XAMPP.

### Telas RF06 — Registrar ocorrência

| Tela | URL | Elementos |
|------|-----|-----------|
| Parada em rota | `/motorista/viagem/{id}/pedido/{pid}` | Botões **Comprovante** e **Ocorrência** |
| Modal ocorrência | Mesma tela | Descrição + foto opcional → `POST /api/motorista/divergencia` |

### Telas RF07 — Comprovante de entrega

| Tela | URL | Elementos |
|------|-----|-----------|
| Comprovante | `/motorista/viagem/{id}/pedido/{pid}/entrega` | Recebedor, foto, assinatura, confirmação |
| Consulta operação | `/viagens/finalizadas` → Apontamento | Foto, assinatura, recebedor, horário, GPS |
| Mapa histórico | `/viagens/finalizadas` → Mapa | Pin cliente (azul), pin motorista (verde), raio 100 m, distância e dentro/fora |

### Tela RF08 — Coordenada na confirmação

| Tela | URL | Comportamento |
|------|-----|---------------|
| Comprovante | `/motorista/.../entrega` | `watchPosition` ao abrir; ao **Confirmar entrega**, nova leitura GPS e envio automático no formulário |

**Cadastro de pedido** (`/pedidos`) mantém lat/long do endereço planejado; **RF08** registra a posição real no momento da entrega.

### Princípios gestálticos aplicados

| Princípio | Aplicação no Log Brasil |
|-----------|-------------------------|
| Proximidade | Métricas e ações da parada agrupadas no mesmo card (`lb-mot-stop-meta`). |
| Similaridade | Chips de status com cores consistentes (pendente, em rota, entregue, divergência). |
| Continuidade | Fluxo linear: pendente → indo → comprovante ou ocorrência. |
| **Fechamento** | Cards de parada com borda e padding fecham visualmente endereço + status; modal de ocorrência com botões alinhados em bloco único. |
| **Figura-fundo** | App motorista escuro: cards claros (`lb-m-card`) como figura; fundo `lb-mot-dark` como campo; botão primário verde/azul com contraste alto. |

### Regras de ouro (Shneiderman) — 8 regras

| # | Regra | Como o projeto atende |
|---|-------|------------------------|
| 1 | Buscar consistência | Navegação fixa no painel; ícones Font Awesome padronizados. |
| 2 | Atalhos frequentes | Atalhos no dashboard (Novo pedido, Planejar rotas). |
| 3 | Feedback informativo | Overlay “Carregando…”, status GPS na tela de comprovante, toasts no app motorista. |
| 4 | Diálogos com fim | Modais de ocorrência e apontamento com Cancelar/Confirmar explícitos. |
| 5 | Prevenir erros | Validação de GPS obrigatório antes de concluir; bloqueio de finalizar viagem com divergência pendente. |
| 6 | Reversão fácil | Limpar assinatura; cancelar modal de ocorrência. |
| 7 | Controle do usuário | Motorista escolhe comprovante ou ocorrência após “Indo até o cliente”. |
| 8 | Reduzir carga de memória | Rótulos “Planejar rotas” vs “Execução (abertas)” no menu. |

**Contradições reconhecidas nos testes (corrigidas nesta versão):**

- Cards de resumo na home do motorista pareciam clicáveis → estilo `pointer-events: none`.
- Rótulo “Papel” pouco claro → “Perfil de acesso” com descrições amigáveis.
- Confusão Viagens × Roteirizador → renomeação e `title` nos links do menu.

### Heurísticas de Nielsen (destaques)

| Heurística | Análise |
|------------|---------|
| Correspondência com o mundo real | “Perfil de acesso” em vez de jargão “Papel”; “Ocorrência” e “Comprovante” no app motorista. |
| Consistência e padrões | Mesma paleta navy/verde; botão primário sempre à direita nos modais. |
| Flexibilidade e eficiência | GPS em background na tela de entrega reduz espera no clique final. |
| Estética e minimalismo | Remoção de texto de template; foco em ações da parada. |
| Ajuda a reconhecer/recuperar erros | Toasts em vez de apenas `alert` no motorista; mensagens de GPS negado/timeout. |

## 4.4 Testes com protótipos

Ver `docs/testes.md` e pasta `docs/testes/relatorios/` para registros por integrante (modelo DOCX do enunciado).

**Tarefas sugeridas para teste:**

1. Cadastrar pedido com coordenadas.
2. Gerar viagem no planejamento de rotas.
3. Motorista: marcar “Indo” e registrar **ocorrência** com foto.
4. Motorista: registrar **comprovante** com GPS automático.
5. Monitoramento: aprovar divergência e consultar histórico.
