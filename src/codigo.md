# Código-fonte — Log Brasil

## Estrutura principal

| Pasta | Conteúdo |
|-------|----------|
| `app/` | Controllers, Models, Services, core |
| `resources/views/` | Telas PHP (painel + motorista + cliente) |
| `public/` | Front controller, CSS, JS, uploads |
| `database/` | `schema.sql`, migrations Supabase |

## Executar localmente

1. Configurar `.env` (copiar de `.env.example`).
2. Aplicar `database/schema.sql` e migrations em `database/*.sql` no Supabase.
3. Apontar o virtual host ou `http://localhost/LogBrasil/public`.

## RF08 — Migration obrigatória

Executar no SQL Editor do Supabase:

`database/migration_entrega_geo.sql`

## Documentação de API e rotas

Ver [docs/DOCUMENTACAO.md](../docs/DOCUMENTACAO.md).
