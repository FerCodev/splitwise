</div><!-- /desktop-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function ensureContainer() {
        var container = document.querySelector('[data-gastito-feedback-live]');
        if (container) return container;

        container = document.createElement('div');
        container.className = 'gastito-feedback-live';
        container.setAttribute('data-gastito-feedback-live', '1');
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'false');
        document.body.appendChild(container);
        return container;
    }

    function showFeedback(type, message, options) {
        var opts = options || {};
        var alertType = ['success', 'error', 'warning', 'info'].indexOf(type) >= 0 ? type : 'info';
        var titles = {
            success: 'Listo',
            error: 'No se pudo completar',
            warning: 'Atención',
            info: 'Información'
        };
        var classes = {
            success: 'tabler-alert-success',
            error: 'tabler-alert-danger',
            warning: 'tabler-alert-warning',
            info: 'tabler-alert-info'
        };
        var icons = {
            success: '&check;',
            error: '!',
            warning: '!',
            info: 'i'
        };
        var duration = Number(opts.duration || (alertType === 'error' ? 12000 : 6000));
        var alert = document.createElement('div');
        alert.className = 'tabler-alert-card ' + classes[alertType] + ' app-feedback';
        alert.setAttribute('role', alertType === 'error' ? 'alert' : 'status');
        alert.style.setProperty('--feedback-duration', duration + 'ms');
        alert.innerHTML = '<span class="app-feedback-timer" aria-hidden="true"></span>' +
            '<span class="tabler-alert-icon" aria-hidden="true">' + icons[alertType] + '</span>' +
            '<div><strong>' + escapeHtml(opts.title || titles[alertType]) + '</strong><small>' + escapeHtml(message) + '</small></div>';

        ensureContainer().appendChild(alert);

        var timer = alert.querySelector('.app-feedback-timer');
        if (timer) {
            timer.addEventListener('animationend', function() {
                alert.classList.add('app-feedback-exit');
                window.setTimeout(function() { alert.remove(); }, 220);
            }, { once: true });
        }
    }

    window.GastitoFeedback = {
        show: showFeedback
    };
})();
</script>
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

    function formatTypingValue(value) {
        var clean = String(value || '').replace(/[^\d,]/g, '');
        var pieces = clean.split(',');
        var integer = pieces[0].replace(/\D/g, '');
        var decimals = pieces.length > 1 ? pieces.slice(1).join('').replace(/\D/g, '').slice(0, 2) : '';
        var formatted = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        if (pieces.length > 1) {
            formatted += ',' + decimals;
        }
        return formatted;
    }

    function formatMoneyInput(input) {
        if (!input) return;
        var normalized = normalizeMoneyInput(input.value);
        if (normalized === '' || normalized === '-' || normalized === '.') return;
        input.value = formatNumber(normalized, 2);
    }

    function parseMoney(value) {
        return Number.parseFloat(normalizeMoneyInput(value));
    }

    function syncHidden(input, options) {
        if (!input) return;
        var opts = options || {};
        var hiddenId = input.getAttribute('data-money-hidden');
        var hidden = hiddenId ? document.getElementById(hiddenId) : null;
        var number = parseMoney(input.value);
        var max = Number.parseFloat(input.getAttribute('data-money-max') || '');
        var exceeded = Number.isFinite(number) && Number.isFinite(max) && number > max;

        if (exceeded) {
            number = max;
            input.value = formatNumber(max, 2);
            input.classList.add('is-invalid');
            if (opts.notify !== false && window.GastitoFeedback) {
                window.GastitoFeedback.show('warning', input.getAttribute('data-money-max-message') || 'El monto no puede superar el máximo permitido.');
            }
        } else {
            input.classList.remove('is-invalid');
        }

        if (hidden) {
            hidden.value = Number.isFinite(number) ? number.toFixed(2) : '';
        }
    }

    function updateInputWhileTyping(input) {
        if (!input) return;
        var formatted = formatTypingValue(input.value);
        if (input.value !== formatted) {
            input.value = formatted;
        }
        syncHidden(input);
    }

    function fillMoneyInput(button) {
        var inputId = button.getAttribute('data-money-target');
        var input = inputId ? document.getElementById(inputId) : null;
        var hiddenId = button.getAttribute('data-money-hidden');
        var hidden = hiddenId ? document.getElementById(hiddenId) : null;
        var value = button.getAttribute('data-money-value') || '0';
        var number = Number.parseFloat(value);

        if (!Number.isFinite(number)) {
            number = 0;
        }

        if (input) {
            input.value = formatNumber(number, 2);
            input.classList.remove('is-invalid');
        }
        if (hidden) {
            hidden.value = number.toFixed(2);
        }
    }

    function bindMoneyInputs(root) {
        var scope = root || document;
        scope.querySelectorAll('input[data-money-input], input[name="monto_visual"]').forEach(function(input) {
            if (input.dataset.moneyBound === '1') return;
            input.dataset.moneyBound = '1';
            input.addEventListener('blur', function() {
                formatMoneyInput(input);
                syncHidden(input, { notify: false });
            });
        });
    }

    window.SplitWiseMoney = {
        normalize: normalizeMoneyInput,
        formatNumber: formatNumber,
        formatCurrency: formatCurrency,
        formatTyping: formatTypingValue,
        formatInput: formatMoneyInput,
        syncHidden: syncHidden,
        bindInputs: bindMoneyInputs
    };

    document.addEventListener('input', function(event) {
        var input = event.target.closest('input[data-money-input]');
        if (input) {
            updateInputWhileTyping(input);
        }
    });

    document.addEventListener('submit', function(event) {
        event.target.querySelectorAll('input[data-money-input][data-money-hidden]').forEach(function(input) {
            syncHidden(input);
        });
    }, true);

    document.addEventListener('click', function(event) {
        var button = event.target.closest('[data-money-fill]');
        if (button) {
            fillMoneyInput(button);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        bindMoneyInputs(document);
    });
})();
</script>
</body>
</html>
