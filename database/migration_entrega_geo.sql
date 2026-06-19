-- LogBrasil — RF08: coordenadas capturadas na confirmação de entrega (motorista).
-- Executar no SQL Editor do Supabase (PostgreSQL).

ALTER TABLE public.viagem_pedidos
    ADD COLUMN IF NOT EXISTS entrega_latitude DOUBLE PRECISION,
    ADD COLUMN IF NOT EXISTS entrega_longitude DOUBLE PRECISION,
    ADD COLUMN IF NOT EXISTS entrega_geo_precisao_m DOUBLE PRECISION,
    ADD COLUMN IF NOT EXISTS entrega_geo_capturada_em TIMESTAMPTZ;

COMMENT ON COLUMN public.viagem_pedidos.entrega_latitude IS
    'Latitude GPS no momento da confirmação de entrega pelo motorista (RF08).';
COMMENT ON COLUMN public.viagem_pedidos.entrega_longitude IS
    'Longitude GPS no momento da confirmação de entrega pelo motorista (RF08).';
COMMENT ON COLUMN public.viagem_pedidos.entrega_geo_precisao_m IS
    'Precisão estimada (metros) retornada pelo dispositivo na captura.';
COMMENT ON COLUMN public.viagem_pedidos.entrega_geo_capturada_em IS
    'Timestamp da leitura GPS usada no registro da entrega.';
