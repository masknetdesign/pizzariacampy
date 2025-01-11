<div class="sidebar col-md-3 col-lg-2 d-md-block bg-dark">
    <div class="position-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categorias.php' ? 'active' : ''; ?>" 
                   href="categorias.php">
                    <i class="bi bi-grid"></i>
                    Categorias
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'produtos.php' ? 'active' : ''; ?>" 
                   href="produtos.php">
                    <i class="bi bi-box"></i>
                    Produtos
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tamanhos.php' ? 'active' : ''; ?>" 
                   href="tamanhos.php">
                    <i class="bi bi-rulers"></i>
                    Tamanhos
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'bordas.php' ? 'active' : ''; ?>" 
                   href="bordas.php">
                    <i class="bi bi-circle"></i>
                    Bordas
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? 'active' : ''; ?>" 
                   href="pedidos.php">
                    <i class="bi bi-cart"></i>
                    Pedidos
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active' : ''; ?>" 
                   href="usuarios.php">
                    <i class="bi bi-people"></i>
                    Usuários
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Configurações</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'configuracoes.php' ? 'active' : ''; ?>" 
                   href="configuracoes.php">
                    <i class="bi bi-gear"></i>
                    Configurações
                </a>
            </li>
        </ul>
    </div>
</div>
