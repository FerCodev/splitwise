<?= view('partials/_head', ['title' => 'Gastito - Mis medios de cobro']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Medios de cobro']) ?>

    <?php $paymentMethodVariant = \App\Services\UiComponentResolver::variant('mis_medios_cobro', 'payment_method_card'); ?>
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
                        <?= view('components/cards/medio_cobro', [
                            'variant' => $paymentMethodVariant,
                            'nombre' => $m['nombre'] ?? $m['tipo'],
                            'activo' => (bool) $m['activo'],
                            'favorito' => (bool) $m['favorito'],
                            'titular' => $m['titular'] ?? null,
                            'alias' => $m['alias'] ?? null,
                            'cbu' => $m['cbu_cvu'] ?? null,
                            'editUrl' => base_url('mis-medios-de-cobro/' . $m['id'] . '/editar'),
                            'favoritoUrl' => base_url('mis-medios-de-cobro/' . $m['id'] . '/favorito'),
                        ]) ?>
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
