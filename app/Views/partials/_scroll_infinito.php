<div id="scroll-sentinel" style="height:1px;"></div>
<div id="scroll-loader" class="text-center py-3 d-none">
    <div class="spinner-border spinner-border-sm text-muted" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>
<div id="scroll-end" class="text-center py-3 d-none">
    <span class="small text-muted">No hay m&aacute;s resultados.</span>
</div>

<script>
(function() {
    var container = document.getElementById('scroll-container');
    var sentinel = document.getElementById('scroll-sentinel');
    var loader = document.getElementById('scroll-loader');
    var endMsg = document.getElementById('scroll-end');
    if (!container || !sentinel) return;

    var page = 1;
    var loading = false;
    var ended = false;
    var baseUrl = window.location.href.replace(/[?&]page=\d+/, '').replace(/[?&]partial=1/, '');

    function buildUrl(p) {
        var sep = baseUrl.indexOf('?') === -1 ? '?' : '&';
        return baseUrl + sep + 'page=' + p + '&partial=1';
    }

    function loadNext() {
        if (loading || ended) return;
        loading = true;
        loader.classList.remove('d-none');
        page++;

        fetch(buildUrl(page))
            .then(function(r) { return r.text(); })
            .then(function(html) {
                loader.classList.add('d-none');
                if (!html.trim()) {
                    ended = true;
                    endMsg.classList.remove('d-none');
                    sentinel.remove();
                    return;
                }
                container.insertAdjacentHTML('beforeend', html);
            })
            .catch(function() {
                loader.classList.add('d-none');
                ended = true;
                endMsg.classList.remove('d-none');
                endMsg.innerHTML = '<span class="small text-muted">Error al cargar. <a href="javascript:location.reload()">Reintentar</a></span>';
                sentinel.remove();
            })
            .finally(function() {
                loading = false;
            });
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting) loadNext();
        }, { rootMargin: '200px' });
        observer.observe(sentinel);
    }
})();
</script>
