<div
  class="c-toc-root {{ $hideOnMobile ? 'c-toc-root--hide-mobile' : '' }} {{ $hideOnDesktop ? 'c-toc-root--hide-desktop' : '' }}"
  data-toc-root="{{ $ID }}" data-mobile-style="{{ $mobileStyle }}">
  @if ($placeInCard)
    @card()
      <div class="c-card__header c-toc__heading">
        @typography([
            'element' => 'h2',
            'variant' => 'h2',
            'id' => 'toc-title-' . $ID
        ])
          {{ $title }}
        @endtypography
      </div>

      <div class="c-card__body">
        <button class="c-toc__toggle" type="button" aria-expanded="false" aria-controls="toc-panel-{{ $ID }}"
          data-toc-toggle="{{ $ID }}" hidden>
          <span class="c-toc__toggle-label">{{ $title }}</span>
          <svg class="c-toc__toggle-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
            focusable="false">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>

        <div id="toc-panel-{{ $ID }}" class="c-toc__panel" data-toc-panel="{{ $ID }}">
          <nav id="{{ $ID }}" class="c-toc" aria-label="{{ __('Table of Contents', 'modularity-toc') }}">
            <ul class="c-toc__list {{ $slidingTrack ? 'c-toc__list--track' : '' }}"></ul>
          </nav>
        </div>
      </div>
    @endcard
  @else
    <div class="c-toc__heading">
      @typography([
          'element' => 'h2',
          'variant' => 'h2',
          'id' => 'toc-title-' . $ID
      ])
        {{ $title }}
      @endtypography
    </div>

    <button class="c-toc__toggle" type="button" aria-expanded="false" aria-controls="toc-panel-{{ $ID }}"
      data-toc-toggle="{{ $ID }}" hidden>
      <span class="c-toc__toggle-label">{{ $title }}</span>
      <svg class="c-toc__toggle-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </button>

    <div id="toc-panel-{{ $ID }}" class="c-toc__panel" data-toc-panel="{{ $ID }}">
      <nav id="{{ $ID }}" class="c-toc" aria-label="{{ __('Table of Contents', 'modularity-toc') }}">
        <ul class="c-toc__list {{ $slidingTrack ? 'c-toc__list--track' : '' }}"></ul>
      </nav>
    </div>
  @endif
</div>

<script type="application/json" data-toc-config="{{ $ID }}">
{
    "id": "{{ $ID }}",
    "sidebars": @json($sidebars),
    "headingLevels": @json($headingLevels),
    "ignoreCardSubHeaders": @json($ignoreCardSubHeaders),
    "mobileStyle": @json($mobileStyle),
    "sidebarSelectorMap": @json($sidebarSelectorMap),
    "mobileBreakpoint": @json($mobileBreakpoint)
}
</script>

@if ($automaticMobileInsertion)
  <div class="c-toc-root c-toc-root--hide-desktop" data-toc-root="{{ $mobileID }}"
    data-mobile-style="{{ $mobileStyle }}" data-toc-insertion-selector="{{ $mobileCssSelector }}"
    data-toc-insertion-method="{{ $insertionMethod }}">
    @if ($placeInCard)
      @card()
        <div class="c-card__header c-toc__heading">
          @typography([
              'element' => 'h2',
              'variant' => 'h2',
              'id' => 'toc-title-' . $mobileID
          ])
            {{ $title }}
          @endtypography
        </div>

        <div class="c-card__body">
          <button class="c-toc__toggle" type="button" aria-expanded="false"
            aria-controls="toc-panel-{{ $mobileID }}" data-toc-toggle="{{ $mobileID }}" hidden>
            <span class="c-toc__toggle-label">{{ $title }}</span>
            <svg class="c-toc__toggle-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
              focusable="false">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>

          <div id="toc-panel-{{ $mobileID }}" class="c-toc__panel" data-toc-panel="{{ $mobileID }}">
            <nav id="{{ $mobileID }}" class="c-toc" aria-label="{{ __('Table of Contents', 'modularity-toc') }}">
              <ul class="c-toc__list {{ $slidingTrack ? 'c-toc__list--track' : '' }}"></ul>
            </nav>
          </div>
        </div>
      @endcard
    @else
      <div class="c-toc__heading">
        @typography([
            'element' => 'h2',
            'variant' => 'h2',
            'id' => 'toc-title-' . $mobileID
        ])
          {{ $title }}
        @endtypography
      </div>

      <button class="c-toc__toggle" type="button" aria-expanded="false" aria-controls="toc-panel-{{ $mobileID }}"
        data-toc-toggle="{{ $mobileID }}" hidden>
        <span class="c-toc__toggle-label">{{ $title }}</span>
        <svg class="c-toc__toggle-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
          viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
          focusable="false">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>

      <div id="toc-panel-{{ $mobileID }}" class="c-toc__panel" data-toc-panel="{{ $mobileID }}">
        <nav id="{{ $mobileID }}" class="c-toc" aria-label="{{ __('Table of Contents', 'modularity-toc') }}">
          <ul class="c-toc__list {{ $slidingTrack ? 'c-toc__list--track' : '' }}"></ul>
        </nav>
      </div>
    @endif
  </div>

  <script type="application/json" data-toc-config="{{ $mobileID }}">
{
    "id": "{{ $mobileID }}",
    "sidebars": @json($sidebars),
    "headingLevels": @json($headingLevels),
    "ignoreCardSubHeaders": @json($ignoreCardSubHeaders),
    "mobileStyle": @json($mobileStyle),
    "sidebarSelectorMap": @json($sidebarSelectorMap),
    "mobileBreakpoint": @json($mobileBreakpoint)
}
</script>
@endif
