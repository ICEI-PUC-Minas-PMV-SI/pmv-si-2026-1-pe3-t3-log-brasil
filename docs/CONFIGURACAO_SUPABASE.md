# Configuração LogBrasil + Supabase (PostgreSQL)

Este guia explica **onde obter cada dado no painel do Supabase** e como preencher o `.env` na raiz do repositório (`c:\xampp\htdocs\LogBrasil\.env`) para o backend **PHP (PDO)**.

> **Importante:** valores como `db.SEU_PROJECT_REF.supabase.co` ou `SEU_PROJECT_REF` são **placeholders**. Substituí-los pelo valor real do seu projeto é obrigatório.

---

## Visão geral do fluxo

1. Criar (ou abrir) um **projeto** no Supabase.
2. Copiar **credenciais do banco** em *Project Settings → Database*.
3. (Opcional) Ajustar **rede** / **SSL** se o PHP não conectar.
4. Executar o script **`database/schema.sql`** no SQL Editor.
5. Criar um **usuário de login** da aplicação (`public.usuarios`) com o script CLI do projeto.
6. Preencher o **`.env`** e habilitar **`pdo_pgsql`** no PHP (XAMPP).
7. Validar `BASE_URL` conforme a URL no navegador.

---

## 1. Acessar o projeto

1. Entre em [https://supabase.com](https://supabase.com) e faça login.
2. **Dashboard** → selecione o **projeto** LogBrasil (ou crie um novo: **New project**).
3. Anote o **Project reference** (nome curto na URL ou em *Settings → General → Reference ID*).  
   Ele aparece em URLs do tipo: `https://supabase.com/dashboard/project/<referencia>/...`

Isso **não** entra direto no `.env`, mas ajuda a reconhecer hosts e URLs.

---

## 2. Credenciais PostgreSQL (PDO / PHP)

### Onde no Supabase

Menu lateral: **⚙️ Project Settings** (ícone de engrenagem) → **Database**.

### 2.1 Host, porta, nome do banco, usuário

Na seção **Connection parameters** (ou equivalente), localize:

| Campo no painel              | Variável `.env` | Observação |
|-----------------------------|-----------------|------------|
| **Host**                     | `DB_HOST`       | Ex.: `db.xxxxx.supabase.co` (substitua pelo host exibido; **não** use o texto `SEU_PROJECT_REF`). |
| **Database name**           | `DB_DATABASE`   | Normalmente `postgres`. |
| **Port**                    | `DB_PORT`       | Geralmente **`5432`** (conexão direta). Para **pooler** pode ser **`6543`** (veja abaixo). |
| **User**                    | `DB_USERNAME`   | Muitas vezes `postgres`. Pooler pode usar forma `postgres.<ref>`. |

### 2.2 Senha do banco (**Database password**)

- Definida na **criação** do projeto (ou redefina em **Database → Reset database password**, se disponível para seu plano).
- Corresponde a **`DB_PASSWORD`** no `.env`.

> **Boas práticas:** não versionar `.env`; cada desenvolvedor copia `.env.example` → `.env` com valores locais ou de sandbox.

### 2.3 String de conexão (opcional como referência)

Na mesma área há **URI** ou **Connection string**. Use-a só para conferir host, porta, usuário e `dbname`; o PHP monta DSN assim:

```text
pgsql:host=<DB_HOST>;port=<DB_PORT>;dbname=<DB_DATABASE>
```

### 2.4 Pool de conexões (PgBouncer) — modo transação

Se o painel recomendar **Session pooler** / **Transaction mode** (porta **6543**):

- **`DB_HOST`**: usar o hostname do **pooler** indicado pelo Supabase (ex.: terminação `pooler.supabase.com`), não confundir com o host direto `db.`…
- **`DB_PORT`**: frequentemente **`6543`**.
- **`DB_USERNAME`**: pode ser no formato **`postgres.<PROJECT_REF>`** — copie **exatamente** o que o painel mostrar na string de pooler.

A documentação oficial do Supabase costuma estar em **Connect** ou **Database** ao clicar em “Connection pooling”.

---

## 3. Variáveis de API (uso futuro REST / outros clientes)

**Project Settings → API**

| Painel Supabase              | `.env` (exemplo projeto) |
|-----------------------------|----------------------------|
| **Project URL**             | pode espelhar `SUPABASE_URL` |
| **anon public** key         | `SUPABASE_ANON_KEY` (**nunca** expor como segredo forte; só chave pública) |
| **service_role**            | **`SUPABASE_SERVICE_ROLE_KEY`** — **somente servidor**, nunca no front |

O painel PHP atual usa **PDO direto** no Postgres; essas chaves são úteis se outro módulo (ex.: Next.js em `web/`) consumir a API.

---

## 4. Aplicar o esquema SQL (tabelas)

1. Menu **SQL** → **SQL Editor** → **New query**.
2. Cole o conteúdo de **`database/schema.sql`** (raiz do repositório).
3. Execute (**Run**).
4. Verifique se não há erros. Em caso de trigger com `EXECUTE FUNCTION` e Postgres antigo, o Supabase pode exigir sintaxe equivalente conforme versão — ajustar só se o editor reportar erro.

---

## 5. Primeiro usuário do painel (tabela `public.usuarios`)

O login PHP valida **`usuarios`** no Postgres (não o Auth mágico do Supabase só com essa tabela).

Na máquina de desenvolvimento, na pasta do projeto:

```bash
php scripts/gerar_usuario_cli.php email@empresa.com "SenhaSegura" "Nome Completo" admin
```

Cole o `INSERT` gerado no **SQL Editor** do Supabase e execute.

---

## 6. `.env` — mapa rápido (PHP + LogBrasil)

| Variável               | Origem típica no Supabase / ambiente |
|------------------------|----------------------------------------|
| `BASE_URL`             | URL até `public/` (ex.: `http://localhost/LogBrasil/public`). |
| `DB_HOST`              | Settings → Database → **Host**. |
| `DB_PORT`              | **5432** (direto) ou **6543** (pooler), conforme o modo escolhido. |
| `DB_DATABASE`          | Nome do banco (normalmente **postgres**). |
| `DB_USERNAME`          | Usuário exibido (atenção ao formato do pooler). |
| `DB_PASSWORD`          | Senha do banco (definição ou reset na área Database). |
| `OPENROUTESERVICE_API_KEY` | Conta OpenRouteService (separado do Supabase). |
| `APP_DEBUG`              | `0` produção; `1` apenas depuração (mensagens extras no login para administrador). |

**Conferência rápida:** se `DB_HOST` ainda contiver `SEU_PROJECT_REF`, a conexão **não vai funcionar** até corrigir.

---

## 7. PHP (XAMPP) — extensão PostgreSQL

Erros como **“could not find driver”** / **“não foi possível encontrar o driver”** indicam PDO sem PostgreSQL:

1. Abra `php.ini` (no XAMPP, em geral `C:\xampp\php\php.ini`).
2. Descomente:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
3. Reinicie o **Apache**.

---

## 8. Firewall / rede (se “connection timed out”)

- Supabase hospeda o Postgres na nuvem; a máquina local precisa de **saída HTTPS/TCP** para a porta configurada (**5432** ou **6543**).
- Redes corporativas às vezes bloqueiam; testar outra rede ou VPN conforme política da empresa.

### 8.1 “Unknown host” / “could not translate host name”

Se a mensagem PDO cita **`Unknown host`** para um host como `db.xxxxx.supabase.co`:

1. No Windows, teste: `nslookup db.SEU_REF.supabase.co` — se retornar **Non-existent domain**, o hostname **não existe** (projeto apagado, pausado além do limite, ou Reference ID errado).
2. Entre em [supabase.com/dashboard](https://supabase.com/dashboard) e confirme que o **projeto aparece** e não está “Paused” de forma irreversível.
3. **Settings → Database → Connection parameters** → copie de novo **Host** e redefina a senha se necessário.
4. Se não houver projeto: **New project** → execute `database/schema.sql` → crie usuário com `scripts/gerar_usuario_cli.php`.

---

## 9. Mensagem genérica na tela de login

Quando aparece:

> **Não foi possível validar o acesso neste momento. Tente novamente em instantes.**

Checklist objetiva para desenvolvedores:

1. **`DB_HOST`** sem placeholder; porta coerente (direto vs pooler).
2. **`DB_USERNAME` / `DB_PASSWORD`** corretos; usuário existe no projeto.
3. **`pdo_pgsql`** ativo e Apache reiniciado.
4. Banco aceita conexão (testar pelo **SQL Editor** no Supabase primeiro).
5. Com **`APP_DEBUG=1`** no `.env`, ao tentar login pode aparecer faixa azul extra com orientação (**ex.: driver PHP**) — apenas para admins; não deixar `1` em produção exposta à internet sem necessidade.

---

## 10. Referências externas úteis

- [Dashboard Supabase](https://supabase.com/dashboard)
- [Documentação Supabase — Connecting to Postgres](https://supabase.com/docs/guides/database/connecting-to-postgres)
- [OpenRouteService](https://openrouteservice.org/) (chaves em **Developers → API Keys** ou portal equivalente da sua conta)

---

*Documento destinado aos desenvolvedores do repositório LogBrasil.*  
*Não inclua senhas ou chaves reais neste arquivo; use sempre `.env` local e placeholders em exemplos.*
