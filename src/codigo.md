# 7. Código-fonte — Log Brasil

Disponibilizar o código-fonte da aplicação.

## Estrutura principal

| Pasta | Conteúdo |
|-------|----------|
| `app/` | Controllers, Models, Services, core |
| `resources/views/` | Telas PHP (painel + motorista + cliente) |
| `public/` | Front controller, CSS, JS, uploads |
| `database/` | `schema.sql`, migrations Supabase |
| `scripts/` | Utilitários CLI (ex.: criar usuário inicial) |

## Executar localmente

1. Copiar `.env.example` para `.env` na raiz e preencher credenciais do Supabase.
2. Aplicar `database/schema.sql` e as migrations em `database/*.sql` no SQL Editor do Supabase.
3. Habilitar `pdo_pgsql` no PHP (XAMPP) e apontar o navegador para `http://localhost/LogBrasil/public`.
4. Criar o primeiro usuário com `php scripts/gerar_usuario_cli.php` e executar o SQL gerado no Supabase.

## Migration obrigatória

Executar no SQL Editor do Supabase:

`database/migration_entrega_geo.sql`