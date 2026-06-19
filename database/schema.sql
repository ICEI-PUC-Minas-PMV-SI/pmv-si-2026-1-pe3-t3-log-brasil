-- LogBrasil: esquema PostgreSQL (Supabase).
-- Execução manual no SQL Editor do Supabase; não é migration automatizada.

CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ---------------------------------------------------------------------------
-- Usuários do painel (autenticação própria; senha com bcrypt/hash no PHP).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.usuarios (
    id              BIGSERIAL PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    senha_hash      TEXT NOT NULL,
    nome_completo   VARCHAR(255) NOT NULL,
    papel           VARCHAR(50) NOT NULL DEFAULT 'gestor'
        CHECK (papel IN (
            'admin',
            'gestor',
            'monitoramento',
            'roteirizador',
            'cliente',
            'motorista'
        )),
    acompanhar_cpf  VARCHAR(11),
    ativo           BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    atualizado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_usuarios_email ON public.usuarios (email);

COMMENT ON TABLE public.usuarios IS 'Usuários com acesso ao painel web.';
COMMENT ON COLUMN public.usuarios.senha_hash IS 'Senha apenas como hash (password_hash/password_verify PHP).';


-- ---------------------------------------------------------------------------
-- Unidade padrão da operação (depósito) para rotas e distância com ORS.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.unidade_padrao (
    id                  SMALLINT PRIMARY KEY DEFAULT 1 CHECK (id = 1),
    nome                VARCHAR(200) NOT NULL DEFAULT 'Matriz LogBrasil',
    logradouro          VARCHAR(255) NOT NULL,
    numero              VARCHAR(40) NOT NULL DEFAULT 'S/N',
    complemento         VARCHAR(120),
    bairro              VARCHAR(120),
    cidade              VARCHAR(120) NOT NULL,
    uf                  CHAR(2) NOT NULL,
    cep                 VARCHAR(12),
    latitude            DOUBLE PRECISION NOT NULL DEFAULT 0,
    longitude           DOUBLE PRECISION NOT NULL DEFAULT 0,
    observacao          TEXT,
    atualizado_em       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    atualizado_por_id   BIGINT REFERENCES public.usuarios (id)
);

COMMENT ON TABLE public.unidade_padrao IS 'Registro singleton (id=1) da origem das viagens.';


-- ---------------------------------------------------------------------------
-- Rotas logísticas (conjunto de cidade/bairros atendidos).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.rotas (
    id              BIGSERIAL PRIMARY KEY,
    nome            VARCHAR(200) NOT NULL UNIQUE,
    ativo           BOOLEAN NOT NULL DEFAULT TRUE,
    observacao      TEXT,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    atualizado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.rotas IS 'Rotas nomeadas para agrupar pedidos geograficamente.';


CREATE TABLE IF NOT EXISTS public.rota_cidades (
    id              BIGSERIAL PRIMARY KEY,
    rota_id         BIGINT NOT NULL REFERENCES public.rotas (id) ON DELETE CASCADE,
    cidade          VARCHAR(120) NOT NULL,
    uf              CHAR(2) NOT NULL,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (rota_id, cidade, uf)
);

CREATE INDEX IF NOT EXISTS idx_rota_cidades_lookup ON public.rota_cidades (cidade, uf);

COMMENT ON TABLE public.rota_cidades IS 'Vínculo cidade+UF inclusa na rota.';


CREATE TABLE IF NOT EXISTS public.rota_bairros (
    id              BIGSERIAL PRIMARY KEY,
    rota_id         BIGINT NOT NULL REFERENCES public.rotas (id) ON DELETE CASCADE,
    bairro          VARCHAR(160) NOT NULL,
    cidade          VARCHAR(120) NOT NULL,
    uf              CHAR(2) NOT NULL,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (rota_id, bairro, cidade, uf)
);

CREATE INDEX IF NOT EXISTS idx_rota_bairros_lookup ON public.rota_bairros (bairro, cidade, uf);

COMMENT ON TABLE public.rota_bairros IS 'Bairros específicos da rota; priorizado na atribuição automática.';


CREATE TABLE IF NOT EXISTS public.veiculos (
    id              BIGSERIAL PRIMARY KEY,
    placa           VARCHAR(12) NOT NULL UNIQUE,
    descricao       VARCHAR(200),
    marca_modelo    VARCHAR(160),
    ano             SMALLINT,
    capacidade_kg   NUMERIC(12, 2),
    tipo            VARCHAR(60),
    frota_interna   VARCHAR(80),
    ativo           BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    atualizado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.veiculos IS 'Cadastro da frota.';


CREATE TABLE IF NOT EXISTS public.motoristas (
    id                      BIGSERIAL PRIMARY KEY,
    nome_completo           VARCHAR(200) NOT NULL,
    senha_hash              TEXT,
    foto_perfil             VARCHAR(520),
    cpf                     VARCHAR(20) UNIQUE,
    cnh_numero              VARCHAR(40),
    cnh_categoria           VARCHAR(10),
    telefone                VARCHAR(40),
    email                   VARCHAR(255),
    empresa_terceira        BOOLEAN NOT NULL DEFAULT FALSE,
    nome_empresa_terceira   VARCHAR(160),
    ativo                   BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em               TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    atualizado_em           TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.motoristas IS 'Motoristas que executam viagens.';


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

COMMENT ON TABLE public.clientes IS 'Cliente final por CPF; endereço e coordenadas reutilizados entre pedidos.';
CREATE INDEX IF NOT EXISTS idx_clientes_cpf ON public.clientes (cpf);


CREATE TABLE IF NOT EXISTS public.pedidos (
    id                          BIGSERIAL PRIMARY KEY,
    cliente_id                  BIGINT REFERENCES public.clientes (id) ON DELETE SET NULL,
    numero_pedido               VARCHAR(64) NOT NULL UNIQUE,
    estado                      VARCHAR(40) NOT NULL DEFAULT 'pendente_roterizador'
        CHECK (estado IN (
            'pendente_roterizador',
            'alocado_rota',
            'em_viagem',
            'entregue',
            'cancelado'
        )),
    rota_id                     BIGINT REFERENCES public.rotas (id) ON DELETE SET NULL,
    nome_destinatario           VARCHAR(200) NOT NULL,
    telefone_destinatario       VARCHAR(40),
    logradouro                  VARCHAR(255) NOT NULL,
    numero                      VARCHAR(40) NOT NULL DEFAULT 'S/N',
    complemento                 VARCHAR(120),
    bairro                      VARCHAR(160) NOT NULL,
    cidade                      VARCHAR(120) NOT NULL,
    uf                          CHAR(2) NOT NULL,
    cep                         VARCHAR(12),
    referencia_entrega          TEXT,
    latitude                    DOUBLE PRECISION NOT NULL DEFAULT 0,
    longitude                   DOUBLE PRECISION NOT NULL DEFAULT 0,
    peso_total_kg               NUMERIC(12, 3) NOT NULL DEFAULT 0,
    quantidade_entregas         SMALLINT NOT NULL DEFAULT 1 CHECK (quantidade_entregas >= 1),
    distancia_ordem_metros      NUMERIC(14, 2),
    ordem_geo_sugerida          SMALLINT,
    observacao_interna          TEXT,
    criado_em                   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    atualizado_em               TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_pedidos_estado_rota ON public.pedidos (estado, rota_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_numero ON public.pedidos (numero_pedido);

COMMENT ON TABLE public.pedidos IS 'Pedidos de entrega; geocodificação e rota vindas do cadastro e base de rotas.';
COMMENT ON COLUMN public.pedidos.quantidade_entregas IS 'Pontos físicos dentro do mesmo número de NF/pedido, se aplicável.';

CREATE INDEX IF NOT EXISTS idx_pedidos_cliente_id ON public.pedidos (cliente_id);


CREATE TABLE IF NOT EXISTS public.pedido_itens (
    id              BIGSERIAL PRIMARY KEY,
    pedido_id       BIGINT NOT NULL REFERENCES public.pedidos (id) ON DELETE CASCADE,
    descricao       VARCHAR(400) NOT NULL,
    quantidade      NUMERIC(12, 3) NOT NULL DEFAULT 1,
    peso_unit_kg    NUMERIC(12, 3),
    sku             VARCHAR(80),
    observacao      TEXT
);

CREATE INDEX IF NOT EXISTS idx_pedido_itens_pedido ON public.pedido_itens (pedido_id);


CREATE TABLE IF NOT EXISTS public.viagens (
    id                      BIGSERIAL PRIMARY KEY,
    rota_id                 BIGINT NOT NULL REFERENCES public.rotas (id),
    veiculo_id              BIGINT REFERENCES public.veiculos (id),
    motorista_id            BIGINT REFERENCES public.motoristas (id),
    status                  VARCHAR(30) NOT NULL DEFAULT 'aberta'
        CHECK (status IN ('aberta', 'finalizada')),
    data_largada_prevista   TIMESTAMPTZ,
    lead_planejado_texto    VARCHAR(200),
    data_largada_real       TIMESTAMPTZ,
    data_retorno_prevista   TIMESTAMPTZ,
    peso_total_kg           NUMERIC(14, 3) DEFAULT 0,
    qt_entregas             INTEGER DEFAULT 0,
    distancia_metros_prev   NUMERIC(14, 2),
    distancia_via_ors_metros NUMERIC(14, 2),
    ordem_geo_json          TEXT,
    observacao_planejamento TEXT,
    criado_em               TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    atualizado_em           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    finalizado_em           TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_viagens_status ON public.viagens (status);
CREATE INDEX IF NOT EXISTS idx_viagens_rota ON public.viagens (rota_id);

COMMENT ON TABLE public.viagens IS 'Planejamentos executados; pedidos vinculados em viagem_pedidos.';


CREATE TABLE IF NOT EXISTS public.divergencias_entrega (
    id                  BIGSERIAL PRIMARY KEY,
    viagem_id           BIGINT NOT NULL REFERENCES public.viagens (id) ON DELETE CASCADE,
    pedido_id           BIGINT REFERENCES public.pedidos (id) ON DELETE SET NULL,
    descricao           TEXT NOT NULL,
    foto_url            TEXT,
    reportado_em        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    origem_usuario_id   BIGINT REFERENCES public.usuarios (id),
    dados_extras_json   TEXT,
    revisao_estado      VARCHAR(28) NOT NULL DEFAULT 'pendente_aprovacao'
        CHECK (revisao_estado IN ('pendente_aprovacao', 'aprovada', 'rejeitada')),
    revisado_em         TIMESTAMPTZ,
    revisado_por_usuario_id BIGINT REFERENCES public.usuarios (id),
    motorista_id        BIGINT REFERENCES public.motoristas (id)
);

CREATE INDEX IF NOT EXISTS idx_div_viagem ON public.divergencias_entrega (viagem_id);

COMMENT ON TABLE public.divergencias_entrega IS 'Registros de inconsistências relatadas pelo motorista ou conferência.';


CREATE TABLE IF NOT EXISTS public.viagem_pedidos (
    id                      BIGSERIAL PRIMARY KEY,
    viagem_id               BIGINT NOT NULL REFERENCES public.viagens (id) ON DELETE CASCADE,
    pedido_id               BIGINT NOT NULL REFERENCES public.pedidos (id) ON DELETE RESTRICT,
    ordem_entrega           SMALLINT NOT NULL DEFAULT 1,
    estado_parada           VARCHAR(32) NOT NULL DEFAULT 'pendente'
        CHECK (estado_parada IN (
            'pendente',
            'indo',
            'entrega_feita',
            'divergencia_aguardando',
            'resolvido_divergencia'
        )),
    indo_em                  TIMESTAMPTZ,
    recebedor_nome           VARCHAR(260),
    foto_mercadoria          VARCHAR(520),
    assinatura_png           TEXT,
    entregue_em              TIMESTAMPTZ,
    entrega_latitude         DOUBLE PRECISION,
    entrega_longitude        DOUBLE PRECISION,
    entrega_geo_precisao_m   DOUBLE PRECISION,
    entrega_geo_capturada_em TIMESTAMPTZ,
    divergencia_id           BIGINT REFERENCES public.divergencias_entrega (id) ON DELETE SET NULL,
    UNIQUE (viagem_id, pedido_id)
);

CREATE INDEX IF NOT EXISTS idx_viagem_pedidos_viagem ON public.viagem_pedidos (viagem_id, ordem_entrega);

COMMENT ON TABLE public.viagem_pedidos IS 'Sequência de entregas da viagem.';


-- ---------------------------------------------------------------------------
-- Gatilhos de atualização automática em colonas atualizado_em (opcional).
-- ---------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION public.touch_atualizado_em()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    NEW.atualizado_em := NOW();
    RETURN NEW;
END $$;

DROP TRIGGER IF EXISTS tr_usuarios_touch ON public.usuarios;
CREATE TRIGGER tr_usuarios_touch BEFORE UPDATE ON public.usuarios
    FOR EACH ROW EXECUTE FUNCTION public.touch_atualizado_em();

DROP TRIGGER IF EXISTS tr_rotas_touch ON public.rotas;
CREATE TRIGGER tr_rotas_touch BEFORE UPDATE ON public.rotas
    FOR EACH ROW EXECUTE FUNCTION public.touch_atualizado_em();

DROP TRIGGER IF EXISTS tr_veiculos_touch ON public.veiculos;
CREATE TRIGGER tr_veiculos_touch BEFORE UPDATE ON public.veiculos
    FOR EACH ROW EXECUTE FUNCTION public.touch_atualizado_em();

DROP TRIGGER IF EXISTS tr_motoristas_touch ON public.motoristas;
CREATE TRIGGER tr_motoristas_touch BEFORE UPDATE ON public.motoristas
    FOR EACH ROW EXECUTE FUNCTION public.touch_atualizado_em();

DROP TRIGGER IF EXISTS tr_clientes_touch ON public.clientes;
CREATE TRIGGER tr_clientes_touch BEFORE UPDATE ON public.clientes
    FOR EACH ROW EXECUTE FUNCTION public.touch_atualizado_em();

DROP TRIGGER IF EXISTS tr_pedidos_touch ON public.pedidos;
CREATE TRIGGER tr_pedidos_touch BEFORE UPDATE ON public.pedidos
    FOR EACH ROW EXECUTE FUNCTION public.touch_atualizado_em();

DROP TRIGGER IF EXISTS tr_viagens_touch ON public.viagens;
CREATE TRIGGER tr_viagens_touch BEFORE UPDATE ON public.viagens
    FOR EACH ROW EXECUTE FUNCTION public.touch_atualizado_em();

-- Usuário administrador inicial (senha será substituída em produção):
-- INSERT INTO usuarios (email, senha_hash, nome_completo, papel) VALUES
-- ('admin@logbrasil.local', '$2y$12$REPLACE_WITH_PASSWORD_HASH_FROM_PHP', 'Administrador', 'admin');
