-- LogBrasil: papéis de usuário, motorista portal, paradas por viagem, divergência com revisão.
-- Rode no Postgres/Supabase (SQL Editor). Ajustes idempotentes com IF NOT EXISTS.

-- Papéis (migra operador/visor legados antes de reaplicar o CHECK).
UPDATE public.usuarios SET papel = 'gestor'
    WHERE papel IN ('operador', 'visor') AND papel IS NOT NULL;

UPDATE public.usuarios SET papel = 'roteirizador' WHERE papel = 'roterizador';

ALTER TABLE public.usuarios DROP CONSTRAINT IF EXISTS usuarios_papel_check;
ALTER TABLE public.usuarios ADD CONSTRAINT usuarios_papel_check CHECK (papel IN (
    'admin', 'gestor', 'monitoramento', 'roteirizador', 'cliente', 'motorista'
));
ALTER TABLE public.usuarios ALTER COLUMN papel SET DEFAULT 'gestor';

ALTER TABLE public.motoristas ADD COLUMN IF NOT EXISTS senha_hash TEXT;
ALTER TABLE public.motoristas ADD COLUMN IF NOT EXISTS foto_perfil VARCHAR(520);

COMMENT ON COLUMN public.motoristas.senha_hash IS 'Opcional — acesso ao app motorista por CPF+senha.';
COMMENT ON COLUMN public.motoristas.foto_perfil IS 'Nome de arquivo público sob /uploads/motoristas/';

-- Parada na viagem
ALTER TABLE public.viagem_pedidos ADD COLUMN IF NOT EXISTS estado_parada VARCHAR(32) NOT NULL DEFAULT 'pendente';
ALTER TABLE public.viagem_pedidos ADD COLUMN IF NOT EXISTS indo_em TIMESTAMPTZ;
ALTER TABLE public.viagem_pedidos ADD COLUMN IF NOT EXISTS recebedor_nome VARCHAR(260);
ALTER TABLE public.viagem_pedidos ADD COLUMN IF NOT EXISTS foto_mercadoria VARCHAR(520);
ALTER TABLE public.viagem_pedidos ADD COLUMN IF NOT EXISTS assinatura_png TEXT;
ALTER TABLE public.viagem_pedidos ADD COLUMN IF NOT EXISTS entregue_em TIMESTAMPTZ;
ALTER TABLE public.viagem_pedidos ADD COLUMN IF NOT EXISTS divergencia_id BIGINT;

ALTER TABLE public.viagem_pedidos DROP CONSTRAINT IF EXISTS viagem_pedidos_estado_parada_check;
ALTER TABLE public.viagem_pedidos ADD CONSTRAINT viagem_pedidos_estado_parada_check CHECK (
    estado_parada IN ('pendente', 'indo', 'entrega_feita', 'divergencia_aguardando', 'resolvido_divergencia')
);

COMMENT ON COLUMN public.viagem_pedidos.estado_parada IS 'Estado por parada dentro da viagem.';

ALTER TABLE public.divergencias_entrega ADD COLUMN IF NOT EXISTS revisao_estado VARCHAR(28) DEFAULT 'aprovada';
ALTER TABLE public.divergencias_entrega ADD COLUMN IF NOT EXISTS revisado_em TIMESTAMPTZ;
ALTER TABLE public.divergencias_entrega ADD COLUMN IF NOT EXISTS revisado_por_usuario_id BIGINT REFERENCES public.usuarios (id);
ALTER TABLE public.divergencias_entrega ADD COLUMN IF NOT EXISTS motorista_id BIGINT REFERENCES public.motoristas (id);

UPDATE public.divergencias_entrega SET revisao_estado = COALESCE(NULLIF(trim(revisao_estado), ''), 'aprovada');

ALTER TABLE public.divergencias_entrega DROP CONSTRAINT IF EXISTS divergencias_revisao_check;
ALTER TABLE public.divergencias_entrega ADD CONSTRAINT divergencias_revisao_check CHECK (
    revisao_estado IN ('pendente_aprovacao', 'aprovada', 'rejeitada')
);

ALTER TABLE public.viagem_pedidos DROP CONSTRAINT IF EXISTS viagem_pedidos_divergencia_fk;

ALTER TABLE public.viagem_pedidos
    ADD CONSTRAINT viagem_pedidos_divergencia_fk FOREIGN KEY (divergencia_id)
    REFERENCES public.divergencias_entrega (id) ON DELETE SET NULL;

ALTER TABLE public.divergencias_entrega ALTER COLUMN revisao_estado SET DEFAULT 'pendente_aprovacao';

-- Opcional ao portal cliente: CPF autorizado para a tela /acompanhar (ver migration_usuario_acompanhar_cpf.sql também).
ALTER TABLE public.usuarios ADD COLUMN IF NOT EXISTS acompanhar_cpf VARCHAR(11);
