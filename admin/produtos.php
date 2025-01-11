<?php
require_once "header.php";
require_once "../classes/Produto.php";

$produto = new Produto($db);
$produtos = $produto->listarTodos();
$categorias = $produto->listarCategorias();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include "sidebar.php"; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gerenciar Produtos</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduto">
                    <i class="bi bi-plus-lg"></i> Novo Produto
                </button>
            </div>

            <!-- Tabela de Produtos -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Imagem</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Preço</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $produtos->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $item['id']; ?></td>
                                        <td>
                                            <?php if ($item['imagem']): ?>
                                                <img src="../<?php echo htmlspecialchars($item['imagem']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['nome']); ?>"
                                                     class="img-thumbnail" style="max-width: 50px;">
                                            <?php else: ?>
                                                <span class="text-muted">Sem imagem</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($item['categoria_nome']); ?></td>
                                        <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $item['ativo'] ? 'success' : 'danger'; ?>">
                                                <?php echo $item['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    onclick="editarProduto(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmarExclusao(<?php echo $item['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal de Produto -->
<div class="modal fade" id="modalProduto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProdutoLabel">Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formProduto" enctype="multipart/form-data">
                    <input type="hidden" id="produto_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Produto</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoria_id" class="form-label">Categoria</label>
                                <select class="form-select" id="categoria_id" name="categoria_id" required>
                                    <option value="">Selecione uma categoria</option>
                                    <?php while ($categoria = $categorias->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $categoria['id']; ?>">
                                            <?php echo htmlspecialchars($categoria['nome']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="preco" class="form-label">Preço</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" id="preco" name="preco" 
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="imagem" class="form-label">Imagem</label>
                                <input type="file" class="form-control" id="imagem" name="imagem" 
                                       accept="image/*">
                                <div class="form-text">Selecione uma imagem para o produto (opcional)</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ativo" 
                                   name="ativo" value="1" checked>
                            <label class="form-check-label" for="ativo">
                                Produto ativo
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarProduto()">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este produto?</p>
                <p class="text-muted small">Se o produto já estiver em algum pedido, ele será apenas inativado.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="excluirProduto()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
let produtoIdParaExcluir = null;
let modalProduto = null;
let modalConfirmacao = null;

document.addEventListener('DOMContentLoaded', function() {
    modalProduto = new bootstrap.Modal(document.getElementById('modalProduto'));
    modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
});

function editarProduto(produto) {
    document.getElementById('produto_id').value = produto.id;
    document.getElementById('nome').value = produto.nome;
    document.getElementById('categoria_id').value = produto.categoria_id;
    document.getElementById('descricao').value = produto.descricao;
    document.getElementById('preco').value = produto.preco;
    document.getElementById('ativo').checked = produto.ativo == 1;
    
    document.getElementById('modalProdutoLabel').textContent = 'Editar Produto';
    modalProduto.show();
}

function confirmarExclusao(id) {
    produtoIdParaExcluir = id;
    modalConfirmacao.show();
}

function excluirProduto() {
    if (!produtoIdParaExcluir) return;
    
    fetch('../api/produtos/excluir.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: produtoIdParaExcluir
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao excluir produto');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
    })
    .finally(() => {
        modalConfirmacao.hide();
        produtoIdParaExcluir = null;
    });
}

function salvarProduto() {
    const form = document.getElementById('formProduto');
    const formData = new FormData(form);
    
    fetch('../api/produtos/salvar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao salvar produto');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
    });
}

// Limpar formulário quando o modal for fechado
document.getElementById('modalProduto').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formProduto').reset();
    document.getElementById('produto_id').value = '';
    document.getElementById('modalProdutoLabel').textContent = 'Novo Produto';
});
</script>

<?php require_once "../includes/footer.php"; ?>
