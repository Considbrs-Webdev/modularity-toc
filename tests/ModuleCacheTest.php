<?php

use ModularityToc\Helper\ModuleCache;
use PHPUnit\Framework\TestCase;

class ModuleCacheTest extends TestCase
{
    protected function setUp(): void
    {
        toc_test_reset();
    }

    public function testQueriesTocModulePostType(): void
    {
        ModuleCache::clear();

        $args = $GLOBALS['toc_test']['get_posts'][0];
        $this->assertSame('mod-toc', $args['post_type']);
        $this->assertSame('ids', $args['fields']);
    }

    public function testClearsFragmentCacheForEveryModule(): void
    {
        toc_test_reset(array('module_ids' => array(148, 149)));

        ModuleCache::clear();

        $this->assertSame(
            array(array(148, 'modules'), array(149, 'modules')),
            $GLOBALS['toc_test']['cache_delete']
        );
    }

    public function testClearsBlockCacheForPagesUsingToc(): void
    {
        toc_test_reset(array('module_ids' => array(148), 'usage_ids' => array(10, 11)));

        ModuleCache::clear();

        $this->assertSame(
            array(array(148, 'modules'), array(10, 'modules'), array(11, 'modules')),
            $GLOBALS['toc_test']['cache_delete']
        );
    }

    public function testUsesBlogSpecificGroupOnMultisite(): void
    {
        toc_test_reset(array('multisite' => true, 'blog_id' => 3, 'module_ids' => array(148)));

        ModuleCache::clear();

        $this->assertSame(array(array(148, 'modules-3')), $GLOBALS['toc_test']['cache_delete']);
    }

    public function testReturnsDeduplicatedIntegerUsageIds(): void
    {
        toc_test_reset(array('usage_ids' => array('10', 10, 0, '11')));

        $this->assertSame(array(10, 11), ModuleCache::clear());
    }

    public function testDoesNothingWhenThereAreNoModulesOrUsage(): void
    {
        $this->assertSame(array(), ModuleCache::clear());
        $this->assertSame(array(), $GLOBALS['toc_test']['cache_delete']);
    }
}
