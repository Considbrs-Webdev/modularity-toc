<?php

use ModularityToc\Helper\PagePurge;
use PHPUnit\Framework\TestCase;

class PagePurgeTest extends TestCase
{
    protected function setUp(): void
    {
        toc_test_reset();
    }

    public function testPurgesLiteSpeedAndVarnishForEachPost(): void
    {
        toc_test_reset(array('permalinks' => array(10 => 'https://example.se/a/', 11 => 'https://example.se/b/')));

        PagePurge::purge(array(10, 11));

        $this->assertSame(
            array(array('litespeed_purge_post', array(10)), array('litespeed_purge_post', array(11))),
            array_slice($GLOBALS['toc_test']['actions'], 0, 2)
        );

        $requests = $GLOBALS['toc_test']['requests'];
        $this->assertSame('https://example.se/a/', $requests[0][0]);
        $this->assertSame('PURGE', $requests[0][1]['method']);
        $this->assertFalse($requests[0][1]['blocking']);
        $this->assertCount(2, $requests);
    }

    public function testSkipsVarnishRequestWhenNoPermalink(): void
    {
        PagePurge::purge(array(10));

        $this->assertSame(array(), $GLOBALS['toc_test']['requests']);
    }

    public function testIgnoresDuplicateAndInvalidIds(): void
    {
        toc_test_reset(array('permalinks' => array(10 => 'https://example.se/a/')));

        PagePurge::purge(array(10, '10', 0));

        $this->assertCount(1, $GLOBALS['toc_test']['requests']);
    }

    public function testFiresPurgedActionWithIds(): void
    {
        PagePurge::purge(array(10, 11));

        $last = end($GLOBALS['toc_test']['actions']);
        $this->assertSame(array('ModularityToc/PagePurge/Purged', array(array(10, 11))), $last);
    }
}
