{{-- Mobile drawer handle (only visible below 78em) --}}
<button class="c-toc-mobile-handle" aria-label="{{ __('Open table of contents', 'modularity-toc') }}"
    data-toc-toggle="{{ $ID }}">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
</button>

{{-- Overlay for mobile drawer --}}
<div class="c-toc-overlay" data-toc-overlay="{{ $ID }}"></div>

{{-- TOC drawer wrapper --}}
<div class="c-toc-drawer" data-toc-drawer="{{ $ID }}">
    {{-- Close button for mobile --}}
    <button class="c-toc-close" aria-label="{{ __('Close table of contents', 'modularity-toc') }}"
        data-toc-close="{{ $ID }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    @if ($placeInCard)
        @card()
            <div class="c-card__header">
                @typography([
                    'element' => 'h2',
                    'variant' => 'h2'
                ])
                    {{ $title }}
                @endtypography
            </div>

            <div class="c-card__body">
                <nav id="{{ $ID }}" class="c-toc" aria-label="{{ __('Table of Contents', 'modularity-toc') }}">
                    <ul class="c-toc__list"></ul>
                </nav>
            </div>
        @endcard
    @else
        @typography([
            'element' => 'h2',
            'variant' => 'h2'
        ])
            {{ $title }}
        @endtypography
        <nav id="{{ $ID }}" class="c-toc" aria-label="{{ __('Table of Contents', 'modularity-toc') }}">
            <ul class="c-toc__list"></ul>
        </nav>
    @endif
</div>

<script type="application/json" data-toc-config="{{ $ID }}">
{
    "id": "{{ $ID }}",
    "sidebars": @json($sidebars),
    "headingLevels": @json($headingLevels),
    "ignoreCardSubHeaders": @json($ignoreCardSubHeaders)
}
</script>
