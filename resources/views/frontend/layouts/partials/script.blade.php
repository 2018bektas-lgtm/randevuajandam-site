<!-- jQuery (Select2 dependency) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Global Select2 Initialization -->
<script>
    $(document).ready(function() {
        $('select').each(function() {
            // Skip if it's already initialized, marked as no-select2, or needs custom initialization (like modals or specific IDs)
            if ($(this).data('select2') || 
                $(this).hasClass('select2-hidden-accessible') || 
                $(this).hasClass('no-select2') || 
                $(this).data('no-select2') ||
                $(this).hasClass('select2-modal') ||
                $(this).hasClass('select2-filter') ||
                $(this).hasClass('select2-hasta-filter') ||
                ['il', 'ilce', 'formDanisanSelect', 'formHizmetSelect'].indexOf($(this).attr('id')) !== -1 ||
                $(this).closest('.modal, [id*="Modal"], [class*="modal"]').length > 0) {
                return;
            }

            $(this).select2({
                width: '100%',
                language: {
                    noResults: function() { return 'Sonuç bulunamadı'; },
                    searching: function() { return 'Aranıyor...'; }
                },
                placeholder: $(this).attr('placeholder') || $(this).find('option:first').text() || 'Seçiniz...',
                allowClear: $(this).prop('required') ? false : true
            });
        });
    });
</script>

@yield('script')
