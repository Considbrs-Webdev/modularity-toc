<?php

namespace ModularityToc;

class TableOfContents extends \Modularity\Module
{
    public $slug = 'toc';
    public $icon = 'background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48cGF0aCBkPSJNMTQuNTI5LDE3Ljk3YzAtMi41NzEtMS42NDctNC43NTctMy45NDEtNS41NjlWOC4xMTcgICAgYzAtMS4wODktMC44ODMtMS45NzEtMS45Ny0xLjk3MWMtMS4wODcsMC0xLjk3MSwwLjg4MS0xLjk3MSwxLjk3MXY0LjI4NGMtMi4yOTQsMC44MTItMy45NDEsMi45OTgtMy45NDEsNS41NjkgICAgYzAsMi41NzEsMS42NDcsNC43NTcsMy45NDEsNS41Njl2MjAuMzkyYy0yLjI5NCwwLjgxMi0zLjk0MSwyLjk5OC0zLjk0MSw1LjU2OXMxLjY0Nyw0Ljc1NywzLjk0MSw1LjU2OXYyMC4zOTIgICAgYy0yLjI5NCwwLjgxMi0zLjk0MSwyLjk5OC0zLjk0MSw1LjU2OXMxLjY0Nyw0Ljc1NywzLjk0MSw1LjU2OXY0LjI4M2MwLDEuMDksMC44ODMsMS45NzEsMS45NzEsMS45NzEgICAgYzEuMDg3LDAsMS45Ny0wLjg4MSwxLjk3LTEuOTcxVjg2LjZjMi4yOTQtMC44MTIsMy45NDEtMi45OTgsMy45NDEtNS41NjlzLTEuNjQ3LTQuNzU4LTMuOTQxLTUuNTY5VjU1LjA2OSAgICBjMi4yOTQtMC44MTIsMy45NDEtMi45OTgsMy45NDEtNS41NjlzLTEuNjQ3LTQuNzU3LTMuOTQxLTUuNTY5VjIzLjUzOUMxMi44ODEsMjIuNzI4LDE0LjUyOSwyMC41NDEsMTQuNTI5LDE3Ljk3eiBNOTMuMzU0LDEwLjA4NyAgICBIMzYuMjA2YzAsMC0yLjY0LTAuMjM1LTMuNDQ5LDAuNTdMMjQuOTQsMTYuNDhjLTAuODA4LDAuODA1LTAuODA4LDIuMTA5LDAsMi45MTRsNy44MTcsNS44MjMgICAgYzAuODA5LDAuODA1LDMuNDQ5LDAuNjM1LDMuNDQ5LDAuNjM1aDU3LjE0OGMyLjE3OSwwLDMuOTQxLTEuNzYzLDMuOTQxLTMuOTQxdi03Ljg4MkM5Ny4yOTUsMTEuODUxLDk1LjUzMiwxMC4wODcsOTMuMzU0LDEwLjA4N3ogICAgIE05My4zNTQsNDEuNjE4SDM2LjIwNmMwLDAtMi42NC0wLjIzNS0zLjQ0OSwwLjU2OWwtNy44MTcsNS44MjRjLTAuODA4LDAuODA0LTAuODA4LDIuMTA5LDAsMi45MTNsNy44MTcsNS44MjMgICAgYzAuODA5LDAuODA1LDMuNDQ5LDAuNjM2LDMuNDQ5LDAuNjM2aDU3LjE0OGMyLjE3OSwwLDMuOTQxLTEuNzYzLDMuOTQxLTMuOTQxdi03Ljg4M0M5Ny4yOTUsNDMuMzgsOTUuNTMyLDQxLjYxOCw5My4zNTQsNDEuNjE4eiAgICAgTTkzLjM1NCw3My4xNDdIMzYuMjA2YzAsMC0yLjY0LTAuMjM0LTMuNDQ5LDAuNTY5TDI0Ljk0LDc5LjU0Yy0wLjgwOCwwLjgwNS0wLjgwOCwyLjEwOSwwLDIuOTE0bDcuODE3LDUuODIzICAgIGMwLjgwOSwwLjgwNSwzLjQ0OSwwLjYzNSwzLjQ0OSwwLjYzNWg1Ny4xNDhjMi4xNzksMCwzLjk0MS0xLjc2MywzLjk0MS0zLjk0MXYtNy44ODJDOTcuMjk1LDc0LjkxLDk1LjUzMiw3My4xNDcsOTMuMzU0LDczLjE0N3oiLz48L3N2Zz4=);';
    public $supports = array();
    public $isBlockCompatible = true;

    public function init()
    {
        $this->nameSingular = __('Table of Contents', 'modularity-toc');
        $this->namePlural = __('Table of Contents', 'modularity-toc');
        $this->description = __('Display table of contents', 'modularity-toc');

        add_filter('Modularity/Block/acf/toc/Data', [$this, 'useBlockTitle'], 10, 3);

        add_filter('body_class', function ($classes) {
            $sticky_toc_column = get_field('sticky_list', 'modularity-toc-settings');

            if ($sticky_toc_column) {
                $classes[] = 'modularity-toc--sticky-sidebar';
            }

            return $classes;
        });
    }

    public function data(): array
    {
        $fields = $this->getFields();
        $slidingTrack = get_field('sliding_track', 'modularity-toc-settings');
        $mobileStyle = get_field('mobile_style', 'modularity-toc-settings');
        $title = !empty($this->data['post_title']) && is_string($this->data['post_title'])
            ? $this->data['post_title']
            : __('Find on page', 'municipio');

        $resolvedMobileStyle = in_array($mobileStyle, ['dropdown', 'expanded'], true)
            ? $mobileStyle
            : 'dropdown';

        /**
         * Filter the sidebar selector map passed to the JS config.
         *
         * Add custom entries as 'key' => '#css-selector' pairs, where
         * the key matches a choice registered via `Modularity/Module/TableOfContents/SidebarChoices`.
         *
         * @param array $sidebarSelectorMap Associative array of sidebar key => CSS selector.
         */
        $sidebarSelectorMap = apply_filters('Modularity/Module/TableOfContents/SidebarSelectorMap', []);

        $data = [
            'ID' => uniqid('toc-'),
            'title' => $title,
            'sidebars' => !empty($fields['sidebars']) ? $fields['sidebars'] : [],
            'headingLevels' => !empty($fields['heading_levels']) ? $fields['heading_levels'] : ['h2'],
            'placeInCard' => !empty($fields['place_in_card']) ? $fields['place_in_card'] : false,
            'ignoreCardSubHeaders' => !empty($fields['ignore_card_sub_headers']) ? $fields['ignore_card_sub_headers'] : false,
            'hideOnMobile' => !empty($fields['hide_on_mobile']),
            'hideOnDesktop' => !empty($fields['hide_on_desktop']),
            'mobileStyle' => $resolvedMobileStyle,
            'slidingTrack' => is_bool($slidingTrack) ? $slidingTrack : true,
            'sidebarSelectorMap' => $sidebarSelectorMap,
        ];

        return $data;
    }

    public function useBlockTitle(array $viewData): array
    {
        if (!empty($viewData['postTitle']) && is_string($viewData['postTitle'])) {
            $viewData['title'] = $viewData['postTitle'];
        }

        return $viewData;
    }

    /**
     * Blade Template
     * @return string
     */
    public function template(): string
    {
        return 'toc.blade.php';
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization (if you must, use __construct with care, this will probably break stuff!!)
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}
