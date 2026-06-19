<?= view('partials/_head', ['title' => 'SplitWise - Mis medios de cobro']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Medios de cobro']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0 d-none d-md-block">Mis medios de cobro</h2>
            <a href="<?= base_url('mis-medios-de-cobro/nuevo') ?>" class="btn btn-primary btn-sm d-none d-md-inline-flex">+ Nuevo</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (empty($medios)): ?>
            <div class="empty-state">
                <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5Zm-9 2a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1H6Z"/></svg>
                <div class="empty-state-title">No ten&eacute;s medios de cobro</div>
                <div class="empty-state-text">Agreg&aacute; tus datos de cobro para recibir pagos.</div>
                <a href="<?= base_url('mis-medios-de-cobro/nuevo') ?>" class="btn btn-primary">Agregar medio</a>
            </div>
        <?php else: ?>
            <div class="card">
                <?php foreach ($medios as $m): ?>
                    <div class="financial-list-item" style="flex-wrap:wrap;border-left:3px solid <?= $m['activo'] ? 'var(--accent)' : 'var(--danger)' ?>;">
                        <div class="avatar avatar-sm" style="background:<?= $m['activo'] ? 'var(--accent-light)' : 'var(--danger-light)' ?>;color:<?= $m['activo'] ? 'var(--accent)' : 'var(--danger)' ?>;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5Z"/></svg>
                        </div>
                        <div class="financial-list-item-info">
                            <div class="financial-list-item-title" style="font-size:14px;">
                                <?= esc($m['nombre'] ?? $m['tipo']) ?>
                                <?php if ($m['favorito']): ?>
                                    <span class="text-warning ms-1" title="Favorito">&starf;</span>
                                <?php endif; ?>
                                <span class="badge <?= $m['activo'] ? 'bg-success' : 'bg-danger' ?>" style="font-size:10px;padding:2px 6px;margin-left:4px;"><?= $m['activo'] ? 'Activo' : 'Inactivo' ?></span>
                            </div>
                            <div class="financial-list-item-subtitle">
                                <?php if ($m['titular']): ?>Titular: <?= esc($m['titular']) ?> &middot; <?php endif; ?>
                                <?php if ($m['alias']): ?>Alias: <?= esc($m['alias']) ?> &middot; <?php endif; ?>
                                <?php if ($m['banco']): ?><?= esc($m['banco']) ?><?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex gap-1 w-100 mt-2">
                            <a href="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/editar') ?>" class="btn btn-primary btn-sm">Editar</a>
                            <?php if (!$m['favorito']): ?>
                                <form action="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/favorito') ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-warning btn-sm">Favorito</button>
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
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('mis-medios-de-cobro/nuevo') ?>" class="d-md-none fab" aria-label="Nuevo medio">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
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
