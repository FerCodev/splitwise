<?= view('partials/_head', ['title' => 'SplitWise - Mis medios de cobro']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Mis medios de cobro</h2>
            <a href="<?= base_url('mis-medios-de-cobro/nuevo') ?>" class="btn btn-primary">+ Nuevo</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Alias</th>
                                <th>CBU/CVU</th>
                                <th>Banco</th>
                                <th>Estado</th>
                                <th>Fav</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($medios)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No tenés medios de cobro registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($medios as $m): ?>
                                    <tr>
                                        <td class="fw-medium"><?= esc($m['nombre'] ?? $m['tipo']) ?></td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?= esc($m['tipo']) ?></span>
                                        </td>
                                        <td><?= esc($m['alias'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($m['cbu_cvu']): ?>
                                                <span class="cbu-cvu-text"><?= esc($m['cbu_cvu']) ?></span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1 copiar-btn" data-copiar="<?= esc($m['cbu_cvu'], 'attr') ?>" title="Copiar CBU/CVU">
                                                    Copiar
                                                </button>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($m['banco'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($m['activo']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($m['favorito']): ?>
                                                <span class="text-warning" title="Favorito">&starf;</span>
                                            <?php else: ?>
                                                <form action="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/favorito') ?>" method="post" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Marcar como favorito">&star;</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                                <form action="<?= base_url('mis-medios-de-cobro/' . $m['id'] . '/toggle') ?>" method="post" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm <?= $m['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                                        <?= $m['activo'] ? 'Desactivar' : 'Activar' ?>
                                                    </button>
                                                </form>
                                                <form action="<?= base_url('mis-medios-de-cobro/' . $m['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar medio de cobro?')">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.copiar-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var texto = this.getAttribute('data-copiar');
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(texto).then(function() {
                        var textoOriginal = this.textContent;
                        this.textContent = 'Copiado!';
                        var self = this;
                        setTimeout(function() {
                            self.textContent = textoOriginal;
                        }, 2000);
                    }.bind(this)).catch(function() {
                        fallbackCopiar(texto, this);
                    }.bind(this));
                } else {
                    fallbackCopiar(texto, this);
                }
            });
        });

        function fallbackCopiar(texto, btn) {
            var textarea = document.createElement('textarea');
            textarea.value = texto;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                var textoOriginal = btn.textContent;
                btn.textContent = 'Copiado!';
                setTimeout(function() {
                    btn.textContent = textoOriginal;
                }, 2000);
            } catch (e) {
                alert('No se pudo copiar al portapapeles. Seleccioná y copiá manualmente.');
            }
            document.body.removeChild(textarea);
        }
    </script>

<?= view('partials/_footer') ?>
