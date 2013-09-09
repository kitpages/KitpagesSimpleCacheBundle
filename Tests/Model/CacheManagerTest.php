<?php
namespace Kitpages\SimpleCacheBundle\Tests\Model;


use Kitpages\SimpleCacheBundle\Model\CacheManager;
use Kitpages\SimpleCacheBundle\Tests\BundleOrmTestCase;

class CacheManagerTest extends BundleOrmTestCase
{
    /** @var  CacheManager */
    protected $cacheManager;

    public function setUp()
    {
        parent::setUp();
        $em = $this->getEntityManager();
        $this->cacheManager = new CacheManager($em, 10);
    }

    public function testPhpunit()
    {
        $this->assertTrue(true);
    }

    public function testGet()
    {
        // check without cache
        $start = microtime(true);
        $html = $this->cacheManager->get(
            'phpunit-test-id-15',
            function() {
                $output = "hello world";
                sleep (1);
                return $output;
            }
        );
        $this->assertTrue(microtime(true) > $start + 1 );
        $this->assertEquals("hello world", $html);

        // check from cache
        $start = microtime(true);
        $html = $this->cacheManager->get(
            'phpunit-test-id-15',
            function() {
                $output = "tutu";
                sleep (1);
                return $output;
            }
        );
        $this->assertTrue(microtime(true) < $start + 0.5 );
        $this->assertEquals("hello world", $html);
    }

    public function testClean()
    {
        // check without cache
        $start = microtime(true);
        $html = $this->cacheManager->get(
            'phpunit-test-id-15',
            function() {
                $output = "hello world";
                sleep (1);
                return $output;
            }
        );
        $this->assertTrue(microtime(true) > $start + 1 );
        $this->assertEquals("hello world", $html);

        $this->cacheManager->clear('phpunit-test-id-15');

        // check from cache
        $start = microtime(true);
        $html = $this->cacheManager->get(
            'phpunit-test-id-15',
            function() {
                $output = "tutu";
                sleep (1);
                return $output;
            }
        );
        $this->assertTrue(microtime(true) > $start + 1 );
        $this->assertEquals("tutu", $html);

    }

}