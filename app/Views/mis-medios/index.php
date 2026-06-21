<?= view('partials/_head', ['title' => 'SplitWise - Mis medios de cobro']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Medios de cobro']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0 d-none d-md-block">Mis medios de cobro</h2>
            <a href="<?= base_url('mis-medios-de-cobro/nuevo') ?>" class="btn btn-primary d-none d-md-inline-flex">+ Nuevo</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (empty($medios)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:64px;height:64px;background:#e2e8f0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#64748b" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5Zm-9 2a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1H6Z"/></svg>
                    </div>
                    <p class="text-muted mb-3">No ten&eacute;s medios de cobro registrados.</p>
                    <a href="<?= base_url('mis-medios-de-cobro/nuevo') ?>" class="btn btn-primary d-none d-md-inline-flex">Agregar medio de cobro</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($medios as $m): ?>
                    <div class="col-12">
                        <div class="payment-bank-card <?= $m['activo'] ? '' : 'payment-bank-card-inactive' ?>">
                            <div class="payment-bank-card-top">
                                <div class="min-width-0">
                                    <div class="payment-bank-label"><?= esc($m['tipo']) ?></div>
                                    <div class="payment-bank-title text-truncate"><?= esc($m['banco'] ?: ($m['nombre'] ?? $m['tipo'])) ?></div>
                                </div>
                                <div class="payment-bank-status">
                                    <span class="badge <?= $m['activo'] ? 'bg-light text-dark' : 'bg-danger' ?> flex-shrink-0"><?= $m['activo'] ? 'Activo' : 'Inactivo' ?></span>
                                            <?php if (!$m['favorito']): ?>
                                                <form action="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/favorito') ?>" method="post" class="d-inline flex-shrink-0">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="payment-bank-fav" aria-label="Marcar como favorito" title="Marcar como favorito">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.806-3.536 1.816.67-3.808a.52.52 0 0 0-.15-.467L1.44 6.721l3.878-.55a.52.52 0 0 0 .393-.288L8 2.223l2.29 3.66a.52.52 0 0 0 .393.288l3.878.55-3.315 3.148a.52.52 0 0 0-.15.467l.67 3.808-3.536-1.816a.52.52 0 0 0-.458 0z"/></svg>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form action="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/favorito') ?>" method="post" class="d-inline flex-shrink-0">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="payment-bank-fav payment-bank-fav-active" aria-label="Quitar favorito" title="Quitar favorito">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                </div>
                            </div>

                            <div class="payment-bank-body">
                                <div class="payment-bank-number">
                                    <?= esc($m['cbu_cvu'] ?: ($m['alias'] ?: ($m['payment_link'] ?: 'Sin dato cargado'))) ?>
                                </div>
                                <div class="payment-bank-meta">
                                            <?php if ($m['titular']): ?>
                                                <div><span>Titular</span><strong><?= esc($m['titular']) ?></strong></div>
                                            <?php endif; ?>
                                            <?php if ($m['alias']): ?>
                                                <div class="payment-bank-copy">
                                                    <span>Alias</span><strong class="text-truncate"><?= esc($m['alias']) ?></strong>
                                                    <button type="button" class="copiar-icon-btn" data-copiar="<?= esc($m['alias'], 'attr') ?>" aria-label="Copiar alias" title="Copiar alias">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($m['cbu_cvu']): ?>
                                                <div class="payment-bank-copy">
                                                    <span>CBU/CVU</span><strong class="text-truncate"><?= esc($m['cbu_cvu']) ?></strong>
                                                    <button type="button" class="copiar-icon-btn" data-copiar="<?= esc($m['cbu_cvu'], 'attr') ?>" aria-label="Copiar CBU/CVU" title="Copiar CBU/CVU">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($m['payment_link']): ?>
                                                <div><span>Link</span>
                                                    <a href="<?= esc($m['payment_link']) ?>" target="_blank" rel="noopener noreferrer">Abrir</a>
                                                </div>
                                            <?php endif; ?>
                                </div>
                            </div>

                                <div class="payment-bank-actions">
                                    <a href="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/editar') ?>" class="btn btn-primary btn-sm">Editar</a>
                                    <?php if ($m['activo']): ?>
                                        <form action="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/toggle') ?>" method="post" class="d-inline" id="toggle-medio-<?= $m['id'] ?>">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn btn-warning btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#confirmModal"
                                                data-confirm-title="Desactivar medio"
                                                data-confirm-msg="Este medio dejar&aacute; de mostrarse como opci&oacute;n de pago."
                                                data-confirm-btn="Desactivar"
                                                data-confirm-form="toggle-medio-<?= $m['id'] ?>">Desactivar</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/toggle') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-success btn-sm">Activar</button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="<?= base_url('mis-medios-de-cobro/' . $m['id']) ?>" method="post" class="d-inline" id="delete-medio-<?= $m['id'] ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="button" class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#confirmModal"
                                            data-confirm-title="Eliminar medio"
                                            data-confirm-msg="Se eliminar&aacute; este medio de cobro. Los pagos ya registrados no se modifican."
                                            data-confirm-btn="Eliminar"
                                            data-confirm-form="delete-medio-<?= $m['id'] ?>">Eliminar</button>
                                    </form>
                                </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('mis-medios-de-cobro/nuevo') ?>" class="fab fab-extended d-lg-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
            <span>Nuevo medio</span>
        </a>
    </div>

    <script>
        document.querySelectorAll('.copiar-icon-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var texto = this.getAttribute('data-copiar');
                var originalHTML = this.innerHTML;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(texto).then(function() {
                        this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="var(--accent)" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022z"/></svg>';
                        var self = this;
                        setTimeout(function() { self.innerHTML = originalHTML; }, 2000);
                    }.bind(this)).catch(function() {
                        fallbackCopiar(texto, this);
                    }.bind(this));
                } else {
                    fallbackCopiar(texto, this);
                }
            });
        });

        function fallbackCopiar(texto, btn) {
            var ta = document.createElement('textarea');
            ta.value = texto;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                var originalHTML = btn.innerHTML;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="var(--accent)" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022z"/></svg>';
                setTimeout(function() { btn.innerHTML = originalHTML; }, 2000);
            } catch (e) {
                alert('No se pudo copiar al portapapeles.');
            }
            document.body.removeChild(ta);
        }
    </script>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
