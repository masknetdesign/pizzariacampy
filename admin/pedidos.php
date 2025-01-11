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

$status_filtro = isset($_GET['status']) ? $_GET['status'] : null;
$pedidos = $pedido->listarPedidos($status_filtro);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos - Pizzaria Campy</title>
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
                            <a class="nav-link text-white" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="pedidos.php">
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
                    <h1 class="h2">Gerenciar Pedidos</h1>
                </div>

                <!-- Filtros -->
                <div class="mb-4">
                    <div class="btn-group">
                        <a href="pedidos.php" class="btn btn-outline-primary <?php echo !$status_filtro ? 'active' : ''; ?>">
                            Todos
                        </a>
                        <a href="pedidos.php?status=recebido" 
                           class="btn btn-outline-primary <?php echo $status_filtro === 'recebido' ? 'active' : ''; ?>">
                            Recebidos
                        </a>
                        <a href="pedidos.php?status=preparando" 
                           class="btn btn-outline-primary <?php echo $status_filtro === 'preparando' ? 'active' : ''; ?>">
                            Em Preparo
                        </a>
                        <a href="pedidos.php?status=saiu_entrega" 
                           class="btn btn-outline-primary <?php echo $status_filtro === 'saiu_entrega' ? 'active' : ''; ?>">
                            Em Entrega
                        </a>
                        <a href="pedidos.php?status=entregue" 
                           class="btn btn-outline-primary <?php echo $status_filtro === 'entregue' ? 'active' : ''; ?>">
                            Entregues
                        </a>
                    </div>
                </div>

                <!-- Lista de Pedidos -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Status</th>
                                <th>Valor Total</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $pedidos->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($p['cliente_nome']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($p['cliente_email']); ?></small>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm status-select" 
                                                data-pedido-id="<?php echo $p['id']; ?>"
                                                style="width: auto;">
                                            <option value="recebido" <?php echo $p['status'] === 'recebido' ? 'selected' : ''; ?>>
                                                Recebido
                                            </option>
                                            <option value="preparando" <?php echo $p['status'] === 'preparando' ? 'selected' : ''; ?>>
                                                Preparando
                                            </option>
                                            <option value="saiu_entrega" <?php echo $p['status'] === 'saiu_entrega' ? 'selected' : ''; ?>>
                                                Saiu para Entrega
                                            </option>
                                            <option value="entregue" <?php echo $p['status'] === 'entregue' ? 'selected' : ''; ?>>
                                                Entregue
                                            </option>
                                        </select>
                                    </td>
                                    <td>R$ <?php echo number_format($p['valor_total'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick="verDetalhes(<?php echo $p['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let modalDetalhes;

        document.addEventListener('DOMContentLoaded', function() {
            modalDetalhes = new bootstrap.Modal(document.getElementById('modalDetalhes'));
            
            // Atualização de status
            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', function() {
                    atualizarStatus(this.dataset.pedidoId, this.value);
                });
            });
        });

        function verDetalhes(pedidoId) {
            fetch(`../api/pedidos.php?id=${pedidoId}`)
                .then(response => response.json())
                .then(data => {
                    const modalBody = document.querySelector('#modalDetalhes .modal-body');
                    
                    let html = `
                        <div class="mb-4">
                            <h6>Informações do Cliente</h6>
                            <p>
                                <strong>Nome:</strong> ${data.pedido.cliente_nome}<br>
                                <strong>Email:</strong> ${data.pedido.cliente_email}<br>
                                <strong>Endereço:</strong> ${data.pedido.endereco_entrega}
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h6>Itens do Pedido</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantidade</th>
                                            <th>Preço Unit.</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    data.itens.forEach(item => {
                        html += `
                            <tr>
                                <td>
                                    ${item.produto_nome}
                                    ${item.borda_nome ? `<br><small class="text-muted">Borda: ${item.borda_nome}</small>` : ''}
                                    ${item.observacoes ? `<br><small class="text-muted">Obs: ${item.observacoes}</small>` : ''}
                                </td>
                                <td>${item.quantidade}</td>
                                <td>R$ ${parseFloat(item.preco_unitario).toFixed(2)}</td>
                                <td>R$ ${(item.quantidade * item.preco_unitario).toFixed(2)}</td>
                            </tr>
                        `;
                    });

                    html += `
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Taxa de Entrega:</strong></td>
                                            <td>R$ ${parseFloat(data.pedido.taxa_entrega).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td><strong>R$ ${parseFloat(data.pedido.valor_total).toFixed(2)}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div>
                            <h6>Informações do Pagamento</h6>
                            <p>
                                <strong>Forma de Pagamento:</strong> ${data.pedido.forma_pagamento.toUpperCase()}<br>
                                <strong>Data do Pedido:</strong> ${new Date(data.pedido.created_at).toLocaleString()}
                            </p>
                        </div>
                    `;

                    modalBody.innerHTML = html;
                    modalDetalhes.show();
                });
        }

        function atualizarStatus(pedidoId, novoStatus) {
            fetch('../api/pedidos.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `pedido_id=${pedidoId}&status=${novoStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza a página para refletir as mudanças
                    window.location.reload();
                } else {
                    alert('Erro ao atualizar status do pedido');
                }
            });
        }
    </script>
</body>
</html>
