-- Opcional ao portal cliente: vínculo CPF para garantir consulta apenas do próprio documento.
ALTER TABLE public.usuarios ADD COLUMN IF NOT EXISTS acompanhar_cpf VARCHAR(11);

COMMENT ON COLUMN public.usuarios.acompanhar_cpf IS 'Somente papel cliente: dígitos do CPF que pode consultar na tela /acompanhar.';
