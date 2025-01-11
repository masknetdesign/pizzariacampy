<?php
require_once "header.php";
require_once "../classes/Categoria.php";

$categoria = new Categoria($db);
$categorias = $categoria->listarTodas();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include "sidebar.php"; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gerenciar Categorias</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria">
                    <i class="bi bi-plus-lg"></i> Nova Categoria
                </button>
            </div>

            <!-- Lista de Categorias -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $categorias->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $item['id']; ?></td>
                                        <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $item['ativo'] ? 'success' : 'danger'; ?>">
                                                <?php echo $item['ativo'] ? 'Ativa' : 'Inativa'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    onclick="editarCategoria(<?php echo htmlspecialchars(json_encode($item)); ?>)">
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

<!-- Modal de Categoria -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCategoriaLabel">Nova Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCategoria">
                    <input type="hidden" id="categoria_id" name="id">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Categoria</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ativo" 
                                   name="ativo" value="1" checked>
                            <label class="form-check-label" for="ativo">
                                Categoria ativa
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarCategoria()">Salvar</button>
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
                <p>Tem certeza que deseja excluir esta categoria?</p>
                <p class="text-muted small">Se a categoria tiver produtos vinculados, ela será apenas inativada.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="excluirCategoria()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
let categoriaIdParaExcluir = null;
let modalCategoria = null;
let modalConfirmacao = null;

document.addEventListener('DOMContentLoaded', function() {
    modalCategoria = new bootstrap.Modal(document.getElementById('modalCategoria'));
    modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
});

function editarCategoria(categoria) {
    document.getElementById('categoria_id').value = categoria.id;
    document.getElementById('nome').value = categoria.nome;
    document.getElementById('descricao').value = categoria.descricao;
    document.getElementById('ativo').checked = categoria.ativo == 1;
    
    document.getElementById('modalCategoriaLabel').textContent = 'Editar Categoria';
    modalCategoria.show();
}

function confirmarExclusao(id) {
    categoriaIdParaExcluir = id;
    modalConfirmacao.show();
}

function excluirCategoria() {
    if (!categoriaIdParaExcluir) return;
    
    fetch('../api/categorias/excluir.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: categoriaIdParaExcluir
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao excluir categoria');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
    })
    .finally(() => {
        modalConfirmacao.hide();
        categoriaIdParaExcluir = null;
    });
}

function salvarCategoria() {
    const form = document.getElementById('formCategoria');
    const formData = new FormData(form);
    
    fetch('../api/categorias/salvar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao salvar categoria');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
    });
}

// Limpar formulário quando o modal for fechado
document.getElementById('modalCategoria').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formCategoria').reset();
    document.getElementById('categoria_id').value = '';
    document.getElementById('modalCategoriaLabel').textContent = 'Nova Categoria';
});
</script>

<?php require_once "../includes/footer.php"; ?>
