<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Pedido.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Verificar se é admin
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

try {
    // Criar pedido de teste
    $stmt = $db->prepare("
        INSERT INTO pedidos (
            usuario_id,
            endereco_rua,
            endereco_numero,
            endereco_bairro,
            forma_pagamento,
            valor_total,
            status
        ) VALUES (
            1,
            'Rua de Teste',
            '123',
            'Bairro Teste',
            'dinheiro',
            49.90,
            'pendente'
        )
    ");
    
    if ($stmt->execute()) {
        $pedido_id = $db->lastInsertId();
        
        // Adicionar item de teste
        $stmt = $db->prepare("
            INSERT INTO pedido_itens (
                pedido_id,
                produto_id,
                quantidade,
                preco_unitario
            ) VALUES (
                :pedido_id,
                1,
                1,
                49.90
            )
        ");
        
        $stmt->bindParam(':pedido_id', $pedido_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'pedido_id' => $pedido_id]);
        } else {
            throw new Exception("Erro ao criar item do pedido");
        }
    } else {
        throw new Exception("Erro ao criar pedido");
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
