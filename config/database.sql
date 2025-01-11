-- Desabilitar verificação de foreign key temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- Criar tabela de tamanhos
DROP TABLE IF EXISTS produto_tamanhos;
DROP TABLE IF EXISTS tamanhos;
DROP TABLE IF EXISTS pedido_itens;
DROP TABLE IF EXISTS pedidos;

CREATE TABLE tamanhos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    multiplicador_preco DECIMAL(10,2) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    ativo BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criar tabela de preços específicos por produto e tamanho
CREATE TABLE produto_tamanhos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produto_id INT NOT NULL,
    tamanho_id INT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (tamanho_id) REFERENCES tamanhos(id) ON DELETE CASCADE
);

-- Criar tabela de pedidos
CREATE TABLE pedidos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    endereco_rua VARCHAR(255) NOT NULL,
    endereco_numero VARCHAR(20) NOT NULL,
    endereco_complemento VARCHAR(255),
    endereco_bairro VARCHAR(100) NOT NULL,
    endereco_referencia VARCHAR(255),
    forma_pagamento VARCHAR(50) NOT NULL,
    troco_para DECIMAL(10,2),
    valor_total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Criar tabela de itens do pedido
CREATE TABLE pedido_itens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    tamanho_id INT,
    borda_id INT,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (tamanho_id) REFERENCES tamanhos(id),
    FOREIGN KEY (borda_id) REFERENCES bordas(id)
);

-- Inserir tamanhos padrão
INSERT INTO tamanhos (nome, multiplicador_preco, ordem) VALUES 
('Pequena', 1.0, 1),
('Média', 1.3, 2),
('Grande', 1.5, 3),
('Família', 1.8, 4);

-- Reabilitar verificação de foreign key
SET FOREIGN_KEY_CHECKS = 1;
