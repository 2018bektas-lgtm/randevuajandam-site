@php
    // jQuery + Select2: yalnızca gerçekten select kullanan sayfalar
    $needsSelect2 = request()->routeIs([
        'frontend.hekimler',
        'frontend.hasta.profil',
        'frontend.hasta.randevular',
        'frontend.hekim.kayit',
        'frontend.paketler',
        'frontend.hekim.paket_ode',
        'frontend.hekim.paket_sec',
        'frontend.hekim.klinik.*',
    ]) || trim($__env->yieldContent('load_select2', '')) === '1';
@endphp

@if($needsSelect2)
<!-- jQuery (Select2 dependency) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js" defer></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery === 'undefined' || !jQuery.fn.select2) return;
    var $ = jQuery;
    $('select').each(function () {
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
                noResults: function () { return 'Sonuç bulunamadı'; },
                searching: function () { return 'Aranıyor...'; }
            },
            placeholder: $(this).attr('placeholder') || $(this).find('option:first').text() || 'Seçiniz...',
            allowClear: $(this).prop('required') ? false : true
        });
    });
});
</script>
@endif

@yield('script')
@stack('scripts')
