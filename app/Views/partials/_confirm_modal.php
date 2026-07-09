<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Confirmar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="confirmModalMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmModalBtn">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var modal = document.getElementById('confirmModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        if (!btn) return;

        var title = btn.getAttribute('data-confirm-title') || 'Confirmar';
        var msg = btn.getAttribute('data-confirm-msg') || '¿Estás seguro?';
        var confirmText = btn.getAttribute('data-confirm-btn') || 'Confirmar';
        var btnClass = btn.getAttribute('data-confirm-class') || 'btn-danger';
        var formId = btn.getAttribute('data-confirm-form');
        var action = btn.getAttribute('data-confirm-action');

        document.getElementById('confirmModalTitle').textContent = title;
        document.getElementById('confirmModalMessage').textContent = msg;
        var confirmBtn = document.getElementById('confirmModalBtn');
        confirmBtn.textContent = confirmText;
        confirmBtn.className = 'btn ' + btnClass;
        confirmBtn.disabled = false;

        confirmBtn.onclick = function() {
            confirmBtn.disabled = true;
            if (formId) {
                var form = document.getElementById(formId);
                if (form) {
                    form.submit();
                    return;
                }
            }
            if (action) {
                modal.dispatchEvent(new CustomEvent('gastito:confirm-action', {
                    detail: { action: action, trigger: btn }
                }));
                var instance = bootstrap.Modal.getInstance(modal);
                if (instance) instance.hide();
            }
        };
    });

    modal.addEventListener('hidden.bs.modal', function () {
        var confirmBtn = document.getElementById('confirmModalBtn');
        confirmBtn.onclick = null;
        confirmBtn.disabled = false;
    });
})();
</script>
