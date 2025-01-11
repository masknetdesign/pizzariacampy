<?php
session_start();
require_once "config/database.php";
require_once "classes/Auth.php";
require_once "classes/Produto.php";
require_once "classes/Tamanho.php";

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$produto = new Produto($db);
$tamanho = new Tamanho($db);

$categoria_id = isset($_GET['categoria']) ? $_GET['categoria'] : null;
$produtos = $produto->listarPorCategoria($categoria_id);
$categorias = $produto->listarCategorias();
$bordas = $produto->listarBordas();
$tamanhos = $tamanho->listarTodos();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - Pizzaria Campy</title>
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
        <!-- Filtro de Categorias -->
        <div class="row mb-4">
            <div class="col">
                <div class="btn-group" role="group">
                    <a href="cardapio.php" class="btn btn-outline-primary <?php echo !$categoria_id ? 'active' : ''; ?>">
                        Todas
                    </a>
                    <?php while ($categoria = $categorias->fetch(PDO::FETCH_ASSOC)): ?>
                        <a href="cardapio.php?categoria=<?php echo $categoria['id']; ?>" 
                           class="btn btn-outline-primary <?php echo $categoria_id == $categoria['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($categoria['nome']); ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Lista de Produtos -->
        <div class="row">
            <?php while ($produto_item = $produtos->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($produto_item['imagem']): ?>
                            <img src="<?php echo htmlspecialchars($produto_item['imagem']); ?>" 
                                 class="card-img-top produto-img" 
                                 alt="<?php echo htmlspecialchars($produto_item['nome']); ?>">
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($produto_item['nome']); ?></h5>
                            <p class="card-text flex-grow-1">
                                <?php echo htmlspecialchars($produto_item['descricao']); ?>
                            </p>
                            <?php if ($produto_item['categoria_id'] == 1): ?>
                                <p class="card-text">
                                    <small class="text-muted">
                                        A partir de R$ <?php echo number_format($produto_item['preco'], 2, ',', '.'); ?>
                                    </small>
                                </p>
                            <?php else: ?>
                                <p class="card-text">
                                    <small class="text-muted">
                                        R$ <?php echo number_format($produto_item['preco'], 2, ',', '.'); ?>
                                    </small>
                                </p>
                            <?php endif; ?>
                            <button type="button" 
                                    class="btn btn-primary mt-2" 
                                    onclick="abrirModalPedido(<?php echo $produto_item['id']; ?>)">
                                Adicionar ao Carrinho
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal de Pedido -->
    <div class="modal fade" id="modalPedido" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Fazer Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPedido">
                        <input type="hidden" id="produto_id" name="produto_id">
                        
                        <div id="div-tamanho" style="display: none;">
                            <div class="mb-3">
                                <label for="tamanho" class="form-label">Escolha o Tamanho</label>
                                <select class="form-select" id="tamanho" name="tamanho_id">
                                    <option value="">Selecione um tamanho</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="div-borda" style="display: none;">
                            <div class="mb-3">
                                <label for="borda" class="form-label">Escolha a Borda</label>
                                <select class="form-select" id="borda" name="borda_id">
                                    <option value="">Selecione uma borda</option>
                                    <?php while ($borda = $bordas->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $borda['id']; ?>" 
                                                data-preco="<?php echo $borda['preco_adicional']; ?>">
                                            <?php echo htmlspecialchars($borda['nome']); ?> 
                                            (+R$ <?php echo number_format($borda['preco_adicional'], 2, ',', '.'); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantidade" class="form-label">Quantidade</label>
                            <div class="quantidade-grupo">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="ajustarQuantidade(-1)">-</button>
                                <input type="number" class="form-control" id="quantidade" 
                                       name="quantidade" value="1" min="1" readonly>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="ajustarQuantidade(1)">+</button>
                            </div>
                        </div>
                        
                        <div id="div-observacoes" class="mb-3" style="display: none;">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" 
                                      rows="3" placeholder="Ex: Sem cebola, mais queijo..."></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0">Total: R$ <span id="total">0,00</span></span>
                            <button type="submit" class="btn btn-primary">Adicionar ao Carrinho</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let modalPedido;
        let produtoAtual = null;
        const CATEGORIA_PIZZA_ID = 1; // Ajuste este ID de acordo com sua categoria de pizzas

        document.addEventListener('DOMContentLoaded', function() {
            modalPedido = new bootstrap.Modal(document.getElementById('modalPedido'));
            
            document.getElementById('formPedido').addEventListener('submit', adicionarAoCarrinho);
            
            // Atualiza o total quando muda a quantidade, borda ou tamanho
            ['quantidade', 'borda', 'tamanho'].forEach(id => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    elemento.addEventListener('change', atualizarTotal);
                }
            });
        });

        function ajustarQuantidade(delta) {
            const input = document.getElementById('quantidade');
            const novoValor = Math.max(1, parseInt(input.value || 1) + delta);
            input.value = novoValor;
            atualizarTotal();
        }

        function atualizarTotal() {
            if (!produtoAtual) return;

            let total = 0;
            const quantidade = parseInt(document.getElementById('quantidade').value) || 1;
            
            if (produtoAtual.categoria_id == CATEGORIA_PIZZA_ID) {
                const tamanhoSelect = document.getElementById('tamanho');
                const bordaSelect = document.getElementById('borda');
                
                if (tamanhoSelect.value) {
                    const tamanhoOption = tamanhoSelect.options[tamanhoSelect.selectedIndex];
                    const precoTamanho = parseFloat(tamanhoOption.dataset.preco);
                    console.log('Preço do tamanho:', precoTamanho); // Debug
                    total += precoTamanho;
                }
                
                if (bordaSelect.value) {
                    const bordaOption = bordaSelect.options[bordaSelect.selectedIndex];
                    const precoBorda = parseFloat(bordaOption.dataset.preco);
                    console.log('Preço da borda:', precoBorda); // Debug
                    total += precoBorda;
                }
            } else {
                total = parseFloat(produtoAtual.preco);
            }
            
            console.log('Total antes da quantidade:', total); // Debug
            total *= quantidade;
            console.log('Total final:', total); // Debug
            
            document.getElementById('total').textContent = total.toLocaleString('pt-BR', { 
                style: 'currency', 
                currency: 'BRL',
                minimumFractionDigits: 2
            }).replace('R$', '').trim();
        }

        function abrirModalPedido(produtoId) {
            fetch(`api/produtos.php?id=${produtoId}`)
                .then(response => response.json())
                .then(produto => {
                    console.log('Produto recebido:', produto); // Debug
                    produtoAtual = produto;
                    document.getElementById('produto_id').value = produto.id;
                    document.getElementById('quantidade').value = 1;
                    
                    // Atualizar título do modal
                    const modalTitle = document.querySelector('#modalPedido .modal-title');
                    if (modalTitle) {
                        modalTitle.textContent = produto.categoria_id == CATEGORIA_PIZZA_ID ? 
                            'Personalizar Pizza' : 'Adicionar ao Carrinho';
                    }
                    
                    const divTamanho = document.getElementById('div-tamanho');
                    const divBorda = document.getElementById('div-borda');
                    const divObservacoes = document.getElementById('div-observacoes');
                    const selectTamanho = document.getElementById('tamanho');
                    const selectBorda = document.getElementById('borda');
                    const textareaObservacoes = document.getElementById('observacoes');
                    
                    // Mostrar/esconder opções baseado na categoria
                    if (produto.categoria_id == CATEGORIA_PIZZA_ID) {
                        divTamanho.style.display = 'block';
                        divBorda.style.display = 'block';
                        divObservacoes.style.display = 'block';
                        selectTamanho.required = true;
                        
                        // Carregar tamanhos
                        fetch(`api/tamanhos.php?produto_id=${produtoId}`)
                            .then(response => response.json())
                            .then(tamanhos => {
                                console.log('Tamanhos recebidos:', tamanhos); // Debug
                                selectTamanho.innerHTML = '<option value="">Selecione um tamanho</option>';
                                tamanhos.forEach(tamanho => {
                                    const preco = parseFloat(tamanho.preco_final);
                                    console.log(`Tamanho ${tamanho.nome}:`, preco); // Debug
                                    selectTamanho.innerHTML += `
                                        <option value="${tamanho.id}" data-preco="${preco}">
                                            ${tamanho.nome} (R$ ${preco.toLocaleString('pt-BR', { minimumFractionDigits: 2 })})
                                        </option>`;
                                });
                                selectTamanho.value = ''; // Reset seleção
                                atualizarTotal(); // Atualizar total após carregar tamanhos
                            })
                            .catch(error => {
                                console.error('Erro ao carregar tamanhos:', error);
                                alert('Erro ao carregar os tamanhos disponíveis. Por favor, tente novamente.');
                            });
                    } else {
                        divTamanho.style.display = 'none';
                        divBorda.style.display = 'none';
                        divObservacoes.style.display = 'none';
                        selectTamanho.required = false;
                        selectBorda.required = false;
                        selectTamanho.value = '';
                        selectBorda.value = '';
                        textareaObservacoes.value = '';
                        atualizarTotal();
                    }
                    
                    modalPedido.show();
                })
                .catch(error => {
                    console.error('Erro ao abrir modal:', error);
                    alert('Erro ao carregar produto. Por favor, tente novamente.');
                });
        }

        function adicionarAoCarrinho(event) {
            event.preventDefault();

            const quantidade = parseInt(document.getElementById('quantidade').value) || 1;
            if (quantidade < 1) {
                alert('A quantidade deve ser maior que zero');
                return;
            }

            const formData = {
                acao: 'adicionar',
                produto_id: parseInt(produtoAtual.id),
                quantidade: quantidade
            };

            if (produtoAtual.categoria_id == CATEGORIA_PIZZA_ID) {
                const tamanhoId = parseInt(document.getElementById('tamanho').value);
                if (!tamanhoId) {
                    alert('Por favor, selecione um tamanho');
                    return;
                }
                formData.tamanho_id = tamanhoId;
                formData.borda_id = parseInt(document.getElementById('borda').value) || null;
                formData.observacoes = document.getElementById('observacoes').value.trim();
            }

            fetch('api/carrinho.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                modalPedido.hide();
                atualizarContadorCarrinho();
                alert('Item adicionado ao carrinho!');
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao adicionar item ao carrinho');
            });
        }

        function atualizarContadorCarrinho() {
            fetch('api/carrinho.php?action=count')
                .then(response => response.json())
                .then(data => {
                    const contador = document.getElementById('carrinho-contador');
                    if (data.count > 0) {
                        contador.textContent = data.count;
                        contador.style.display = 'inline-block';
                    } else {
                        contador.style.display = 'none';
                    }
                });
        }

        // Inicialização quando o DOM estiver carregado
        document.addEventListener('DOMContentLoaded', function() {
            modalPedido = new bootstrap.Modal(document.getElementById('modalPedido'));
            
            // Adiciona listener para o formulário
            document.getElementById('formPedido').addEventListener('submit', adicionarAoCarrinho);
            
            // Adiciona listeners para atualizar o total
            const quantidadeInput = document.getElementById('quantidade');
            quantidadeInput.addEventListener('change', atualizarTotal);
            quantidadeInput.addEventListener('input', atualizarTotal);
            
            const tamanhoSelect = document.getElementById('tamanho');
            const bordaSelect = document.getElementById('borda');
            
            if (tamanhoSelect) {
                tamanhoSelect.addEventListener('change', function() {
                    console.log('Tamanho selecionado:', this.value); // Debug
                    atualizarTotal();
                });
            }
            if (bordaSelect) {
                bordaSelect.addEventListener('change', function() {
                    console.log('Borda selecionada:', this.value); // Debug
                    atualizarTotal();
                });
            }
            
            // Atualiza o contador do carrinho ao carregar a página
            atualizarContadorCarrinho();
        });
    </script>
</body>
</html>
