-- LogBrasil: clientes cadastrados por CPF + geo no cliente; vínculo com pedidos.
-- Execute no Postgres/Supabase (SQL Editor). Idempotente onde possível.

CREATE TABLE IF NOT EXISTS public.clientes (
    id                  BIGSERIAL PRIMARY KEY,
    cpf                 VARCHAR(11) NOT NULL UNIQUE,
    nome_completo       VARCHAR(200) NOT NULL,
    telefone            VARCHAR(40),
    logradouro          VARCHAR(255) NOT NULL DEFAULT '',
    numero              VARCHAR(40) NOT NULL DEFAULT 'S/N',
    complemento         VARCHAR(120),
    bairro              VARCHAR(160) NOT NULL DEFAULT '',
    cidade              VARCHAR(120) NOT NULL DEFAULT '',
    uf                  CHAR(2) NOT NULL DEFAULT '',
    cep                 VARCHAR(12),
    referencia_entrega  TEXT,
    latitude            DOUBLE PRECISION NOT NULL DEFAULT 0,
    longitude           DOUBLE PRECISION NOT NULL DEFAULT 0,
    criado_em           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    atualizado_em       TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.clientes IS 'Cliente final por CPF; endereço e coordenadas compartilhados entre pedidos.';
CREATE INDEX IF NOT EXISTS idx_clientes_cpf ON public.clientes (cpf);

ALTER TABLE public.pedidos
    ADD COLUMN IF NOT EXISTS cliente_id BIGINT REFERENCES public.clientes (id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_pedidos_cliente_id ON public.pedidos (cliente_id);

DROP TRIGGER IF EXISTS tr_clientes_touch ON public.clientes;
CREATE TRIGGER tr_clientes_touch BEFORE UPDATE ON public.clientes
    FOR EACH ROW EXECUTE FUNCTION public.touch_atualizado_em();
