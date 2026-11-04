<?php

namespace ModularityToc\Helper;

class PagePurge
{
    /**
     * Ask page caches (LiteSpeed, Varnish) to purge the given posts.
     *
     * - LiteSpeed Cache: purged through its public `litespeed_purge_post` action.
     * - Varnish: a non-blocking PURGE request to the permalink, like Modularity's own CachePurge.
     *
     * Fires `ModularityToc/PagePurge/Purged` afterwards so other cache layers can hook in.
     *
     * @param int[] $postIds
     * @return void
     */
    public static function purge(array $postIds): void
    {
        $postIds = array_values(array_unique(array_filter(array_map('intval', $postIds))));

        foreach ($postIds as $postId) {
            do_action('litespeed_purge_post', $postId);

            $url = get_permalink($postId);

            if (!empty($url)) {
                wp_remote_request($url, array(
                    'method'      => 'PURGE',
                    'timeout'     => 2,
                    'redirection' => 0,
                    'blocking'    => false,
                ));
            }
        }

        /**
         * Fires after page cache purge requests have been sent for pages using the TOC.
         *
         * @param int[] $postIds
         */
        do_action('ModularityToc/PagePurge/Purged', $postIds);
    }
}
