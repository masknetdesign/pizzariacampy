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

    $tamanho = new Tamanho($db);

    // Preparar dados do tamanho
    $dados = [
        'nome' => $_POST['nome'],
        'descricao' => $_POST['descricao'],
        'multiplicador_preco' => $_POST['multiplicador_preco'],
        'ativo' => isset($_POST['ativo']) ? 1 : 0
    ];

    // Salvar ou atualizar
    if (!empty($_POST['id'])) {
        $dados['id'] = $_POST['id'];
        $resultado = $tamanho->atualizar($dados);
    } else {
        $resultado = $tamanho->criar($dados);
    }

    if (!$resultado) {
        throw new Exception('Erro ao salvar tamanho no banco de dados');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Tamanho salvo com sucesso'
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
