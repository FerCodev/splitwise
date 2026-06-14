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
            <span class="text-white small"><?= session()->get('userName') ?></span>
        <?php endif; ?>
    </div>
</nav>

<!-- Bottom tab bar (mobile) -->
<nav class="d-md-none bottom-tab-bar">
    <a href="<?= base_url('dashboard') ?>" class="bottom-tab-item <?= tabActive('dashboard', $current) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354zM2.5 7.707 8 2.207l5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/></svg>
        <span>Home</span>
    </a>
    <a href="<?= base_url('grupos') ?>" class="bottom-tab-item <?= tabActive('grupos', $current) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1A.26.26 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.27.27 0 0 1-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.5 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/></svg>
        <span>Grupos</span>
    </a>
    <a href="<?= base_url('gastos') ?>" class="bottom-tab-item <?= tabActive('gastos', $current) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M0 3a2 2 0 0 1 2-2h13.5a.5.5 0 0 1 0 1H15v2a1 1 0 0 1-1 1h-2V4a1 1 0 0 1 1-1h1V1H2a1 1 0 0 0-1 1v11.5a.5.5 0 0 0 1 0V3z"/><path d="M4.5 6a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0v-5a.5.5 0 0 1 .5-.5m3 0a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0v-5a.5.5 0 0 1 .5-.5m3 0a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0v-5a.5.5 0 0 1 .5-.5"/></svg>
        <span>Gastos</span>
    </a>
    <a href="<?= base_url('perfil') ?>" class="bottom-tab-item <?= tabActive('perfil', $current) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg>
        <span>Perfil</span>
    </a>
    <button class="bottom-tab-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="M&aacute;s">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/></svg>
        <span>M&aacute;s</span>
    </button>
</nav>

<!-- Mobile offcanvas menu -->
<div class="offcanvas offcanvas-bottom" tabindex="-1" id="mobileMenu" aria-label="Men&uacute; mobile">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Men&uacute;</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <div class="list-group list-group-flush">
            <a href="<?= base_url('reportes') ?>" class="list-group-item list-group-item-action">Reportes</a>
            <a href="<?= base_url('pagos') ?>" class="list-group-item list-group-item-action">Pagos</a>
            <a href="<?= base_url('mis-medios-de-cobro') ?>" class="list-group-item list-group-item-action">Mis medios de cobro</a>
            <?php if ($isAdmin): ?>
                <div class="list-group-item small text-muted fw-medium">Administraci&oacute;n</div>
                <a href="<?= base_url('categorias') ?>" class="list-group-item list-group-item-action ps-4">Categor&iacute;as</a>
                <a href="<?= base_url('usuarios') ?>" class="list-group-item list-group-item-action ps-4">Usuarios</a>
            <?php endif; ?>
            <div class="list-group-item list-group-item-action text-danger fw-medium" onclick="location.href='<?= base_url('logout') ?>'" style="cursor:pointer;">
                Cerrar Sesi&oacute;n
            </div>
        </div>
    </div>
</div>
