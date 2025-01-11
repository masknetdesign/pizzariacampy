<?php
require_once "../../config/database.php";
require_once "../../classes/Auth.php";
require_once "../../classes/Produto.php";
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

    $produto = new Produto($db);

    // Processar upload de imagem
    $imagem_path = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/produtos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $target_file = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file)) {
            $imagem_path = 'uploads/produtos/' . $filename;
        } else {
            throw new Exception('Erro ao fazer upload da imagem');
        }
    }

    // Preparar dados do produto
    $dados = [
        'nome' => $_POST['nome'],
        'descricao' => $_POST['descricao'],
        'preco' => $_POST['preco'],
        'categoria_id' => $_POST['categoria_id'],
        'ativo' => isset($_POST['ativo']) ? 1 : 0
    ];

    if ($imagem_path) {
        $dados['imagem'] = $imagem_path;
    }

    // Salvar ou atualizar
    if (!empty($_POST['id'])) {
        $dados['id'] = $_POST['id'];
        $resultado = $produto->atualizar($dados);
    } else {
        $resultado = $produto->criar($dados);
    }

    if (!$resultado) {
        throw new Exception('Erro ao salvar produto no banco de dados');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Produto salvo com sucesso'
    ]);

} catch (Exception $e) {
    logError($e->getMessage(), [
        'POST' => $_POST,
        'FILES' => isset($_FILES) ? $_FILES : null
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar requisição: ' . $e->getMessage()
    ]);
}
