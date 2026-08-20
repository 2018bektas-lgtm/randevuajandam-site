@php
    $navItems = [
        [
            'route'  => 'hekim.finans.index',
            'active' => request()->routeIs('hekim.finans.index'),
            'label'  => 'Genel Bakış',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
        ],
        [
            'route'  => 'hekim.finans.gelirler',
            'active' => request()->routeIs('hekim.finans.gelirler'),
            'label'  => 'Gelirler',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 12a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V12zm-12 0h.008v.008H6V12z"/>',
        ],
        [
            'route'  => 'hekim.finans.giderler',
            'active' => request()->routeIs('hekim.finans.giderler'),
            'label'  => 'Giderler',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>',
        ],
        [
            'route'  => 'hekim.finans.hasta-bakiyeleri',
            'active' => request()->routeIs('hekim.finans.hasta-bakiyeleri') || request()->routeIs('hekim.finans.hasta-hesap'),
            'label'  => 'Hasta Cari',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>',
        ],
        [
            'route'  => 'hekim.finans.kategoriler',
            'active' => request()->routeIs('hekim.finans.kategoriler'),
            'label'  => 'Kategoriler',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z"/>',
        ],
    ];
@endphp

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-3 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
    <nav class="flex items-center gap-1 overflow-x-auto scrollbar-hide -mx-1 px-1" aria-label="Finans navigasyonu">
        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="group inline-flex items-center gap-2 whitespace-nowrap px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-150
                      {{ $item['active']
                        ? 'bg-[#C96A2B] text-white shadow-sm'
                        : 'text-[#4B5563] hover:bg-[#FAFAFA] hover:text-[#111827]' }}">
                <svg class="w-4 h-4 {{ $item['active'] ? 'text-white' : 'text-[#9CA3AF] group-hover:text-[#C96A2B]' }}"
                     fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    {!! $item['icon'] !!}
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    @isset($aksiyon)
        <div class="flex items-center gap-2 md:pr-2">
            {{ $aksiyon }}
        </div>
    @endisset
</div>
