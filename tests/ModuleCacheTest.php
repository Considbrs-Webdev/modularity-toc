<?php

use ModularityToc\Helper\ModuleCache;
use PHPUnit\Framework\TestCase;

class ModuleCacheTest extends TestCase
{
    protected function setUp(): void
    {
        toc_test_reset();
    }

    public function testFlushesWholeModuleGroupWhenSupported(): void
    {
        toc_test_reset(array('flush_group' => true, 'module_ids' => array(148), 'usage_ids' => array(10)));

        ModuleCache::clear();

        $this->assertSame(array('modules'), $GLOBALS['toc_test']['flushed']);
        $this->assertSame(array(), $GLOBALS['toc_test']['cache_delete']);
        $this->assertSame(array(), $GLOBALS['toc_test']['get_posts'], 'No lookups needed when flushing the group');
    }

    public function testFlushesBlogSpecificGroupOnMultisite(): void
    {
        toc_test_reset(array('flush_group' => true, 'multisite' => true, 'blog_id' => 3));

        ModuleCache::clear();

        $this->assertSame(array('modules-3'), $GLOBALS['toc_test']['flushed']);
    }

    public function testFallbackQueriesTocModulePostType(): void
    {
        ModuleCache::clear();

        $args = $GLOBALS['toc_test']['get_posts'][0];
        $this->assertSame('mod-toc', $args['post_type']);
        $this->assertSame('ids', $args['fields']);
    }

    public function testFallbackDeletesEntryForEveryModule(): void
    {
        toc_test_reset(array('module_ids' => array(148, 149)));

        ModuleCache::clear();

        $this->assertSame(
            array(array(148, 'modules'), array(149, 'modules')),
            $GLOBALS['toc_test']['cache_delete']
        );
        $this->assertSame(array(), $GLOBALS['toc_test']['flushed']);
    }

    public function testFallbackDeletesBlockCacheForPagesUsingToc(): void
    {
        toc_test_reset(array('module_ids' => array(148), 'usage_ids' => array(10, '11', 10, 0)));

        ModuleCache::clear();

        $this->assertSame(
            array(array(148, 'modules'), array(10, 'modules'), array(11, 'modules')),
            $GLOBALS['toc_test']['cache_delete']
        );
    }

    public function testFallbackUsesBlogSpecificGroupOnMultisite(): void
    {
        toc_test_reset(array('multisite' => true, 'blog_id' => 3, 'module_ids' => array(148)));

        ModuleCache::clear();

        $this->assertSame(array(array(148, 'modules-3')), $GLOBALS['toc_test']['cache_delete']);
    }

    public function testDoesNothingWhenThereAreNoModulesOrUsage(): void
    {
        ModuleCache::clear();

        $this->assertSame(array(), $GLOBALS['toc_test']['cache_delete']);
    }
}
