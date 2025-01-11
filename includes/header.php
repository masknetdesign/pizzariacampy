<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($auth)) {
    require_once dirname(__FILE__) . "/../classes/Auth.php";
    require_once dirname(__FILE__) . "/../config/database.php";
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);
}

// Determinar se estamos na área admin ou não
$isAdmin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$baseUrl = $isAdmin ? '../' : '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $baseUrl; ?>index.php">Pizzaria Campy</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $baseUrl; ?>cardapio.php">Cardápio</a>
                </li>
                <?php if($auth->isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>meus-pedidos.php">Meus Pedidos</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if($auth->isLoggedIn()): ?>
                    <?php if($auth->isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $baseUrl; ?>admin/dashboard.php">
                                <i class="bi bi-gear-fill"></i> Área Administrativa
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>carrinho.php">
                            <i class="bi bi-cart"></i> 
                            Carrinho
                            <span id="carrinho-contador" class="badge bg-danger rounded-pill" style="display: <?php echo isset($_SESSION['carrinho']) && count($_SESSION['carrinho']) > 0 ? 'inline-block' : 'none'; ?>">
                                <?php echo isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : '0'; ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>logout.php">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>login.php">
                            <i class="bi bi-person"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>cadastro.php">
                            <i class="bi bi-person-plus"></i> Cadastro
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
