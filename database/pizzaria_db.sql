CREATE DATABASE IF NOT EXISTS pizzaria_db;
USE pizzaria_db;

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('cliente', 'admin') DEFAULT 'cliente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT true
);

CREATE TABLE tamanhos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao VARCHAR(100),
    multiplicador_preco DECIMAL(3,2) NOT NULL,
    ativo BOOLEAN DEFAULT true
);

CREATE TABLE bordas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao VARCHAR(100),
    preco_adicional DECIMAL(10,2) NOT NULL,
    ativo BOOLEAN DEFAULT true
);

CREATE TABLE produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    imagem VARCHAR(255),
    ativo BOOLEAN DEFAULT true,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE produto_tamanhos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produto_id INT,
    tamanho_id INT,
    preco DECIMAL(10,2) NOT NULL,
    ativo BOOLEAN DEFAULT true,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (tamanho_id) REFERENCES tamanhos(id)
);

CREATE TABLE pedidos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    status ENUM('recebido', 'preparando', 'saiu_entrega', 'entregue') DEFAULT 'recebido',
    forma_pagamento ENUM('pix', 'cartao', 'dinheiro'),
    valor_total DECIMAL(10,2) NOT NULL,
    taxa_entrega DECIMAL(10,2) NOT NULL,
    endereco_entrega TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE pedido_itens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT,
    produto_id INT,
    tamanho_id INT,
    borda_id INT,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (tamanho_id) REFERENCES tamanhos(id),
    FOREIGN KEY (borda_id) REFERENCES bordas(id)
);
