# Validação das correções — Log Brasil

Guia passo a passo para conferir cada item do feedback da professora e as implementações da equipe.

**Ambiente:** `http://localhost/LogBrasil/public` (ajuste conforme `.env` / `CONF_BASE_URL`).

---

## Pré-requisito (obrigatório para RF08)

1. Abra o **SQL Editor** do Supabase.
2. Execute o arquivo `database/migration_entrega_geo.sql`.
3. Confirme as colunas:

```sql
SELECT column_name
FROM information_schema.columns
WHERE table_schema = 'public'
  AND table_name = 'viagem_pedidos'
  AND column_name IN (
    'entrega_latitude', 'entrega_longitude',
    'entrega_geo_precisao_m', 'entrega_geo_capturada_em'
  );
```

**Esperado:** 4 linhas retornadas.

---

## 1. Template substituído por conteúdo da equipe

| Passo | Ação | Resultado esperado |
|-------|------|-------------------|
| 1.1 | Abra `docs/design.md`, `docs/testes.md`, `docs/especificacao.md` | Conteúdo sobre Log Brasil e RF06–RF08; não apenas texto genérico do enunciado |
| 1.2 | Abra `README.md` | Links para arquivos em `docs/` que **existem** no repositório |
| 1.3 | Busque em `docs/design.md` por "RF06" / "Persona" | Seções preenchidas com o projeto |

**Pendência da equipe:** nomes dos integrantes no `README.md` e arquivos `.docx` em `docs/testes/relatorios/`.

---

## 2. RF06 — Tela de ocorrência

| Passo | Ação | Resultado esperado |
|-------|------|-------------------|
| 2.1 | `/motorista/login` → entrar com CPF/senha do motorista | App motorista abre |
| 2.2 | Viagens → viagem → parada pendente → **Indo até o cliente** | Aparecem **Comprovante** e **Ocorrência** |
| 2.3 | **Ocorrência** | Modal **RF06 — Registrar ocorrência** + descrição + foto opcional |
| 2.4 | Registrar com descrição | Parada aguarda análise; toast de sucesso |
| 2.5 | `/monitoramento/divergencias` | Ocorrência listada |

---

## 3. RF07 — Comprovante de entrega

| Passo | Ação | Resultado esperado |
|-------|------|-------------------|
| 3.1 | Parada em **indo** → **Comprovante** | URL `.../entrega` |
| 3.2 | Topo da tela | Chip **RF07 — Comprovante de entrega** |
| 3.3 | Preencher recebedor, foto, assinatura → confirmar | Parada **Feita** |
| 3.4 | `/viagens/finalizadas` → **Detalhes** → **Ver apontamento** | Recebedor, data, foto, assinatura |

---

## 4. RF08 — GPS automático na confirmação

| Passo | Ação | Resultado esperado |
|-------|------|-------------------|
| 4.1 | Tela `/entrega` (celular ou GPS simulado no Chrome) | Status **GPS pronto** com lat/lng |
| 4.2 | Confirmar entrega | Overlay de registro; sem campos manuais de coordenada |
| 4.3 | Rede → `POST .../api/motorista/concluir` | `entrega_latitude`, `entrega_longitude` no FormData |
| 4.4 | SQL: `SELECT entrega_latitude, entrega_longitude FROM viagem_pedidos WHERE estado_parada = 'entrega_feita' ORDER BY id DESC LIMIT 1` | Valores preenchidos |
| 4.5 | Apontamento no histórico | Bloco **Local GPS na entrega** |

**Teste negativo:** negar localização → mensagem de erro; entrega não grava.

> GPS funciona em **HTTPS** ou **localhost**.

---

## 5. Mapa do histórico — pin do motorista e raio 100 m

| Passo | Ação | Resultado esperado |
|-------|------|-------------------|
| 5.1 | `/viagens/finalizadas` → **Mapa** em viagem com entregas e GPS | Modal abre com legenda |
| 5.2 | No mapa, por parada entregue com GPS | Pin **azul** = cliente (cadastro); pin **verde** = apontamento motorista |
| 5.3 | Círculo tracejado | Raio de **100 m** em torno do cliente |
| 5.4 | Clique nos pins / popup | Distância em metros e texto **Dentro do raio (≤100 m)** ou **Fora do raio (>100 m)** |
| 5.5 | Painel lateral da legenda | Lista NF com distância e status dentro/fora |

---

## 6. Gestalt — Fechamento e Figura-Fundo

| Passo | Ação | Resultado esperado |
|-------|------|-------------------|
| 6.1 | `docs/design.md` → Princípios gestálticos | Fechamento e Figura-Fundo explicados |
| 6.2 | App motorista (tema escuro) | Cards claros sobre fundo escuro; hierarquia visual clara |

---

## 7. Heurísticas de Nielsen

| Heurística | Validação |
|------------|-----------|
| Correspondência com o mundo real | `/usuarios` → **Perfil de acesso** com rótulos amigáveis |
| Consistência | Menu **Planejar rotas** vs **Execução (abertas)** |
| Flexibilidade | GPS em background na tela de comprovante |
| Recuperação de erros | Toasts no app motorista |
| Documentação | Tabela em `docs/design.md` |

---

## 8. Oito regras de ouro

| Passo | Ação | Resultado esperado |
|-------|------|-------------------|
| 8.1 | `docs/design.md` → Regras de ouro | **8** regras, incluindo feedback e prevenir erros |
| 8.2 | App | Overlay de carregamento; bloqueio sem GPS; bloqueio de finalizar viagem com divergência pendente |

---

## 9. Contradições dos testes

| Problema | Validação |
|----------|-----------|
| Cards que pareciam botão | `/motorista` — métricas **não** navegam; só o card “Viagens em aberto” |
| Rótulo “Papel” | **Perfil de acesso** em usuários |
| Viagens × Roteirizador | `title` nos links do menu explicando cada função |
| Texto no relatório | Seção em `docs/design.md` |

---

## 10. Testes com usuário

| Passo | Ação | Resultado esperado |
|-------|------|-------------------|
| 10.1 | `docs/testes.md` | Tabela 1 usuário/integrante; tarefas T1–T5 |
| 10.2 | `docs/testes/relatorios/` | `MODELO_RESUMO.md` + (equipe) DOCX por integrante |
| 10.3 | Roteiro T1→T5 | Pedido → viagem → ocorrência ou comprovante+GPS → histórico/mapa |

---

## 11. Especificação RF06–RF08

| Passo | Ação | Resultado esperado |
|-------|------|-------------------|
| 11.1 | `docs/especificacao.md` | RF06, RF07, RF08 na tabela |
| 11.2 | Seção modelo de dados RF08 | Cita migration `migration_entrega_geo.sql` |

---

## Checklist rápido

- [ ] Migration RF08 no Supabase
- [ ] Documentação acadêmica sem template vazio
- [ ] RF06: Ocorrência + registro
- [ ] RF07: Comprovante + apontamento
- [ ] RF08: GPS automático + banco
- [ ] Mapa histórico: pins cliente/motorista + raio 100 m + distância
- [ ] Gestalt e 8 regras no `design.md`
- [ ] Perfil de acesso + menu renomeado
- [ ] Stats home motorista não clicáveis
- [ ] DOCX de testes por integrante (equipe)

---

## Roteiro integrado (demo completa)

1. Cadastrar pedido em `/pedidos` com **Buscar coordenadas**.
2. **Planejar rotas** → gerar viagem.
3. Motorista: **Indo** → **Comprovante** → confirmar (GPS).
4. Finalizar viagem no app motorista (se aplicável).
5. **Histórico** → **Mapa** → validar pins, círculo 100 m e distância.
6. **Ver apontamento** → conferir GPS textual.

---

*Última atualização: mapa do histórico com validação geográfica (raio 100 m).*
