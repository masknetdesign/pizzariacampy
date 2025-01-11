<?php
session_start();
require_once "../config/database.php";
require_once "../classes/Auth.php";
require_once "../classes/Pedido.php";

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$pedido = new Pedido($db);

// Verificar se é admin
if (!$auth->isAdmin()) {
    header("Location: ../index.php");
    exit;
}

$estatisticas = $pedido->getEstatisticas();
$pedidos_recentes = $pedido->listarPedidos(null, 10);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Pizzaria Campy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="pedidos.php">
                                <i class="bi bi-list-check"></i> Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="produtos.php">
                                <i class="bi bi-grid"></i> Produtos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="categorias.php">
                                <i class="bi bi-tags"></i> Categorias
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="usuarios.php">
                                <i class="bi bi-people"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../index.php">
                                <i class="bi bi-house"></i> Voltar ao Site
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Conteúdo Principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Cards de Estatísticas -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">Pedidos Hoje</h5>
                                <p class="card-text h2">
                                    <?php echo $estatisticas['pedidos_por_status']['recebido'] ?? 0; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">Faturamento Total</h5>
                                <p class="card-text h2">
                                    R$ <?php echo number_format($estatisticas['faturamento_total'], 2, ',', '.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body">
                                <h5 class="card-title">Em Preparo</h5>
                                <p class="card-text h2">
                                    <?php echo $estatisticas['pedidos_por_status']['preparando'] ?? 0; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">Em Entrega</h5>
                                <p class="card-text h2">
                                    <?php echo $estatisticas['pedidos_por_status']['saiu_entrega'] ?? 0; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produtos Mais Vendidos -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Produtos Mais Vendidos</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th>Quantidade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($estatisticas['produtos_populares'] as $produto => $quantidade): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($produto); ?></td>
                                                    <td><?php echo $quantidade; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pedidos Recentes -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Pedidos Recentes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Cliente</th>
                                                <th>Status</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($pedido = $pedidos_recentes->fetch(PDO::FETCH_ASSOC)): ?>
                                                <tr>
                                                    <td><?php echo $pedido['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo match($pedido['status']) {
                                                                'recebido' => 'primary',
                                                                'preparando' => 'warning',
                                                                'saiu_entrega' => 'info',
                                                                'entregue' => 'success',
                                                                default => 'secondary'
                                                            };
                                                        ?>">
                                                            <?php echo ucfirst($pedido['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
