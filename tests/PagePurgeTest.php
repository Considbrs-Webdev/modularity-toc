<?php

use ModularityToc\Helper\PagePurge;
use PHPUnit\Framework\TestCase;

class PagePurgeTest extends TestCase
{
    protected function setUp(): void
    {
        toc_test_reset();
    }

    public function testPurgesAllLiteSpeedPagesOnce(): void
    {
        PagePurge::purgeAll();

        $hooks = array_column($GLOBALS['toc_test']['actions'], 0);
        $this->assertSame(1, count(array_keys($hooks, 'litespeed_purge_all', true)));
        $this->assertNotContains('litespeed_purge_post', $hooks);
    }

    public function testFiresPurgedActionAfterPurge(): void
    {
        PagePurge::purgeAll();

        $this->assertSame(
            array('litespeed_purge_all', 'ModularityToc/PagePurge/Purged'),
            array_column($GLOBALS['toc_test']['actions'], 0)
        );
    }
}
