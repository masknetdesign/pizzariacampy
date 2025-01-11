<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['produto_id'])) {
            $produto_id = $_GET['produto_id'];
            error_log("Buscando tamanhos para o produto ID: " . $produto_id);

            // Primeiro, verificar se o produto existe
            $stmt = $db->prepare("SELECT id, preco FROM produtos WHERE id = ?");
            $stmt->execute([$produto_id]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produto) {
                error_log("Produto não encontrado: " . $produto_id);
                http_response_code(404);
                echo json_encode(['error' => 'Produto não encontrado']);
                exit;
            }

            error_log("Produto encontrado: " . print_r($produto, true));

            // Buscar os tamanhos
            $query = "
                SELECT 
                    t.id,
                    t.nome,
                    t.multiplicador_preco,
                    t.ordem,
                    COALESCE(pt.preco, t.multiplicador_preco * :preco_base) as preco_final
                FROM 
                    tamanhos t
                LEFT JOIN 
                    produto_tamanhos pt ON pt.tamanho_id = t.id AND pt.produto_id = :produto_id
                WHERE 
                    t.ativo = 1
                ORDER BY 
                    t.ordem
            ";

            error_log("Executando query: " . $query);
            
            $stmt = $db->prepare($query);
            $stmt->bindValue(':produto_id', $produto_id, PDO::PARAM_INT);
            $stmt->bindValue(':preco_base', $produto['preco'], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $tamanhos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Tamanhos encontrados: " . print_r($tamanhos, true));
                
                if (empty($tamanhos)) {
                    error_log("Nenhum tamanho encontrado para o produto $produto_id");
                    // Retornar array vazio em vez de erro
                    echo json_encode([]);
                } else {
                    echo json_encode($tamanhos);
                }
            } else {
                error_log("Erro ao executar a query: " . print_r($stmt->errorInfo(), true));
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao buscar tamanhos']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID do produto não fornecido']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
    }
} catch (PDOException $e) {
    error_log("Erro PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
