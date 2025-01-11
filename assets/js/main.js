// Variáveis globais
let modalPedido;
let produtoAtual = null;
let carrinhoItens = [];

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    modalPedido = new bootstrap.Modal(document.getElementById('modalPedido'));
    atualizarContadorCarrinho();
});

// Função para abrir o modal de pedido
function abrirModalPedido(produtoId) {
    if (!verificarLogin()) {
        window.location.href = 'login.php';
        return;
    }
    
    produtoAtual = produtoId;
    document.getElementById('produto_id').value = produtoId;
    modalPedido.show();
}

// Função para verificar se o usuário está logado
function verificarLogin() {
    return document.querySelector('a[href="logout.php"]') !== null;
}

// Função para adicionar ao carrinho
function adicionarAoCarrinho(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('formPedido'));
    
    fetch('api/carrinho/adicionar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            modalPedido.hide();
            atualizarContadorCarrinho();
            mostrarMensagem('Produto adicionado ao carrinho!', 'success');
        } else {
            mostrarMensagem(data.message || 'Erro ao adicionar ao carrinho', 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao processar o pedido', 'danger');
    });
}

// Função para atualizar o contador do carrinho
function atualizarContadorCarrinho() {
    fetch('api/carrinho/contar.php')
        .then(response => response.json())
        .then(data => {
            const contador = document.getElementById('carrinho-contador');
            if (contador) {
                contador.textContent = data.quantidade || 0;
            }
        })
        .catch(error => console.error('Erro:', error));
}

// Função para mostrar mensagens
function mostrarMensagem(mensagem, tipo) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Event Listeners
document.getElementById('formPedido')?.addEventListener('submit', adicionarAoCarrinho);

// Atualizar preço quando mudar a borda
document.getElementById('borda')?.addEventListener('change', function() {
    const precoBase = parseFloat(document.getElementById('preco_base').value);
    const precoBorda = parseFloat(this.options[this.selectedIndex].dataset.preco || 0);
    const precoTotal = precoBase + precoBorda;
    
    document.getElementById('preco_total').textContent = 
        'R$ ' + precoTotal.toFixed(2).replace('.', ',');
});
