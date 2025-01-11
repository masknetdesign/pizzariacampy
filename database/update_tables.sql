USE pizzaria_db;

-- Adicionar coluna ativo se não existir
ALTER TABLE categorias
ADD COLUMN IF NOT EXISTS ativo BOOLEAN DEFAULT true;

-- Criar diretório de uploads se não existir
-- Isso precisa ser feito manualmente ou via PHP
