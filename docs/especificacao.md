# 3. Especificação de Requisitos de Software — Log Brasil

## 3.1 Objetivo

Especificar o **Log Brasil — Sistema de Gestão de Entregas**, voltado ao cadastro de pedidos, planejamento de rotas, execução de viagens, registro de ocorrências e comprovantes de entrega, com portal do motorista em campo.

## 3.2 Escopo

- Cadastro de clientes, pedidos, veículos, motoristas e rotas territoriais.
- Roteirização e geração de viagens.
- App do motorista: status da parada, **ocorrências (RF06)**, **comprovante de entrega (RF07)** e **captura automática de GPS na confirmação (RF08)**.
- Painel de monitoramento e histórico com visualização de comprovantes.

**Fora do escopo desta versão:** faturamento, multi-empresa, rastreamento contínuo em tempo real da frota.

## 3.3 Requisitos funcionais (resumo)

| Código | Requisito | Implementação no protótipo |
|--------|-----------|----------------------------|
| RF01 | Cadastrar pedidos com endereço e geocodificação | `/pedidos` — lat/long via busca ou manual |
| RF02 | Cadastrar rotas, veículos e motoristas | `/rotas`, `/veiculos`, `/motoristas` |
| RF03 | Roteirizar e gerar viagem | `/roteirizador` → `POST /api/viagem/gerar` |
| RF04 | Acompanhar viagens em execução | `/viagens/abertas` |
| RF05 | Portal cliente acompanhar entrega | `/acompanhar` |
| **RF06** | **Registrar ocorrência na parada** | App motorista → parada → **Ocorrência** (texto + foto opcional) |
| **RF07** | **Comprovante de entrega** | App motorista → **Comprovante** (recebedor, foto, assinatura, horário) |
| **RF08** | **Capturar coordenada na confirmação** | GPS automático ao confirmar em `/motorista/.../entrega` |
| RF09 | Revisar divergências | `/monitoramento/divergencias` |
| RF10 | Histórico e comprovantes | `/viagens/finalizadas` → Apontamento |

## 3.4 Requisitos não funcionais

| Código | Descrição |
|--------|-----------|
| RNF01 | Interface web responsiva (painel + app motorista mobile-first). |
| RNF02 | Autenticação com senha (hash bcrypt) e CSRF nos formulários. |
| RNF03 | Armazenamento PostgreSQL (Supabase). |
| RNF04 | Upload seguro de imagens (tipo e tamanho validados). |

## 3.5 Atores

| Ator | Descrição |
|------|-----------|
| Administrador | Gestão de usuários e cadastros. |
| Gestor / Planejamento de rotas | Pedidos, roteirização e viagens. |
| Monitoramento | Viagens e divergências. |
| Motorista | App em `/motorista` — execução em campo. |
| Cliente final | Acompanhamento por CPF em `/acompanhar`. |

## 3.6 Modelo de dados — RF08

Campos em `viagem_pedidos` (migration `database/migration_entrega_geo.sql`):

- `entrega_latitude`, `entrega_longitude`
- `entrega_geo_precisao_m`, `entrega_geo_capturada_em`

Preenchidos automaticamente no `POST /api/motorista/concluir`.
