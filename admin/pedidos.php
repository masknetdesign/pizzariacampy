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
$total_pedidos = $pedido->countPedidos();
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
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-secondary me-2" onclick="simularPedido()">
                            <i class="bi bi-play-fill"></i> Simular Novo Pedido
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="testSound()">
                            <i class="bi bi-volume-up"></i> Testar Som
                        </button>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="soundToggle" checked>
                            <label class="form-check-label" for="soundToggle">Alerta Sonoro</label>
                        </div>
                    </div>
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

    <!-- Audio elemento -->
    <audio id="newOrderSound" preload="auto">
        <source src="../assets/new-order.mp3" type="audio/mpeg">
    </audio>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let modalDetalhes;
        let notificationSound;
        let lastOrderCount = <?php echo $total_pedidos; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            modalDetalhes = new bootstrap.Modal(document.getElementById('modalDetalhes'));
            notificationSound = document.getElementById('newOrderSound');
            
            // Restaurar preferência de som
            const soundEnabled = localStorage.getItem('orderSoundEnabled') !== 'false';
            document.getElementById('soundToggle').checked = soundEnabled;
            
            // Configurar toggle de som
            document.getElementById('soundToggle').addEventListener('change', function(e) {
                localStorage.setItem('orderSoundEnabled', e.target.checked);
            });
            
            // Verificar novos pedidos a cada 30 segundos
            checkNewOrders();
            setInterval(checkNewOrders, 30000);
            
            // Atualização de status
            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', function() {
                    atualizarStatus(this.dataset.pedidoId, this.value);
                });
            });

            // Log inicial
            console.log('Sistema de notificação iniciado. Som ' + (soundEnabled ? 'ativado' : 'desativado'));
        });

        function checkNewOrders() {
            console.log('Verificando novos pedidos...');
            fetch('../api/check-new-orders.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Resposta da API não ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Resposta da verificação:', data);
                    
                    if (data.hasNewOrders) {
                        console.log(`Novos pedidos encontrados! Count: ${data.count}, Last: ${lastOrderCount}`);
                        
                        if (data.count > lastOrderCount) {
                            console.log('Tocando notificação...');
                            playNotification();
                            
                            // Mostrar notificação visual
                            showNotification(`Novo pedido recebido! Total de pedidos novos: ${data.count}`);
                            
                            // Recarregar após 2 segundos para garantir que o som toque
                            setTimeout(() => {
                                console.log('Recarregando página...');
                                location.reload();
                            }, 2000);
                        }
                        
                        lastOrderCount = data.count;
                    } else {
                        console.log('Nenhum pedido novo encontrado');
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar novos pedidos:', error);
                    showError('Erro ao verificar novos pedidos: ' + error.message);
                });
        }

        function playNotification() {
            if (localStorage.getItem('orderSoundEnabled') !== 'false' && notificationSound) {
                console.log('Iniciando reprodução do som...');
                
                // Garantir que o áudio está no início
                notificationSound.currentTime = 0;
                
                // Tentar tocar o som
                notificationSound.play()
                    .then(() => {
                        console.log('Som reproduzido com sucesso');
                    })
                    .catch(e => {
                        console.error('Erro ao tocar som:', e);
                        showError('Erro ao tocar som de notificação');
                    });
            } else {
                console.log('Som desativado ou elemento de áudio não encontrado');
            }
        }

        function showNotification(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                <strong><i class="bi bi-bell"></i></strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Remover após 5 segundos
            setTimeout(() => alertDiv.remove(), 5000);
        }

        function showError(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                <strong><i class="bi bi-exclamation-triangle"></i></strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Remover após 5 segundos
            setTimeout(() => alertDiv.remove(), 5000);
        }

        function simularPedido() {
            fetch('../api/simular-pedido.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        playNotification();
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        alert('Erro ao simular pedido: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao simular pedido');
                });
        }

        function testSound() {
            if (notificationSound) {
                const button = document.querySelector('button[onclick="testSound()"]');
                const originalText = button.innerHTML;
                
                // Mudar aparência do botão
                button.innerHTML = '<i class="bi bi-volume-up"></i> Tocando...';
                button.classList.add('btn-success');
                button.disabled = true;

                notificationSound.currentTime = 0;
                notificationSound.play()
                    .then(() => {
                        console.log('Som testado com sucesso');
                        // Restaurar botão após 1 segundo
                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.classList.remove('btn-success');
                            button.disabled = false;
                        }, 1000);
                    })
                    .catch(e => {
                        console.error('Erro ao testar som:', e);
                        alert('Erro ao tocar som. Verifique se seu navegador permite reprodução de áudio.');
                        // Restaurar botão imediatamente em caso de erro
                        button.innerHTML = originalText;
                        button.classList.remove('btn-success');
                        button.disabled = false;
                    });
            }
        }

        function verDetalhes(pedidoId) {
            fetch(`../api/pedidos.php?id=${pedidoId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Dados do pedido:', data); // Debug
                    const modalBody = document.querySelector('#modalDetalhes .modal-body');
                    
                    let html = `
                        <div class="mb-4">
                            <h6>Informações do Cliente</h6>
                            <p>
                                <strong>Nome:</strong> ${data.pedido.cliente_nome}<br>
                                <strong>Email:</strong> ${data.pedido.cliente_email}
                            </p>
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
                            <h6>Informações do Pagamento</h6>
                            <p>
                                <strong>Forma de Pagamento:</strong> ${data.pedido.forma_pagamento}<br>
                                ${data.pedido.troco_para ? `<strong>Troco para:</strong> R$ ${Number(data.pedido.troco_para).toFixed(2).replace('.', ',')}<br>` : ''}
                                <strong>Valor Total:</strong> R$ ${Number(data.pedido.valor_total).toFixed(2).replace('.', ',')}
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
                                        ${data.itens.map(item => {
                                            const valorUnitario = Number(item.preco_unitario || item.valor_unitario);
                                            const subtotal = valorUnitario * item.quantidade;
                                            return `
                                                <tr>
                                                    <td>
                                                        ${item.produto_nome}
                                                        ${item.tamanho_nome ? `<br><small>Tamanho: ${item.tamanho_nome}</small>` : ''}
                                                        ${item.borda_nome ? `<br><small>Borda: ${item.borda_nome}</small>` : ''}
                                                        ${item.observacoes ? `<br><small class="text-muted">Obs: ${item.observacoes}</small>` : ''}
                                                    </td>
                                                    <td>${item.quantidade}</td>
                                                    <td class="text-end">R$ ${valorUnitario.toFixed(2).replace('.', ',')}</td>
                                                    <td class="text-end">R$ ${subtotal.toFixed(2).replace('.', ',')}</td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td class="text-end"><strong>R$ ${Number(data.pedido.valor_total).toFixed(2).replace('.', ',')}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Status do Pedido</h6>
                            <p>
                                <span class="badge ${getStatusClass(data.pedido.status)}">
                                    ${getStatusText(data.pedido.status)}
                                </span>
                            </p>
                            <small class="text-muted">
                                Pedido feito em: ${new Date(data.pedido.created_at).toLocaleString('pt-BR')}
                            </small>
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
                case 'pendente':
                    return 'bg-warning';
                case 'preparando':
                    return 'bg-info';
                case 'saiu_entrega':
                    return 'bg-primary';
                case 'entregue':
                    return 'bg-success';
                case 'cancelado':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }

        function getStatusText(status) {
            switch(status) {
                case 'pendente':
                    return 'Recebido';
                case 'preparando':
                    return 'Em Preparo';
                case 'saiu_entrega':
                    return 'Saiu para Entrega';
                case 'entregue':
                    return 'Entregue';
                case 'cancelado':
                    return 'Cancelado';
                default:
                    return status.charAt(0).toUpperCase() + status.slice(1);
            }
        }

        function atualizarStatus(pedidoId, novoStatus) {
            fetch('../api/pedidos.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${pedidoId}&status=${novoStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizado com sucesso
                    location.reload();
                } else {
                    alert('Erro ao atualizar status do pedido');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar status do pedido');
            });
        }
    </script>
</body>
</html>
