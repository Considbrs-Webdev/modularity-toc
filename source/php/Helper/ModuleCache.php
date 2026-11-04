<?php

namespace ModularityToc\Helper;

class ModuleCache
{
    /**
     * Module post type registered by Modularity ("mod-" + module slug).
     */
    private const POST_TYPE = 'mod-toc';

    /**
     * Clear Modularity's fragment cache so TOC modules and blocks are rendered with current settings.
     *
     * Modularity only clears a module's fragment cache when that module post is saved, so changes
     * to the global settings page need to clear it explicitly. Mirrors the cache group naming in
     * Modularity\Helper\Cache.
     *
     * When the object cache supports flushing a group (e.g. Redis Object Cache), the whole fragment
     * group is flushed in one operation. This is much cheaper than looking up every page using the TOC,
     * and the fragments are simply rebuilt on the next request.
     *
     * Otherwise the entries are deleted one by one:
     * - Module posts (mod-toc): cached under the module post id.
     * - Block usage (acf/toc): cached under the id of the post containing the block.
     *
     * @return void
     */
    public static function clear(): void
    {
        $group = 'modules' . (is_multisite() ? '-' . get_current_blog_id() : '');

        if (self::canFlushGroup()) {
            wp_cache_flush_group($group);
            return;
        }

        foreach (self::getModuleIds() as $moduleId) {
            wp_cache_delete($moduleId, $group);
        }

        foreach (self::getUsageIds() as $postId) {
            wp_cache_delete($postId, $group);
        }
    }

    private static function canFlushGroup(): bool
    {
        // wp_cache_supports() and wp_cache_flush_group() are part of WordPress core since 6.1.
        return wp_cache_supports('flush_group');
    }

    /**
     * @return int[]
     */
    private static function getModuleIds(): array
    {
        return get_posts(array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ));
    }

    /**
     * Ids of posts using a TOC, either as a module or as a block.
     *
     * @return int[]
     */
    private static function getUsageIds(): array
    {
        if (!class_exists('\Modularity\Helper\ModuleUsageByName')) {
            return array();
        }

        $ids = \Modularity\Helper\ModuleUsageByName::getModuleUsageByName(self::POST_TYPE);

        return array_values(array_unique(array_filter(array_map('intval', (array) $ids))));
    }
}
