document.addEventListener('DOMContentLoaded', function () {
    const searchBars = document.querySelectorAll('.global-search');

    searchBars.forEach(function (searchBar) {
        const input = searchBar.querySelector('input[name="q"]');
        const results = searchBar.querySelector('.search-results');
        let timer;

        function render(payload) {
            if (!payload.results.length && !payload.error) {
                results.innerHTML = '<div class="search-empty">No matching solutions</div>';
            } else if (payload.error) {
                results.innerHTML = '<div class="search-empty">Search is temporarily unavailable.</div>';
            } else {
                results.innerHTML = payload.results.map(function (product) {
                    return '<a class="search-result" href="order.php?product_id=' + encodeURIComponent(product.id) + '">' +
                        '<span><strong>' + escapeHtml(product.name) + '</strong><small>' + escapeHtml(product.category) + ' · ' + escapeHtml(product.sku) + '</small></span>' +
                        '<b>₹' + Number(product.price).toLocaleString('en-IN') + '</b></a>';
                }).join('');
            }
            results.hidden = false;
        }

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (character) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
            });
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const query = input.value.trim();
            if (!query) {
                results.hidden = true;
                results.innerHTML = '';
                return;
            }
            timer = setTimeout(function () {
                fetch('search_api.php?q=' + encodeURIComponent(query), {headers: {'Accept': 'application/json'}})
                    .then(function (response) { return response.json(); })
                    .then(render)
                    .catch(function () { render({results: [], error: true}); });
            }, 180);
        });

        searchBar.addEventListener('submit', function (event) {
            event.preventDefault();
            input.dispatchEvent(new Event('input'));
        });

        input.addEventListener('focus', function () {
            if (input.value.trim() && results.innerHTML) {
                results.hidden = false;
            }
        });

        document.addEventListener('click', function (event) {
            if (!searchBar.contains(event.target)) {
                results.hidden = true;
            }
        });
    });
});
