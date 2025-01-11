<?php
require_once "header.php";
require_once "../classes/Borda.php";

$borda = new Borda($db);
$bordas = $borda->listarTodas();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include "sidebar.php"; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gerenciar Bordas</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBorda">
                    <i class="bi bi-plus-lg"></i> Nova Borda
                </button>
            </div>

            <!-- Lista de Bordas -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Preço Adicional</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $bordas->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $item['id']; ?></td>
                                        <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                                        <td>R$ <?php echo number_format($item['preco_adicional'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $item['ativo'] ? 'success' : 'danger'; ?>">
                                                <?php echo $item['ativo'] ? 'Ativa' : 'Inativa'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    onclick="editarBorda(<?php echo htmlspecialchars(json_encode($item)); ?>)">
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

<!-- Modal de Borda -->
<div class="modal fade" id="modalBorda" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBordaLabel">Nova Borda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formBorda">
                    <input type="hidden" id="borda_id" name="id">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Borda</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" 
                               placeholder="Ex: Recheada com catupiry">
                    </div>
                    
                    <div class="mb-3">
                        <label for="preco_adicional" class="form-label">Preço Adicional</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" class="form-control" id="preco_adicional" 
                                   name="preco_adicional" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ativo" 
                                   name="ativo" value="1" checked>
                            <label class="form-check-label" for="ativo">
                                Borda ativa
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarBorda()">Salvar</button>
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
                <p>Tem certeza que deseja excluir esta borda?</p>
                <p class="text-muted small">
                    Se esta borda estiver sendo usada em pedidos, ela será apenas inativada.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="excluirBorda()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
let bordaIdParaExcluir = null;
let modalBorda = null;
let modalConfirmacao = null;

document.addEventListener('DOMContentLoaded', function() {
    modalBorda = new bootstrap.Modal(document.getElementById('modalBorda'));
    modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
});

function editarBorda(borda) {
    document.getElementById('borda_id').value = borda.id;
    document.getElementById('nome').value = borda.nome;
    document.getElementById('descricao').value = borda.descricao;
    document.getElementById('preco_adicional').value = borda.preco_adicional;
    document.getElementById('ativo').checked = borda.ativo == 1;
    
    document.getElementById('modalBordaLabel').textContent = 'Editar Borda';
    modalBorda.show();
}

function confirmarExclusao(id) {
    bordaIdParaExcluir = id;
    modalConfirmacao.show();
}

function excluirBorda() {
    if (!bordaIdParaExcluir) return;
    
    fetch('../api/bordas/excluir.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: bordaIdParaExcluir
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao excluir borda');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
    })
    .finally(() => {
        modalConfirmacao.hide();
        bordaIdParaExcluir = null;
    });
}

function salvarBorda() {
    const form = document.getElementById('formBorda');
    const formData = new FormData(form);
    
    fetch('../api/bordas/salvar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao salvar borda');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
    });
}

// Limpar formulário quando o modal for fechado
document.getElementById('modalBorda').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formBorda').reset();
    document.getElementById('borda_id').value = '';
    document.getElementById('modalBordaLabel').textContent = 'Nova Borda';
});
</script>

<?php require_once "../includes/footer.php"; ?>
