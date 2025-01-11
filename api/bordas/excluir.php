<?php
require_once "../../config/database.php";
require_once "../../classes/Auth.php";
require_once "../../classes/Borda.php";
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
        throw new Exception('ID da borda não fornecido');
    }

    $borda = new Borda($db);
    $resultado = $borda->excluir($data->id);

    if (!$resultado) {
        throw new Exception('Erro ao excluir borda do banco de dados');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Borda excluída com sucesso'
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
