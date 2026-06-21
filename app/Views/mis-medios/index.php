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
            <div class="row g-3">                <?php foreach ($medios as $m): ?>
                    <div class="col-12">
                        <div class="payment-bank-card <?= $m['activo'] ? '' : 'payment-bank-card-inactive' ?>">
                            <div class="payment-bank-card-top">
                                <div class="payment-bank-name text-truncate"><?= esc($m['nombre'] ?? $m['tipo']) ?></div>
                                <div class="payment-bank-controls">
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
                                    <a href="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/editar') ?>" class="payment-bank-gear" aria-label="Configurar medio de cobro" title="Configurar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.43 12.98c.04-.32.07-.65.07-.98s-.02-.66-.07-.98l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.37 7.37 0 0 0-1.69-.98L14.5 2.42A.5.5 0 0 0 14 2h-4a.5.5 0 0 0-.5.42L9.12 5.07c-.61.24-1.18.57-1.69.98l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64l2.11 1.65c-.04.32-.07.65-.07.98s.02.66.07.98l-2.11 1.65a.5.5 0 0 0-.12.64l2 3.46c.13.22.39.31.6.22l2.49-1c.51.4 1.08.73 1.69.98l.38 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.38-2.65c.61-.24 1.18-.57 1.69-.98l2.49 1c.22.09.48 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.65Z"/></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="payment-bank-body">
                                <div class="payment-bank-meta payment-bank-meta-primary">
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
                                    <?php if (!$m['titular'] && !$m['alias'] && !$m['cbu_cvu']): ?>
                                        <div><span>Datos</span><strong>Sin datos cargados</strong></div>
                                    <?php endif; ?>
                                </div>
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
