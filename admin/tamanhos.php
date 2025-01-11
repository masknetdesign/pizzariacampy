<?php
require_once "header.php";
require_once "../classes/Tamanho.php";

$tamanho = new Tamanho($db);
$tamanhos = $tamanho->listarTodos();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include "sidebar.php"; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gerenciar Tamanhos</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTamanho">
                    <i class="bi bi-plus-lg"></i> Novo Tamanho
                </button>
            </div>

            <!-- Lista de Tamanhos -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Multiplicador</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $tamanhos->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $item['id']; ?></td>
                                        <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                                        <td><?php echo number_format($item['multiplicador_preco'], 2, ',', '.'); ?>x</td>
                                        <td>
                                            <span class="badge bg-<?php echo $item['ativo'] ? 'success' : 'danger'; ?>">
                                                <?php echo $item['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    onclick="editarTamanho(<?php echo htmlspecialchars(json_encode($item)); ?>)">
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

<!-- Modal de Tamanho -->
<div class="modal fade" id="modalTamanho" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTamanhoLabel">Novo Tamanho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTamanho">
                    <input type="hidden" id="tamanho_id" name="id">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Tamanho</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" 
                               placeholder="Ex: 6 fatias, serve 2 pessoas">
                    </div>
                    
                    <div class="mb-3">
                        <label for="multiplicador_preco" class="form-label">Multiplicador de Preço</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="multiplicador_preco" 
                                   name="multiplicador_preco" step="0.1" min="0.1" required>
                            <span class="input-group-text">x</span>
                        </div>
                        <div class="form-text">
                            Ex: 1.0 = preço normal, 1.5 = 50% mais caro, 0.8 = 20% mais barato
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ativo" 
                                   name="ativo" value="1" checked>
                            <label class="form-check-label" for="ativo">
                                Tamanho ativo
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarTamanho()">Salvar</button>
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
                <p>Tem certeza que deseja excluir este tamanho?</p>
                <p class="text-muted small">
                    Se este tamanho estiver sendo usado em produtos, ele será apenas inativado.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="excluirTamanho()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
let tamanhoIdParaExcluir = null;
let modalTamanho = null;
let modalConfirmacao = null;

document.addEventListener('DOMContentLoaded', function() {
    modalTamanho = new bootstrap.Modal(document.getElementById('modalTamanho'));
    modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
});

function editarTamanho(tamanho) {
    document.getElementById('tamanho_id').value = tamanho.id;
    document.getElementById('nome').value = tamanho.nome;
    document.getElementById('descricao').value = tamanho.descricao;
    document.getElementById('multiplicador_preco').value = tamanho.multiplicador_preco;
    document.getElementById('ativo').checked = tamanho.ativo == 1;
    
    document.getElementById('modalTamanhoLabel').textContent = 'Editar Tamanho';
    modalTamanho.show();
}

function confirmarExclusao(id) {
    tamanhoIdParaExcluir = id;
    modalConfirmacao.show();
}

function excluirTamanho() {
    if (!tamanhoIdParaExcluir) return;
    
    fetch('../api/tamanhos/excluir.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: tamanhoIdParaExcluir
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao excluir tamanho');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
    })
    .finally(() => {
        modalConfirmacao.hide();
        tamanhoIdParaExcluir = null;
    });
}

function salvarTamanho() {
    const form = document.getElementById('formTamanho');
    const formData = new FormData(form);
    
    fetch('../api/tamanhos/salvar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao salvar tamanho');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
    });
}

// Limpar formulário quando o modal for fechado
document.getElementById('modalTamanho').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formTamanho').reset();
    document.getElementById('tamanho_id').value = '';
    document.getElementById('modalTamanhoLabel').textContent = 'Novo Tamanho';
});
</script>

<?php require_once "../includes/footer.php"; ?>
