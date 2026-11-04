<?php

namespace ModularityToc\Helper;

class ModuleCache
{
    /**
     * Module post type registered by Modularity ("mod-" + module slug).
     */
    private const POST_TYPE = 'mod-toc';

    /**
     * Delete the Modularity fragment cache for every TOC placement on the current site.
     *
     * Modularity only clears a module's fragment cache when that module post is saved,
     * so changes to the global settings page need to clear it explicitly.
     * Mirrors the cache group naming in Modularity\Helper\Cache.
     *
     * Two kinds of entries are cleared:
     * - Module posts (mod-toc): cached under the module post id.
     * - Block usage (acf/toc): cached under the id of the post containing the block.
     *
     * @return int[] Ids of the posts/pages where the TOC is used, so their page cache can be purged.
     */
    public static function clear(): array
    {
        $group = 'modules' . (is_multisite() ? '-' . get_current_blog_id() : '');

        foreach (self::getModuleIds() as $moduleId) {
            wp_cache_delete($moduleId, $group);
        }

        $postIds = self::getUsageIds();

        foreach ($postIds as $postId) {
            wp_cache_delete($postId, $group);
        }

        return $postIds;
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
