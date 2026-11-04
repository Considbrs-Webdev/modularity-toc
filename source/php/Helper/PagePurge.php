<?php

namespace ModularityToc\Helper;

class PagePurge
{
    /**
     * Ask LiteSpeed Cache to purge all cached pages.
     *
     * The TOC settings affect every page that uses a TOC, which can be hundreds of pages. A single
     * purge-all is much cheaper than purging each page separately, and settings rarely change.
     * Does nothing when LiteSpeed Cache is not active.
     *
     * Fires `ModularityToc/PagePurge/Purged` afterwards so other cache layers can hook in.
     *
     * @return void
     */
    public static function purgeAll(): void
    {
        do_action('litespeed_purge_all');

        /**
         * Fires after the page cache has been purged because the TOC settings changed.
         */
        do_action('ModularityToc/PagePurge/Purged');
    }
}
