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
        
        <?php if (empty($pedidos)): ?>
            <div class="alert alert-info mt-4">
                Você ainda não fez nenhum pedido.
                <a href="cardapio.php" class="alert-link">Fazer meu primeiro pedido</a>
            </div>
        <?php else: ?>
            <div class="row mt-4">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Pedido #<?php echo $pedido['id']; ?></h5>
                                <span class="badge <?php echo $pedido['status'] === 'pendente' ? 'bg-warning' : 
                                    ($pedido['status'] === 'em_preparo' ? 'bg-info' : 
                                    ($pedido['status'] === 'saiu_entrega' ? 'bg-primary' : 
                                    ($pedido['status'] === 'entregue' ? 'bg-success' : 'bg-danger'))); ?>">
                                    <?php 
                                        echo $pedido['status'] === 'pendente' ? 'Pendente' :
                                            ($pedido['status'] === 'em_preparo' ? 'Em Preparo' :
                                            ($pedido['status'] === 'saiu_entrega' ? 'Saiu para Entrega' :
                                            ($pedido['status'] === 'entregue' ? 'Entregue' : 'Cancelado')));
                                    ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="card-text">
                                    <strong>Data:</strong> 
                                    <?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?>
                                </p>
                                <p class="card-text">
                                    <strong>Endereço:</strong><br>
                                    <?php echo $pedido['endereco_rua']; ?>, 
                                    <?php echo $pedido['endereco_numero']; ?><br>
                                    <?php echo $pedido['endereco_bairro']; ?>
                                    <?php if ($pedido['endereco_complemento']): ?>
                                        <br><?php echo $pedido['endereco_complemento']; ?>
                                    <?php endif; ?>
                                </p>
                                <p class="card-text">
                                    <strong>Forma de Pagamento:</strong> 
                                    <?php echo ucfirst($pedido['forma_pagamento']); ?>
                                    <?php if ($pedido['troco_para']): ?>
                                        (Troco para R$ <?php echo number_format($pedido['troco_para'], 2, ',', '.'); ?>)
                                    <?php endif; ?>
                                </p>
                                <p class="card-text">
                                    <strong>Total:</strong> 
                                    R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>
                                </p>
                                <button type="button" 
                                        class="btn btn-primary" 
                                        onclick="verDetalhes(<?php echo $pedido['id']; ?>)">
                                    Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de Detalhes -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detalhes-pedido">
                        Carregando...
                    </div>
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
            const detalhesDiv = document.getElementById('detalhes-pedido');
            detalhesDiv.innerHTML = 'Carregando...';
            modalDetalhes.show();
            
            fetch(`api/pedidos.php?id=${pedidoId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '<div class="mb-4">';
                    html += '<h6>Itens do Pedido:</h6>';
                    html += '<ul class="list-unstyled">';
                    
                    data.itens.forEach(item => {
                        html += '<li class="mb-2">';
                        html += `<strong>${item.quantidade}x ${item.produto_nome}</strong>`;
                        if (item.tamanho_nome) {
                            html += `<br><small>Tamanho: ${item.tamanho_nome}</small>`;
                        }
                        if (item.borda_nome) {
                            html += `<br><small>Borda: ${item.borda_nome}</small>`;
                        }
                        if (item.observacoes) {
                            html += `<br><small>Obs: ${item.observacoes}</small>`;
                        }
                        html += `<br><small>R$ ${(item.preco_unitario * item.quantidade).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</small>`;
                        html += '</li>';
                    });
                    
                    html += '</ul></div>';
                    
                    if (data.pedido.status === 'pendente') {
                        html += '<div class="alert alert-warning">Aguardando confirmação do estabelecimento</div>';
                    } else if (data.pedido.status === 'em_preparo') {
                        html += '<div class="alert alert-info">Seu pedido está sendo preparado</div>';
                    } else if (data.pedido.status === 'saiu_entrega') {
                        html += '<div class="alert alert-primary">Seu pedido está a caminho</div>';
                    } else if (data.pedido.status === 'entregue') {
                        html += '<div class="alert alert-success">Pedido entregue</div>';
                    } else {
                        html += '<div class="alert alert-danger">Pedido cancelado</div>';
                    }
                    
                    detalhesDiv.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erro:', error);
                    detalhesDiv.innerHTML = 'Erro ao carregar detalhes do pedido';
                });
        }
    </script>
</body>
</html>
