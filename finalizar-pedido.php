<?php
session_start();
require_once "config/database.php";
require_once "classes/Auth.php";
require_once "classes/Carrinho.php";
require_once "classes/Pedido.php";

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$carrinho = new Carrinho($db);

// Redirecionar se não estiver logado
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$usuario = $auth->getUser();
$itens = $carrinho->getItens();
$total = $carrinho->getTotal();

// Se não houver itens no carrinho, redirecionar para o cardápio
if (empty($itens)) {
    header("Location: cardapio.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pedido - Pizzaria Campy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2>Finalizar Pedido</h2>
        
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Endereço de Entrega</h5>
                    </div>
                    <div class="card-body">
                        <form id="formEndereco">
                            <div class="mb-3">
                                <label for="cep" class="form-label">CEP</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="cep" name="cep" maxlength="9" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="buscarCep()">
                                        Buscar CEP
                                    </button>
                                </div>
                                <div id="cepFeedback" class="invalid-feedback">
                                    CEP inválido
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="rua" class="form-label">Rua</label>
                                    <input type="text" class="form-control" id="rua" name="rua" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="numero" class="form-label">Número</label>
                                    <input type="text" class="form-control" id="numero" name="numero" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" class="form-control" id="complemento" name="complemento">
                            </div>
                            <div class="mb-3">
                                <label for="bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="bairro" name="bairro" required>
                            </div>
                            <div class="mb-3">
                                <label for="referencia" class="form-label">Ponto de Referência</label>
                                <input type="text" class="form-control" id="referencia" name="referencia">
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Forma de Pagamento</h5>
                    </div>
                    <div class="card-body">
                        <form id="formPagamento">
                            <div class="mb-3">
                                <select class="form-select" id="formaPagamento" name="forma_pagamento" required>
                                    <option value="">Selecione a forma de pagamento</option>
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="cartao">Cartão (na entrega)</option>
                                    <option value="pix">PIX</option>
                                </select>
                            </div>
                            <div id="divTroco" class="mb-3" style="display: none;">
                                <label for="troco" class="form-label">Troco para quanto?</label>
                                <input type="number" class="form-control" id="troco" name="troco" step="0.01">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Resumo do Pedido</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($itens as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo $item['quantidade']; ?>x <?php echo $item['nome']; ?></span>
                                <span>R$ <?php echo number_format($item['preco_total'], 2, ',', '.'); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total do Pedido</strong>
                            <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                        </div>
                        <button type="button" class="btn btn-primary w-100 mt-3" onclick="finalizarPedido()">
                            Confirmar Pedido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscara para o CEP
        const cepInput = document.getElementById('cep');
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.slice(0, 5) + '-' + value.slice(5, 8);
            }
            e.target.value = value;
        });

        // Buscar endereço pelo CEP
        function buscarCep() {
            const cep = document.getElementById('cep').value.replace(/\D/g, '');
            const cepInput = document.getElementById('cep');
            const cepFeedback = document.getElementById('cepFeedback');
            
            if (cep.length !== 8) {
                cepInput.classList.add('is-invalid');
                cepFeedback.textContent = 'CEP inválido';
                return;
            }
            
            cepInput.classList.remove('is-invalid');
            
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        cepInput.classList.add('is-invalid');
                        cepFeedback.textContent = 'CEP não encontrado';
                        return;
                    }
                    
                    document.getElementById('rua').value = data.logradouro;
                    document.getElementById('bairro').value = data.bairro;
                    
                    // Foca no campo número após preencher o endereço
                    document.getElementById('numero').focus();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    cepInput.classList.add('is-invalid');
                    cepFeedback.textContent = 'Erro ao buscar CEP';
                });
        }

        // Buscar CEP ao pressionar Enter
        document.getElementById('cep').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarCep();
            }
        });
        
        // Mostrar/esconder campo de troco
        document.getElementById('formaPagamento').addEventListener('change', function() {
            const divTroco = document.getElementById('divTroco');
            divTroco.style.display = this.value === 'dinheiro' ? 'block' : 'none';
            if (this.value !== 'dinheiro') {
                document.getElementById('troco').value = '';
            }
        });

        function finalizarPedido() {
            const formEndereco = document.getElementById('formEndereco');
            const formPagamento = document.getElementById('formPagamento');
            
            if (!formEndereco.checkValidity() || !formPagamento.checkValidity()) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return;
            }
            
            const formData = new FormData();
            
            // Adicionar dados do endereço
            formData.append('rua', document.getElementById('rua').value);
            formData.append('numero', document.getElementById('numero').value);
            formData.append('complemento', document.getElementById('complemento').value);
            formData.append('bairro', document.getElementById('bairro').value);
            formData.append('referencia', document.getElementById('referencia').value);
            
            // Adicionar forma de pagamento
            const formaPagamento = document.getElementById('formaPagamento').value;
            formData.append('forma_pagamento', formaPagamento);
            
            if (formaPagamento === 'dinheiro') {
                formData.append('troco', document.getElementById('troco').value);
            }
            
            // Enviar pedido
            fetch('api/pedidos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pedido realizado com sucesso!');
                    window.location.href = 'meus-pedidos.php';
                } else {
                    alert(data.error || 'Erro ao finalizar pedido. Por favor, tente novamente.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao finalizar pedido. Por favor, tente novamente.');
            });
        }
    </script>
</body>
</html>
