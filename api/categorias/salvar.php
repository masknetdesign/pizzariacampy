<?php
require_once "../../config/database.php";
require_once "../../classes/Auth.php";
require_once "../../classes/Categoria.php";
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

    $categoria = new Categoria($db);

    // Preparar dados da categoria
    $dados = [
        'nome' => $_POST['nome'],
        'descricao' => $_POST['descricao'],
        'ativo' => isset($_POST['ativo']) ? 1 : 0
    ];

    // Salvar ou atualizar
    if (!empty($_POST['id'])) {
        $dados['id'] = $_POST['id'];
        $resultado = $categoria->atualizar($dados);
    } else {
        $resultado = $categoria->criar($dados);
    }

    if (!$resultado) {
        throw new Exception('Erro ao salvar categoria no banco de dados');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Categoria salva com sucesso'
    ]);

} catch (Exception $e) {
    logError($e->getMessage(), [
        'POST' => $_POST
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar requisição: ' . $e->getMessage()
    ]);
}
