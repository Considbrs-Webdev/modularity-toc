<?php

/**
 * Minimal WordPress/Modularity stubs so helpers can be tested without loading WordPress.
 * Functions are defined in the namespace of the code under test, which PHP resolves first.
 */

namespace {
    require_once __DIR__ . '/../vendor/autoload.php';

    $GLOBALS['toc_test'] = array();

    function toc_test_reset(array $state = array()): void
    {
        $GLOBALS['toc_test'] = $state + array(
            'multisite'    => false,
            'flush_group'  => false,
            'flushed'      => array(),
            'blog_id'      => 1,
            'module_ids'   => array(),
            'usage_ids'    => array(),
            'cache_delete' => array(),
            'actions'      => array(),
            'get_posts'    => array(),
        );
    }

    toc_test_reset();
}

namespace Modularity\Helper {
    class ModuleUsageByName
    {
        public static function getModuleUsageByName($postType)
        {
            return $GLOBALS['toc_test']['usage_ids'];
        }
    }
}

namespace ModularityToc\Helper {
    function is_multisite()
    {
        return $GLOBALS['toc_test']['multisite'];
    }

    function get_current_blog_id()
    {
        return $GLOBALS['toc_test']['blog_id'];
    }

    function wp_cache_supports($feature)
    {
        return $feature === 'flush_group' && $GLOBALS['toc_test']['flush_group'];
    }

    function wp_cache_flush_group($group)
    {
        $GLOBALS['toc_test']['flushed'][] = $group;
        return true;
    }

    function get_posts($args)
    {
        $GLOBALS['toc_test']['get_posts'][] = $args;
        return $GLOBALS['toc_test']['module_ids'];
    }

    function wp_cache_delete($key, $group = '')
    {
        $GLOBALS['toc_test']['cache_delete'][] = array($key, $group);
        return true;
    }


    function do_action($hook, ...$args)
    {
        $GLOBALS['toc_test']['actions'][] = array($hook, $args);
    }
}
