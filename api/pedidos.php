<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Carrinho.php';
require_once '../classes/Pedido.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$carrinho = new Carrinho($db);
$pedido = new Pedido($db);

// Verificar se está logado
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario = $auth->getUser();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $pedido_info = $pedido->buscarPedido($_GET['id']);
    
    // Verificar se o pedido existe e pertence ao usuário
    if (!$pedido_info || $pedido_info['usuario_id'] != $usuario['id']) {
        http_response_code(404);
        echo json_encode(['error' => 'Pedido não encontrado']);
        exit;
    }
    
    $itens = $pedido->buscarItensPedido($_GET['id']);
    
    echo json_encode([
        'pedido' => $pedido_info,
        'itens' => $itens
    ]);
    
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se há itens no carrinho
    $itens = $carrinho->getItens();
    if (empty($itens)) {
        http_response_code(400);
        echo json_encode(['error' => 'Carrinho vazio']);
        exit;
    }
    
    // Validar campos obrigatórios
    $campos_obrigatorios = ['rua', 'numero', 'bairro', 'forma_pagamento'];
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            http_response_code(400);
            echo json_encode(['error' => 'Campo obrigatório não preenchido: ' . $campo]);
            exit;
        }
    }
    
    try {
        // Iniciar transação
        $db->beginTransaction();
        
        // Criar pedido
        $stmt = $db->prepare("
            INSERT INTO pedidos (
                usuario_id, 
                endereco_rua, 
                endereco_numero, 
                endereco_complemento, 
                endereco_bairro, 
                endereco_referencia,
                forma_pagamento,
                troco_para,
                valor_total,
                status
            ) VALUES (
                :usuario_id,
                :rua,
                :numero,
                :complemento,
                :bairro,
                :referencia,
                :forma_pagamento,
                :troco_para,
                :valor_total,
                'pendente'
            )
        ");
        
        $stmt->bindValue(':usuario_id', $usuario['id']);
        $stmt->bindValue(':rua', $_POST['rua']);
        $stmt->bindValue(':numero', $_POST['numero']);
        $stmt->bindValue(':complemento', $_POST['complemento'] ?? '');
        $stmt->bindValue(':bairro', $_POST['bairro']);
        $stmt->bindValue(':referencia', $_POST['referencia'] ?? '');
        $stmt->bindValue(':forma_pagamento', $_POST['forma_pagamento']);
        $stmt->bindValue(':troco_para', $_POST['troco'] ?? null);
        $stmt->bindValue(':valor_total', $carrinho->getTotal());
        
        $stmt->execute();
        $pedido_id = $db->lastInsertId();
        
        // Inserir itens do pedido
        $stmt = $db->prepare("
            INSERT INTO pedido_itens (
                pedido_id,
                produto_id,
                tamanho_id,
                borda_id,
                quantidade,
                preco_unitario,
                observacoes
            ) VALUES (
                :pedido_id,
                :produto_id,
                :tamanho_id,
                :borda_id,
                :quantidade,
                :preco_unitario,
                :observacoes
            )
        ");
        
        foreach ($itens as $item) {
            $stmt->bindValue(':pedido_id', $pedido_id);
            $stmt->bindValue(':produto_id', $item['produto_id']);
            $stmt->bindValue(':tamanho_id', $item['tamanho_id'] ?? null);
            $stmt->bindValue(':borda_id', $item['borda_id'] ?? null);
            $stmt->bindValue(':quantidade', $item['quantidade']);
            $stmt->bindValue(':preco_unitario', $item['preco_unitario']);
            $stmt->bindValue(':observacoes', $item['observacoes'] ?? '');
            $stmt->execute();
        }
        
        // Limpar carrinho
        $carrinho->limpar();
        
        // Confirmar transação
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'pedido_id' => $pedido_id
        ]);
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $db->rollBack();
        error_log("Erro ao criar pedido: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao processar pedido']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
