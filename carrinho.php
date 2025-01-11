<?php
session_start();
require_once "config/database.php";
require_once "classes/Auth.php";
require_once "classes/Carrinho.php";

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$carrinho = new Carrinho($db);

// Redirecionar se não estiver logado
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - Pizzaria Campy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Remove as setas do input number */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
        
        /* Estilo para o grupo de quantidade */
        .quantidade-grupo {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 120px;
        }
        
        .quantidade-grupo .btn {
            padding: 0.375rem 0.75rem;
            font-weight: bold;
        }
        
        .quantidade-grupo input {
            width: 40px !important;
            text-align: center;
            border-left: 0;
            border-right: 0;
            border-radius: 0;
            padding: 0.375rem 0;
        }
        
        .quantidade-grupo .btn:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        
        .quantidade-grupo .btn:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2>Meu Carrinho</h2>
        
        <div id="carrinho-conteudo">
            <!-- Será preenchido via JavaScript -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function atualizarContadorCarrinho() {
            fetch('api/carrinho.php?action=count')
                .then(response => response.json())
                .then(data => {
                    const contador = document.getElementById('carrinho-contador');
                    if (contador) {
                        contador.textContent = data.count;
                        contador.style.display = data.count > 0 ? 'inline-block' : 'none';
                    }
                })
                .catch(error => console.error('Erro ao atualizar contador:', error));
        }

        function atualizarCarrinhoUI(data) {
            const conteudo = document.getElementById('carrinho-conteudo');
            
            // Atualiza o contador do carrinho
            atualizarContadorCarrinho();
            
            if (!data.itens || data.itens.length === 0) {
                conteudo.innerHTML = `
                    <div class="alert alert-info">
                        Seu carrinho está vazio. 
                        <a href="cardapio.php" class="alert-link">Ir para o cardápio</a>
                    </div>
                `;
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.itens.forEach((item, index) => {
                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${item.imagem || 'assets/img/pizza-placeholder.jpg'}" alt="${item.nome}" class="img-thumbnail me-3" style="width: 100px;">
                                <div>
                                    <h5 class="mb-1">${item.nome}</h5>
                                    ${item.tamanho_nome && item.categoria_id != 2 ? `<p class="mb-1">Tamanho: ${item.tamanho_nome}</p>` : ''}
                                    ${item.borda_nome ? `<p class="mb-1">Borda: ${item.borda_nome}</p>` : ''}
                                    ${item.observacoes ? `<p class="mb-1"><small>Obs: ${item.observacoes}</small></p>` : ''}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="quantidade-grupo">
                                <button type="button" class="btn btn-outline-secondary" onclick="atualizarQuantidade(${index}, ${item.quantidade - 1})">-</button>
                                <input type="number" class="form-control" value="${item.quantidade}" min="1" 
                                       onchange="atualizarQuantidade(${index}, parseInt(this.value) || 1)">
                                <button type="button" class="btn btn-outline-secondary" onclick="atualizarQuantidade(${index}, ${item.quantidade + 1})">+</button>
                            </div>
                        </td>
                        <td class="text-end">
                            <div>
                                <p class="mb-0">R$ ${Number(item.preco_total).toFixed(2)}</p>
                                <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removerItem(${index})">
                                    <i class="bi bi-trash"></i> Remover
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>R$ ${Number(data.total).toFixed(2)}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <a href="cardapio.php" class="btn btn-secondary">Continuar Comprando</a>
                    <a href="finalizar-pedido.php" class="btn btn-primary">Finalizar Pedido</a>
                </div>
            `;

            conteudo.innerHTML = html;
        }

        function atualizarQuantidade(index, novaQuantidade) {
            if (novaQuantidade < 1) return;
            
            fetch('api/carrinho.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    acao: 'atualizar',
                    index: index,
                    quantidade: novaQuantidade
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                atualizarCarrinhoUI(data);
                atualizarContadorCarrinho();
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar quantidade. Por favor, tente novamente.');
            });
        }

        function removerItem(index) {
            fetch('api/carrinho.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    acao: 'remover',
                    index: index
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                atualizarCarrinhoUI(data);
                atualizarContadorCarrinho();
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao remover item. Por favor, tente novamente.');
            });
        }

        // Carregar carrinho ao iniciar a página
        fetch('api/carrinho.php')
            .then(response => response.json())
            .then(data => atualizarCarrinhoUI(data))
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao carregar carrinho. Por favor, recarregue a página.');
            });
    </script>
</body>
</html>
