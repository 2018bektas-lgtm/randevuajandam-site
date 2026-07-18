@php
    $tr = $siteAyari ?? \App\Models\SiteAyari::cached();
    $gtm = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->gtm_container_id ?? ''));
@endphp
@if($gtm !== '')
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif
