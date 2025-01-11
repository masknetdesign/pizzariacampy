<?php
session_start();
require_once "config/database.php";
require_once "classes/Auth.php";
require_once "classes/Pedido.php";

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$pedido = new Pedido($db);

// Redirecionar se não estiver logado
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$usuario = $auth->getUser();
$pedidos = $pedido->listarPedidosUsuario($usuario['id']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Pizzaria Campy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2>Meus Pedidos</h2>
        
        <div class="row mt-4">
            <?php if ($pedidos->rowCount() === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Você ainda não fez nenhum pedido.
                        <a href="cardapio.php" class="alert-link">Fazer meu primeiro pedido</a>
                    </div>
                </div>
            <?php else: ?>
                <?php while ($p = $pedidos->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Pedido #<?php echo $p['id']; ?></h5>
                                <span class="badge <?php 
                                    switch($p['status']) {
                                        case 'recebido':
                                            echo 'bg-warning';
                                            break;
                                        case 'preparando':
                                            echo 'bg-info';
                                            break;
                                        case 'saiu_entrega':
                                            echo 'bg-primary';
                                            break;
                                        case 'entregue':
                                            echo 'bg-success';
                                            break;
                                        default:
                                            echo 'bg-danger';
                                    }
                                ?>">
                                    <?php 
                                        switch($p['status']) {
                                            case 'recebido':
                                                echo 'Recebido';
                                                break;
                                            case 'preparando':
                                                echo 'Em Preparo';
                                                break;
                                            case 'saiu_entrega':
                                                echo 'Saiu para Entrega';
                                                break;
                                            case 'entregue':
                                                echo 'Entregue';
                                                break;
                                            default:
                                                echo 'Cancelado';
                                        }
                                    ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <strong>Data:</strong> 
                                    <?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Endereço:</strong><br>
                                    <?php 
                                        echo $p['endereco_rua'] . ', ' . $p['endereco_numero'];
                                        if (!empty($p['endereco_complemento'])) {
                                            echo ' - ' . $p['endereco_complemento'];
                                        }
                                        echo '<br>' . $p['endereco_bairro'];
                                        if (!empty($p['endereco_referencia'])) {
                                            echo '<br><small class="text-muted">Ref: ' . $p['endereco_referencia'] . '</small>';
                                        }
                                    ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Forma de Pagamento:</strong> 
                                    <?php echo ucfirst($p['forma_pagamento']); ?>
                                    <?php if (!empty($p['troco_para'])): ?>
                                        <br>
                                        <small>Troco para: R$ <?php echo number_format($p['troco_para'], 2, ',', '.'); ?></small>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-3">
                                    <strong>Total:</strong> 
                                    R$ <?php echo number_format($p['valor_total'], 2, ',', '.'); ?>
                                </p>
                                <button type="button" class="btn btn-primary" onclick="verDetalhes(<?php echo $p['id']; ?>)">
                                    Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
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
        });
        
        function verDetalhes(pedidoId) {
            fetch(`api/pedidos.php?id=${pedidoId}`)
                .then(response => response.json())
                .then(data => {
                    const modalBody = document.querySelector('#modalDetalhes .modal-body');
                    
                    let html = `
                        <div class="mb-4">
                            <h6>Status do Pedido</h6>
                            <div class="alert ${getStatusClass(data.pedido.status)}">
                                ${getStatusText(data.pedido.status)}
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6>Endereço de Entrega</h6>
                            <p>
                                ${data.pedido.endereco_rua}, ${data.pedido.endereco_numero}
                                ${data.pedido.endereco_complemento ? `<br>${data.pedido.endereco_complemento}` : ''}
                                <br>${data.pedido.endereco_bairro}
                                ${data.pedido.endereco_referencia ? `<br><small class="text-muted">Ref: ${data.pedido.endereco_referencia}</small>` : ''}
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h6>Itens do Pedido</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Qtd</th>
                                            <th class="text-end">Valor Unit.</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.itens.map(item => `
                                            <tr>
                                                <td>
                                                    ${item.produto_nome}
                                                    ${item.tamanho_nome ? `<br><small>Tamanho: ${item.tamanho_nome}</small>` : ''}
                                                    ${item.borda_nome ? `<br><small>Borda: ${item.borda_nome}</small>` : ''}
                                                    ${item.observacoes ? `<br><small>Obs: ${item.observacoes}</small>` : ''}
                                                </td>
                                                <td>${item.quantidade}</td>
                                                <td class="text-end">R$ ${Number(item.valor_unitario).toFixed(2)}</td>
                                                <td class="text-end">R$ ${(item.quantidade * item.valor_unitario).toFixed(2)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td class="text-end"><strong>R$ ${Number(data.pedido.valor_total).toFixed(2)}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    `;
                    
                    modalBody.innerHTML = html;
                    modalDetalhes.show();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar detalhes do pedido');
                });
        }

        function getStatusClass(status) {
            switch(status) {
                case 'recebido':
                    return 'alert-warning';
                case 'preparando':
                    return 'alert-info';
                case 'saiu_entrega':
                    return 'alert-primary';
                case 'entregue':
                    return 'alert-success';
                default:
                    return 'alert-danger';
            }
        }

        function getStatusText(status) {
            switch(status) {
                case 'recebido':
                    return 'Pedido recebido e aguardando preparo';
                case 'preparando':
                    return 'Seu pedido está sendo preparado';
                case 'saiu_entrega':
                    return 'Seu pedido está a caminho';
                case 'entregue':
                    return 'Pedido entregue';
                default:
                    return 'Pedido cancelado';
            }
        }
    </script>
</body>
</html>
