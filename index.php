<?php
session_start();
require_once "config/database.php";
require_once "classes/Auth.php";

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizzaria Campy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Pizzaria Campy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="cardapio.php">Cardápio</a>
                    </li>
                    <?php if($auth->isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="meus-pedidos.php">Meus Pedidos</a>
                        </li>
                        <?php if($auth->isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Painel Admin</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if($auth->isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="carrinho.php">
                                <i class="bi bi-cart"></i> Carrinho
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Sair</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastro.php">Cadastro</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="jumbotron">
            <h1 class="display-4">Bem-vindo à Pizzaria Campy!</h1>
            <p class="lead">As melhores pizzas da região, feitas com ingredientes selecionados e muito amor.</p>
            <hr class="my-4">
            <p>Faça seu pedido agora mesmo e receba em casa!</p>
            <a class="btn btn-primary btn-lg" href="cardapio.php" role="button">Ver Cardápio</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
