<?php

namespace ModularityToc\AcfFields;

/**
 * Constants for the built-in sidebar keys used by the TOC module.
 *
 * These string values are persisted by ACF in post_meta, so they must
 * remain stable. They also correspond to the keys in the JS
 * `TableOfContents.SIDEBAR_SELECTOR_MAP` static property.
 *
 * Third-party code that registers additional choices via the
 * `Modularity/Module/TableOfContents/SidebarChoices` filter can define
 * its own constants following the same pattern.
 */
class SidebarKey
{
    public const CONTENT_AREA_TOP    = 'content-area-top';
    public const CONTENT_AREA        = 'content-area';
    public const CONTENT_AREA_BOTTOM = 'content-area-bottom';
}
