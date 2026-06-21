<?php
    $current = service('uri')->getSegment(1) ?: 'dashboard';
    $isAdmin = session()->get('userRole') === 'admin';
    $mobileTitle = $pageTitle ?? 'Home';
    function tabActive($tab, $current) {
        return $tab === $current ? 'active' : '';
    }
?>

<!-- Desktop sidebar -->
<aside class="desktop-sidebar">
    <div class="desktop-sidebar-brand">SplitWise</div>
    <nav class="desktop-sidebar-nav">
        <a href="<?= base_url('dashboard') ?>" class="desktop-sidebar-link <?= tabActive('dashboard', $current) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354zM2.5 7.707 8 2.207l5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/></svg>
            Home
        </a>
        <a href="<?= base_url('grupos') ?>" class="desktop-sidebar-link <?= tabActive('grupos', $current) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
            Grupos
        </a>
        <a href="<?= base_url('gastos') ?>" class="desktop-sidebar-link <?= tabActive('gastos', $current) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5h-2v12h2V2zm-4-2v2h2V2H9zm-4 4v2h2V7H5zm-4 4v2h2v-2H1z"/></svg>
            Gastos
        </a>
        <a href="<?= base_url('pagos') ?>" class="desktop-sidebar-link <?= tabActive('pagos', $current) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V3zm1 0v10h12V3H2z"/></svg>
            Pagos
        </a>
        <a href="<?= base_url('reportes') ?>" class="desktop-sidebar-link <?= tabActive('reportes', $current) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.5A1.5 1.5 0 0 1 1.5 0h7l.793.793a.5.5 0 0 1 .353.146L11.5 2.5h3A1.5 1.5 0 0 1 16 4v10.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5zM1.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5V4a.5.5 0 0 0-.5-.5h-3.5l-1-1H1.5z"/></svg>
            Reportes
        </a>
        <a href="<?= base_url('mis-medios-de-cobro') ?>" class="desktop-sidebar-link <?= tabActive('mis-medios-de-cobro', $current) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5Z"/></svg>
            Medios de cobro
        </a>

        <?php if ($isAdmin): ?>
            <div class="desktop-sidebar-section">Administraci&oacute;n</div>
            <a href="<?= base_url('categorias') ?>" class="desktop-sidebar-link <?= tabActive('categorias', $current) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.393-.13-.803-.264-1.186-.406-1.078-.401-1.868-.465-2.604-.138v-6.972c0-.507-.133-.954-.4-1.358a1.2 1.2 0 0 0-.479-.458c-.295-.166-.651-.19-1.078-.093-.47.106-.896.336-1.356.628V2.828z"/></svg>
                Categor&iacute;as
            </a>
            <a href="<?= base_url('usuarios') ?>" class="desktop-sidebar-link <?= tabActive('usuarios', $current) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1A.26.26 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.27.27 0 0 1-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.5 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/></svg>
                Usuarios
            </a>
            <a href="<?= base_url('admin/catalogo-tarjetas') ?>" class="desktop-sidebar-link <?= tabActive('admin/catalogo-tarjetas', $current) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V3zm1 0v10h12V3H2zm3 2h6v1H5V5zm0 2h6v1H5V7zm0 2h4v1H5V9z"/></svg>
                Cat&aacute;logo
            </a>
        <?php endif; ?>
    </nav>
    <div class="desktop-sidebar-footer">
        <div class="desktop-sidebar-user">
            <div class="avatar" style="background:var(--primary);width:36px;height:36px;font-size:14px;"><?= esc(strtoupper(substr((string) session()->get('userName'), 0, 1))) ?></div>
            <div class="desktop-sidebar-user-info">
                <div class="desktop-sidebar-user-name"><?= esc(session()->get('userName')) ?></div>
                <div class="desktop-sidebar-user-email"><?= esc(session()->get('userEmail')) ?></div>
            </div>
        </div>
        <div style="padding:4px 8px;">
            <a href="<?= base_url('perfil') ?>" class="desktop-sidebar-link">Mi perfil</a>
            <a href="<?= base_url('logout') ?>" class="desktop-sidebar-link" style="color:var(--danger);">Cerrar sesi&oacute;n</a>
        </div>
    </div>
</aside>

<!-- Mobile top bar -->
<nav class="navbar navbar-dark d-lg-none" style="background:var(--primary);">
    <div class="container mobile-topbar">
        <span class="mobile-page-title"><?= esc($mobileTitle) ?></span>
    </div>
</nav>

<!-- Header offcanvas menu (mobile) -->
<div class="offcanvas offcanvas-end mobile-menu" tabindex="-1" id="headerMenu" aria-label="Men&uacute; principal">
    <div class="offcanvas-header mobile-menu-header">
        <h5 class="offcanvas-title">Men&uacute;</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body mobile-menu-body">
        <div class="mobile-menu-user">
            <div class="mobile-menu-avatar" aria-hidden="true"><?= esc(strtoupper(substr((string) session()->get('userName'), 0, 1))) ?></div>
            <div class="mobile-menu-user-copy">
                <div class="mobile-menu-name"><?= esc(session()->get('userName')) ?></div>
                <div class="mobile-menu-email"><?= esc(session()->get('userEmail')) ?></div>
            </div>
        </div>

        <div class="mobile-menu-section">
            <div class="mobile-menu-section-title">Navegaci&oacute;n</div>
            <a href="<?= base_url('perfil') ?>" class="mobile-menu-link">Mi perfil</a>
            <a href="<?= base_url('grupos') ?>" class="mobile-menu-link">Grupos</a>
            <a href="<?= base_url('gastos') ?>" class="mobile-menu-link">Gastos</a>
            <a href="<?= base_url('pagos') ?>" class="mobile-menu-link">Pagos</a>
            <a href="<?= base_url('reportes') ?>" class="mobile-menu-link">Reportes</a>
            <a href="<?= base_url('mis-medios-de-cobro') ?>" class="mobile-menu-link">Mis medios de cobro</a>
            <?php if ($current === 'dashboard'): ?>
                <button type="button" class="mobile-menu-link mobile-menu-link-button" data-bs-dismiss="offcanvas" onclick="var el=document.getElementById('resumenCollapse');if(el){bootstrap.Collapse.getOrCreateInstance(el).toggle()}">
                    Resumen
                </button>
            <?php endif; ?>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mobile-menu-section">
                <div class="mobile-menu-section-title">Administraci&oacute;n</div>
                <a href="<?= base_url('categorias') ?>" class="mobile-menu-link">Categor&iacute;as</a>
                <a href="<?= base_url('usuarios') ?>" class="mobile-menu-link">Usuarios</a>
                <a href="<?= base_url('admin/catalogo-tarjetas') ?>" class="mobile-menu-link">Cat&aacute;logo</a>
            </div>
        <?php endif; ?>

        <div class="mobile-menu-session">
            <a href="<?= base_url('logout') ?>" class="mobile-menu-logout">
                <span class="mobile-menu-logout-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-6A1.5 1.5 0 0 1 2 11.5v-7A1.5 1.5 0 0 1 3.5 3h6a.5.5 0 0 1 0 1h-6A.5.5 0 0 0 3 4.5v7a.5.5 0 0 0 .5.5h6a.5.5 0 0 1 .5.5z"/><path fill-rule="evenodd" d="M11.854 5.146a.5.5 0 0 0-.708.708L12.293 7H6.5a.5.5 0 0 0 0 1h5.793l-1.147 1.146a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0 0-.708l-2-2z"/></svg>
                </span>
                <span>
                    <span class="mobile-menu-logout-title">Cerrar sesi&oacute;n</span>
                    <span class="mobile-menu-logout-subtitle">Salir de esta cuenta</span>
                </span>
            </a>
        </div>
    </div>
</div>

<!-- Bottom tab bar (mobile) -->
<nav class="d-lg-none bottom-tab-bar">
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
    <button type="button" class="bottom-tab-item" data-bs-toggle="offcanvas" data-bs-target="#headerMenu" aria-label="Men&uacute;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/></svg>
        <span>Men&uacute;</span>
    </button>
</nav>

<div class="desktop-main">
<?= view('partials/_alerts') ?>
