<?php
require_once "../../config/database.php";
require_once "../../classes/Auth.php";
require_once "../../classes/Categoria.php";

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Verificar se é admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso não autorizado'
    ]);
    exit;
}

// Pegar dados da requisição
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    echo json_encode([
        'success' => false,
        'message' => 'ID da categoria não fornecido'
    ]);
    exit;
}

$categoria = new Categoria($db);
$resultado = $categoria->excluir($data->id);

echo json_encode([
    'success' => $resultado,
    'message' => $resultado ? 'Categoria excluída com sucesso' : 'Erro ao excluir categoria'
]);
