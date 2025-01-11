<?php
require_once '../config/database.php';
require_once '../classes/Carrinho.php';

header('Content-Type: application/json');
session_start();

$database = new Database();
$db = $database->getConnection();
$carrinho = new Carrinho($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'count') {
        echo json_encode(["count" => $carrinho->getQuantidadeItens()]);
    } else {
        $itens = $carrinho->getItens();
        $total = array_reduce($itens, function($acc, $item) {
            return $acc + $item['preco_total'];
        }, 0);
        
        echo json_encode([
            "itens" => $itens,
            "total" => $total
        ]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    error_log('Dados recebidos na API do carrinho: ' . print_r($data, true));
    
    if (!isset($data['acao'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Ação não especificada']);
        exit;
    }

    switch ($data['acao']) {
        case 'adicionar':
            if (!isset($data['produto_id']) || !isset($data['quantidade'])) {
                http_response_code(400);
                $missing = [];
                if (!isset($data['produto_id'])) $missing[] = 'produto_id';
                if (!isset($data['quantidade'])) $missing[] = 'quantidade';
                echo json_encode([
                    'error' => 'Dados incompletos',
                    'missing' => $missing
                ]);
                exit;
            }

            // Verificar categoria do produto
            $stmt = $db->prepare("SELECT categoria_id FROM produtos WHERE id = ?");
            $stmt->execute([$data['produto_id']]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produto) {
                http_response_code(400);
                echo json_encode(['error' => 'Produto não encontrado']);
                exit;
            }

            $tamanho_id = null;
            $borda_id = null;
            $observacoes = '';
            $quantidade = max(1, intval($data['quantidade']));

            // Se for pizza (categoria_id = 1), validar tamanho
            if ($produto['categoria_id'] == 1) {
                if (!isset($data['tamanho_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Tamanho não selecionado para pizza']);
                    exit;
                }
                $tamanho_id = intval($data['tamanho_id']);
                $borda_id = isset($data['borda_id']) ? intval($data['borda_id']) : null;
                $observacoes = $data['observacoes'] ?? '';
            }
            
            error_log("Tentando adicionar ao carrinho: produto_id={$data['produto_id']}, tamanho_id=$tamanho_id, borda_id=$borda_id, quantidade=$quantidade");
            
            if ($carrinho->adicionar($data['produto_id'], $tamanho_id, $borda_id, $quantidade, $observacoes)) {
                http_response_code(200);
                echo json_encode(['message' => 'Item adicionado ao carrinho']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Erro ao adicionar item ao carrinho']);
            }
            break;
            
        case 'atualizar':
            if (isset($data['index'], $data['quantidade'])) {
                $quantidade = max(1, intval($data['quantidade']));
                if ($carrinho->atualizar($data['index'], $quantidade)) {
                    $itens = $carrinho->getItens();
                    $total = array_reduce($itens, function($acc, $item) {
                        return $acc + $item['preco_total'];
                    }, 0);
                    
                    echo json_encode([
                        'message' => 'Quantidade atualizada',
                        'itens' => $itens,
                        'total' => $total
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erro ao atualizar quantidade']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Dados incompletos']);
            }
            break;
            
        case 'remover':
            if (isset($data['index'])) {
                if ($carrinho->remover($data['index'])) {
                    $itens = $carrinho->getItens();
                    $total = array_reduce($itens, function($acc, $item) {
                        return $acc + $item['preco_total'];
                    }, 0);
                    
                    echo json_encode([
                        'message' => 'Item removido',
                        'itens' => $itens,
                        'total' => $total
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erro ao remover item']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Índice não fornecido']);
            }
            break;
            
        case 'limpar':
            if ($carrinho->limpar()) {
                echo json_encode([
                    'message' => 'Carrinho limpo',
                    'itens' => [],
                    'total' => 0
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Erro ao limpar carrinho']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação inválida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
