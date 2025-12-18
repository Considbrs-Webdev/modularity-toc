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

<script type="application/json" data-toc-config="{{ $ID }}">
{
    "id": "{{ $ID }}",
    "sidebars": @json($sidebars),
    "headingLevels": @json($headingLevels),
    "ignoreCardSubHeaders": @json($ignoreCardSubHeaders)
}
</script>
