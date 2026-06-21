</div><!-- /desktop-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(btn) {
    var wrapper = btn.parentElement;
    var input = wrapper.querySelector('input');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-label', show ? 'Ocultar contrase\u00f1a' : 'Mostrar contrase\u00f1a');
    btn.innerHTML = show
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>';
}
</script>
<script>
document.addEventListener('click', function(event) {
    var closeButton = event.target.closest('[data-app-alert-close]');
    if (!closeButton) {
        return;
    }

    var alert = closeButton.closest('.app-alert');
    if (alert) {
        alert.remove();
    }
});
</script>
<script>
(function() {
    function normalizeMoneyInput(value) {
        return String(value || '').replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
    }

    function formatNumber(value, decimals) {
        var number = Number.parseFloat(value);
        var fixedDecimals = Number.isInteger(decimals) ? decimals : 2;
        if (!Number.isFinite(number)) {
            number = 0;
        }
        return number.toLocaleString('es-AR', {
            minimumFractionDigits: fixedDecimals,
            maximumFractionDigits: fixedDecimals
        });
    }

    function formatCurrency(value, options) {
        var opts = options || {};
        var prefix = opts.symbol === false ? '' : '$';
        return prefix + formatNumber(value, opts.decimals);
    }

    function formatMoneyInput(input) {
        if (!input) return;
        var normalized = normalizeMoneyInput(input.value);
        if (normalized === '' || normalized === '-' || normalized === '.') return;
        input.value = formatNumber(normalized, 2);
    }

    function bindMoneyInputs(root) {
        var scope = root || document;
        scope.querySelectorAll('input[data-money-input], input[name="monto_visual"]').forEach(function(input) {
            if (input.dataset.moneyBound === '1') return;
            input.dataset.moneyBound = '1';
            input.addEventListener('blur', function() { formatMoneyInput(input); });
        });
    }

    window.SplitWiseMoney = {
        normalize: normalizeMoneyInput,
        formatNumber: formatNumber,
        formatCurrency: formatCurrency,
        formatInput: formatMoneyInput,
        bindInputs: bindMoneyInputs
    };

    document.addEventListener('DOMContentLoaded', function() {
        bindMoneyInputs(document);
    });
})();
</script>
</body>
</html>
