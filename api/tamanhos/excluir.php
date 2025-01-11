<?php
require_once "../../config/database.php";
require_once "../../classes/Auth.php";
require_once "../../classes/Tamanho.php";
require_once "../debug.php";

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    // Verificar se é admin
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
        throw new Exception('Acesso não autorizado');
    }

    // Pegar dados da requisição
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->id)) {
        throw new Exception('ID do tamanho não fornecido');
    }

    $tamanho = new Tamanho($db);
    $resultado = $tamanho->excluir($data->id);

    if (!$resultado) {
        throw new Exception('Erro ao excluir tamanho do banco de dados');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Tamanho excluído com sucesso'
    ]);

} catch (Exception $e) {
    logError($e->getMessage(), [
        'input' => json_decode(file_get_contents("php://input"))
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar requisição: ' . $e->getMessage()
    ]);
}
