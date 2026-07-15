<script>
(function () {
    document.querySelectorAll('[data-group-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-group-toggle');
            const panel = document.querySelector('[data-group-panel="' + id + '"]');
            const isOpen = panel?.classList.contains('is-open');

            document.querySelectorAll('[data-group-panel]').forEach((p) => p.classList.remove('is-open'));
            document.querySelectorAll('[data-group-toggle]').forEach((b) => {
                b.classList.remove('is-open');
                b.setAttribute('aria-expanded', 'false');
            });

            if (!isOpen && panel) {
                panel.classList.add('is-open');
                btn.classList.add('is-open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });
})();
</script>
