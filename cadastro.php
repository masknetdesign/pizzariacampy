<?php
session_start();
require_once "config/database.php";
require_once "classes/Auth.php";

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';
    
    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirma_senha)) {
        $message = 'Todos os campos são obrigatórios';
        $messageType = 'danger';
    } elseif (strlen($senha) < 6) {
        $message = 'A senha deve ter pelo menos 6 caracteres';
        $messageType = 'danger';
    } elseif ($senha !== $confirma_senha) {
        $message = 'As senhas não coincidem';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email inválido';
        $messageType = 'danger';
    } else {
        try {
            if ($auth->register($nome, $email, $senha)) {
                $message = 'Cadastro realizado com sucesso! Faça login para continuar.';
                $messageType = 'success';
                // Redireciona após 2 segundos
                header("refresh:2;url=login.php");
            } else {
                $message = 'Erro ao realizar cadastro. Email já pode estar em uso.';
                $messageType = 'danger';
            }
        } catch (PDOException $e) {
            $message = 'Erro ao realizar cadastro. Tente novamente mais tarde.';
            $messageType = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Pizzaria Campy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="form-container">
            <h2 class="text-center mb-4">Cadastro</h2>
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="cadastro.php" id="formCadastro">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" 
                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" 
                           minlength="6" required>
                    <div class="form-text">A senha deve ter pelo menos 6 caracteres</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirma_senha" class="form-label">Confirme a senha</label>
                    <input type="password" class="form-control" id="confirma_senha" 
                           name="confirma_senha" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
            </form>
            
            <div class="text-center mt-3">
                <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário no lado do cliente
        document.getElementById('formCadastro').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirma_senha = document.getElementById('confirma_senha').value;
            
            if (senha !== confirma_senha) {
                e.preventDefault();
                alert('As senhas não coincidem!');
            }
        });
    </script>
</body>
</html>
