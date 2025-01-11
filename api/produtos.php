<?php
header('Content-Type: application/json');
require_once "../config/database.php";
require_once "../classes/Produto.php";

$database = new Database();
$db = $database->getConnection();
$produto = new Produto($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $db->prepare("
            SELECT p.*, c.id as categoria_id, c.nome as categoria_nome 
            FROM produtos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.id = :id AND p.ativo = 1
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produto) {
            echo json_encode($produto);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID do produto não fornecido']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
