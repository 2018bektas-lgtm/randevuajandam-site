{{--
  Unified YSB nav renderer (yonetim style).
  Expected vars:
    $ysbDash: href, match, title, sub?, icon?
    $ysbGroups: [ id, label, icon, items[ href, match, label, locked?, badge?, badge_html? ] ]
    $ysbExtraHtml?: string
    $ysbSectionLabel?: string
--}}
@php
    $ysbDash = $ysbDash ?? null;
    $ysbGroups = $ysbGroups ?? [];
    $ysbSectionLabel = $ysbSectionLabel ?? 'Menu';
    $defaultDashIcon = 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z';
@endphp

<nav class="ysb-nav" aria-label="Panel menu">
    @if($ysbDash)
        @php
            $dashMatch = $ysbDash['match'];
            $dashActive = is_array($dashMatch)
                ? request()->routeIs(...$dashMatch)
                : request()->routeIs($dashMatch);
        @endphp
        <a href="{{ $ysbDash['href'] }}" class="ysb-dash {{ $dashActive ? 'is-active' : '' }}">
            <span class="ysb-dash-icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ysbDash['icon'] ?? $defaultDashIcon }}"/>
                </svg>
            </span>
            <span class="ysb-dash-text">
                <span class="ysb-dash-title">{{ $ysbDash['title'] }}</span>
                @if(!empty($ysbDash['sub']))
                    <span class="ysb-dash-sub">{{ $ysbDash['sub'] }}</span>
                @endif
            </span>
        </a>
    @endif

    <div class="ysb-section-label">{{ $ysbSectionLabel }}</div>

    @foreach($ysbGroups as $gi => $group)
        @if(empty($group['items']))
            @continue
        @endif
        @php
            $groupActive = collect($group['items'])->contains(function ($i) {
                $m = $i['match'] ?? '';
                return is_array($m) ? request()->routeIs(...$m) : request()->routeIs($m);
            });
        @endphp
        <div class="ysb-group {{ $groupActive ? 'is-open is-active' : '' }}"
             data-ysb-group="{{ $group['id'] }}"
             style="--ysb-i: {{ $gi }}">
            <button type="button"
                    class="ysb-group-btn"
                    data-ysb-toggle="{{ $group['id'] }}"
                    aria-expanded="{{ $groupActive ? 'true' : 'false' }}">
                <span class="ysb-group-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $group['icon'] }}"/>
                    </svg>
                </span>
                <span class="ysb-group-label">{{ $group['label'] }}</span>
                <span class="ysb-group-meta">
                    <span class="ysb-count">{{ count($group['items']) }}</span>
                    <svg class="ysb-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </span>
            </button>

            <div class="ysb-panel" data-ysb-panel="{{ $group['id'] }}">
                <div class="ysb-panel-inner">
                    <ul class="ysb-list">
                        @foreach($group['items'] as $item)
                            @php
                                $m = $item['match'] ?? '';
                                $active = is_array($m) ? request()->routeIs(...$m) : request()->routeIs($m);
                            @endphp
                            <li>
                                <a href="{{ $item['href'] }}"
                                   class="ysb-link {{ $active ? 'is-active' : '' }} {{ !empty($item['locked']) ? 'opacity-80' : '' }}"
                                   @if(!empty($item['title'])) title="{{ $item['title'] }}" @endif>
                                    <span class="ysb-dot"></span>
                                    <span class="ysb-link-text">{{ $item['label'] }}</span>
                                    @if(!empty($item['locked']))
                                        <span class="ysb-badge">Yukselt</span>
                                    @elseif(!empty($item['badge']))
                                        <span class="ysb-badge">{{ $item['badge'] }}</span>
                                    @elseif(!empty($item['badge_html']))
                                        {!! $item['badge_html'] !!}
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endforeach

    @if(!empty($ysbExtraHtml))
        <div style="margin-top:.75rem">{!! $ysbExtraHtml !!}</div>
    @endif
</nav>

<script>
(function () {
    if (window.__ysbNavBound) return;
    window.__ysbNavBound = true;

    function closeGroup(group) {
        if (!group) return;
        group.classList.remove('is-open');
        const btn = group.querySelector('[data-ysb-toggle]');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }
    function openGroup(group) {
        if (!group) return;
        group.classList.add('is-open');
        const btn = group.querySelector('[data-ysb-toggle]');
        if (btn) btn.setAttribute('aria-expanded', 'true');
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-ysb-toggle]');
        if (!btn) return;
        const id = btn.getAttribute('data-ysb-toggle');
        const group = document.querySelector('[data-ysb-group="' + id + '"]');
        if (!group) return;
        const wasOpen = group.classList.contains('is-open');
        document.querySelectorAll('[data-ysb-group].is-open').forEach(function (g) {
            if (g !== group) closeGroup(g);
        });
        if (wasOpen) closeGroup(group); else openGroup(group);
    });
})();
</script>
