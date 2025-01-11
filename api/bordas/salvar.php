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

    $borda = new Borda($db);

    // Preparar dados da borda
    $dados = [
        'nome' => $_POST['nome'],
        'descricao' => $_POST['descricao'],
        'preco_adicional' => $_POST['preco_adicional'],
        'ativo' => isset($_POST['ativo']) ? 1 : 0
    ];

    // Salvar ou atualizar
    if (!empty($_POST['id'])) {
        $dados['id'] = $_POST['id'];
        $resultado = $borda->atualizar($dados);
    } else {
        $resultado = $borda->criar($dados);
    }

    if (!$resultado) {
        throw new Exception('Erro ao salvar borda no banco de dados');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Borda salva com sucesso'
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
