<?php

namespace ModularityToc\Helper;

class ModuleCache
{
    /**
     * Module post type registered by Modularity ("mod-" + module slug).
     */
    private const POST_TYPE = 'mod-toc';

    /**
     * Delete the Modularity fragment cache entry of every TOC module on the current site.
     *
     * Modularity only clears a module's fragment cache when that module post is saved,
     * so changes to the global settings page need to clear it explicitly.
     * Mirrors the cache group naming in Modularity\Helper\Cache.
     *
     * @return void
     */
    public static function clear()
    {
        $group = 'modules' . (function_exists('is_multisite') && is_multisite() ? '-' . get_current_blog_id() : '');

        $moduleIds = get_posts(array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ));

        foreach ($moduleIds as $moduleId) {
            wp_cache_delete($moduleId, $group);
        }
    }
}
