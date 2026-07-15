{{--
  Beklenen: $navDashboard (href, match, label, icon?), $navGroups[]
  $navGroups item: id, label, icon (path d), items[]: href, match, label, locked?
--}}
@php
    $lockBadge = '<span class="text-[9px] font-extrabold uppercase tracking-wide text-amber-700 bg-amber-50 border border-amber-100 px-1.5 py-0.5 rounded shrink-0">Yükselt</span>';
@endphp
<nav class="flex-1 min-h-0 p-3 space-y-1 overflow-y-auto text-sm">
    @if(!empty($navDashboard))
        @php $dashActive = request()->routeIs($navDashboard['match']); @endphp
        <a href="{{ $navDashboard['href'] }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $dashActive ? 'bg-[#FFF7ED] text-[#C96A2B] font-semibold border-l-4 border-[#C96A2B]' : 'text-[#6B7280] hover:text-[#111827] hover:bg-[#FAFAFA] font-medium' }}">
            <svg class="w-5 h-5 shrink-0 {{ $dashActive ? 'text-[#C96A2B]' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $navDashboard['icon'] ?? 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' }}"/>
            </svg>
            <span class="font-display">{{ $navDashboard['label'] }}</span>
            @if(!empty($navDashboard['badge']))
                <span class="ml-auto">{{ $navDashboard['badge'] }}</span>
            @endif
        </a>
        <div class="pt-2 pb-1 px-2"><div class="h-px bg-slate-100"></div></div>
    @endif

    @foreach($navGroups ?? [] as $group)
        @if(empty($group['items']))
            @continue
        @endif
        @php
            $groupActive = collect($group['items'])->contains(fn ($item) => request()->routeIs($item['match']));
        @endphp
        <div class="nav-group" data-group="{{ $group['id'] }}">
            <button type="button"
                    class="nav-group-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 font-medium text-left {{ $groupActive ? 'has-active is-open' : '' }}"
                    data-group-toggle="{{ $group['id'] }}"
                    aria-expanded="{{ $groupActive ? 'true' : 'false' }}">
                <svg class="w-5 h-5 shrink-0 {{ $groupActive ? 'text-[#C96A2B]' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $group['icon'] }}"/>
                </svg>
                <span class="font-display flex-1 text-[13px] text-left">{{ $group['label'] }}</span>
                <svg class="nav-chevron w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>
            <div class="nav-group-panel ml-3 pl-3 border-l border-slate-100 mt-0.5 mb-1 space-y-0.5 {{ $groupActive ? 'is-open' : '' }}"
                 data-group-panel="{{ $group['id'] }}">
                @foreach($group['items'] as $item)
                    @php $itemActive = request()->routeIs($item['match']); @endphp
                    <a href="{{ $item['href'] }}"
                       class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-[12.5px] transition-all duration-150 {{ $itemActive ? 'nav-child-active' : 'nav-child-idle font-medium' }} {{ !empty($item['locked']) ? 'opacity-80' : '' }}"
                       @if(!empty($item['title'])) title="{{ $item['title'] }}" @endif>
                        <span class="flex items-center gap-2 min-w-0">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $itemActive ? 'bg-[#C96A2B]' : 'bg-slate-300' }}"></span>
                            <span class="font-display truncate">{{ $item['label'] }}</span>
                        </span>
                        @if(!empty($item['locked']))
                            {!! $lockBadge !!}
                        @elseif(!empty($item['badge_html']))
                            {!! $item['badge_html'] !!}
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach

    @if(!empty($navExtraHtml))
        <div class="pt-2 pb-1 px-2"><div class="h-px bg-slate-100"></div></div>
        {!! $navExtraHtml !!}
    @endif
</nav>
