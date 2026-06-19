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
                        <div class="card border-0 shadow-sm" style="border-left: 3px solid <?= $m['activo'] ? '#16a34a' : '#dc2626' ?>;">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-3 mb-2">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:42px;height:42px;background:<?= $m['activo'] ? '#dcfce7' : '#fee2e2' ?>;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="<?= $m['activo'] ? '#16a34a' : '#dc2626' ?>" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5Zm-9 2a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1H6Z"/></svg>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <span class="fw-semibold"><?= esc($m['nombre'] ?? $m['tipo']) ?></span>
                                                <?php if ($m['favorito']): ?>
                                                    <span class="text-warning ms-1" title="Favorito">&starf;</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <?php if ($m['activo']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="small text-muted mb-2">
                                            <?php if ($m['titular']): ?>
                                                <div><span class="fw-medium">Titular:</span> <?= esc($m['titular']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($m['alias']): ?>
                                                <div><span class="fw-medium">Alias:</span> <?= esc($m['alias']) ?>
                                                    <button type="button" class="btn btn-sm btn-secondary py-0 px-1 copiar-btn" data-copiar="<?= esc($m['alias'], 'attr') ?>" title="Copiar alias">Copiar</button>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($m['cbu_cvu']): ?>
                                                <div><span class="fw-medium">CBU/CVU:</span> <?= esc($m['cbu_cvu']) ?>
                                                    <button type="button" class="btn btn-sm btn-secondary py-0 px-1 copiar-btn" data-copiar="<?= esc($m['cbu_cvu'], 'attr') ?>" title="Copiar CBU/CVU">Copiar</button>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($m['banco']): ?>
                                                <div><span class="fw-medium">Banco:</span> <?= esc($m['banco']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($m['payment_link']): ?>
                                                <div><span class="fw-medium">Link:</span>
                                                    <a href="<?= esc($m['payment_link']) ?>" target="_blank" rel="noopener noreferrer">Abrir</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/editar') ?>" class="btn btn-primary btn-sm">Editar</a>
                                    <?php if (!$m['favorito']): ?>
                                        <form action="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/favorito') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-warning btn-sm" title="Marcar como favorito">Favorito</button>
                                        </form>
                                    <?php endif; ?>
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
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('mis-medios-de-cobro/nuevo') ?>" class="fab fab-extended d-md-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
            <span>Nuevo medio</span>
        </a>
    </div>

    <script>
        document.querySelectorAll('.copiar-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var texto = this.getAttribute('data-copiar');
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(texto).then(function() {
                        var original = this.textContent;
                        this.textContent = 'Copiado!';
                        var self = this;
                        setTimeout(function() { self.textContent = original; }, 2000);
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
                var original = btn.textContent;
                btn.textContent = 'Copiado!';
                setTimeout(function() { btn.textContent = original; }, 2000);
            } catch (e) {
                alert('No se pudo copiar al portapapeles.');
            }
            document.body.removeChild(ta);
        }
    </script>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
