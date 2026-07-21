{{-- FAQPage JSON-LD — $faqs: list of [q,a] --}}
@if(!empty($faqs) && is_iterable($faqs))
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faqs)->map(fn ($f) => [
        '@type' => 'Question',
        'name' => is_array($f) ? ($f['q'] ?? $f['question'] ?? '') : '',
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => is_array($f) ? ($f['a'] ?? $f['answer'] ?? '') : '',
        ],
    ])->filter(fn ($e) => ($e['name'] ?? '') !== '')->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endif
