<?php
    $current = service('uri')->getSegment(1) ?: 'dashboard';
    $isAdmin = session()->get('userRole') === 'admin';
    function tabActive($tab, $current) {
        return $tab === $current ? 'active' : '';
    }
?>

<!-- Desktop navbar -->
<nav class="navbar navbar-dark bg-primary d-none d-md-block">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('dashboard') ?>">SplitWise</a>
        <div class="d-flex align-items-center gap-1">
            <a href="<?= base_url('grupos') ?>" class="btn btn-outline-light btn-sm">Grupos</a>
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Men&uacute;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/></svg>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text"><?= esc(session()->get('userName')) ?></span></li>
                    <li><span class="dropdown-item-text text-muted small"><?= esc(session()->get('userEmail')) ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= base_url('perfil') ?>">Mi perfil</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('grupos') ?>">Grupos</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('gastos') ?>">Gastos</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('pagos') ?>">Pagos</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('reportes') ?>">Reportes</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('mis-medios-de-cobro') ?>">Mis medios de cobro</a></li>
                    <?php if ($isAdmin): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><span class="dropdown-item-text small text-muted">Administraci&oacute;n</span></li>
                        <li><a class="dropdown-item" href="<?= base_url('categorias') ?>">Categor&iacute;as</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('usuarios') ?>">Usuarios</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">Cerrar Sesi&oacute;n</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile top bar -->
<nav class="navbar navbar-dark bg-primary d-md-none">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('dashboard') ?>">SplitWise</a>
        <?php if ($current !== 'login'): ?>
            <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#headerMenu" aria-label="Men&uacute;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/></svg>
            </button>
        <?php endif; ?>
    </div>
</nav>

<!-- Header offcanvas menu (mobile) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="headerMenu" aria-label="Men&uacute; principal">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Men&uacute;</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="list-group list-group-flush">
            <div class="list-group-item">
                <div class="fw-semibold"><?= esc(session()->get('userName')) ?></div>
                <div class="text-muted small"><?= esc(session()->get('userEmail')) ?></div>
            </div>
            <a href="<?= base_url('perfil') ?>" class="list-group-item list-group-item-action">Mi perfil</a>
            <a href="<?= base_url('grupos') ?>" class="list-group-item list-group-item-action">Grupos</a>
            <a href="<?= base_url('gastos') ?>" class="list-group-item list-group-item-action">Gastos</a>
            <a href="<?= base_url('pagos') ?>" class="list-group-item list-group-item-action">Pagos</a>
            <a href="<?= base_url('reportes') ?>" class="list-group-item list-group-item-action">Reportes</a>
            <a href="<?= base_url('mis-medios-de-cobro') ?>" class="list-group-item list-group-item-action">Mis medios de cobro</a>
            <?php if ($current === 'dashboard'): ?>
                <button type="button" class="list-group-item list-group-item-action" data-bs-dismiss="offcanvas" onclick="var el=document.getElementById('resumenCollapse');if(el){bootstrap.Collapse.getOrCreateInstance(el).toggle()}">
                    Resumen
                </button>
            <?php endif; ?>
            <?php if ($isAdmin): ?>
                <div class="list-group-item small text-muted fw-medium">Administraci&oacute;n</div>
                <a href="<?= base_url('categorias') ?>" class="list-group-item list-group-item-action ps-4">Categor&iacute;as</a>
                <a href="<?= base_url('usuarios') ?>" class="list-group-item list-group-item-action ps-4">Usuarios</a>
            <?php endif; ?>
            <a href="<?= base_url('logout') ?>" class="list-group-item list-group-item-action text-danger fw-medium">Cerrar Sesi&oacute;n</a>
        </div>
    </div>
</div>

<!-- Bottom tab bar (mobile) -->
<nav class="d-md-none bottom-tab-bar">
    <a href="<?= base_url('dashboard') ?>" class="bottom-tab-item <?= tabActive('dashboard', $current) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354zM2.5 7.707 8 2.207l5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/></svg>
        <span>Home</span>
    </a>
    <a href="<?= base_url('reportes') ?>" class="bottom-tab-item <?= tabActive('reportes', $current) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.5A1.5 1.5 0 0 1 1.5 0h7l.793.793a.5.5 0 0 1 .353.146L11.5 2.5h3A1.5 1.5 0 0 1 16 4v10.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5zM1.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5V4a.5.5 0 0 0-.5-.5h-3.5l-1-1H1.5z"/><path d="M5 6.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.m0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.m0 2a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5z"/></svg>
        <span>Reportes</span>
    </a>
    <a href="<?= base_url('perfil') ?>" class="bottom-tab-item <?= tabActive('perfil', $current) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg>
        <span>Perfil</span>
    </a>
</nav>


