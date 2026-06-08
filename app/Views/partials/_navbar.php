<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('dashboard') ?>">SplitWise</a>
        <div class="d-flex align-items-center gap-1">
            <a href="<?= base_url('grupos') ?>" class="btn btn-outline-light btn-sm">Grupos</a>
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/></svg>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text"><?= session()->get('userName') ?></span></li>
                    <li><span class="dropdown-item-text text-muted small"><?= session()->get('userEmail') ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= base_url('grupos') ?>">Grupos</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('gastos') ?>">Gastos</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('pagos') ?>">Pagos</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('categorias') ?>">Categor&iacute;as</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('usuarios') ?>">Usuarios</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
