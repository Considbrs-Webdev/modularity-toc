@if ($placeInCard)
    @card()
        <div class="c-card__header">
            @if (!$hideTitle && $postTitle)
                <h2 class="c-card__title">{{ $postTitle }}</h2>
            @endif
        </div>
        <div class="c-card__body">
            <nav id="{{ $ID }}" class="c-toc" aria-label="{{ __('Table of Contents', 'modularity-toc') }}">
                <ul class="c-toc__list"></ul>
            </nav>
        </div>
    @endcard
@else
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
