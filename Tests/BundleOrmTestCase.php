<?php
namespace Kitpages\SimpleCacheBundle\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ArrayCache;
use Kitpages\SimpleCacheBundle\Tests\SchemaSetupListener;

use DoctrineExtensions\PHPUnit\OrmTestCase;

class BundleOrmTestCase
    extends OrmTestCase
{
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function createEntityManager()
    {
        // event manager used to create schema before tests
        $eventManager = new EventManager();
        $eventManager->addEventListener(array("preTestSetUp"), new SchemaSetupListener());

        // doctrine xml configs and namespaces
        $configPathList = array();
        if (is_dir(__DIR__.'/../Resources/config/doctrine')) {
            $dir = __DIR__.'/../Resources/config/doctrine';
            $configPathList[] = $dir;
            $prefixList[$dir] = 'Kitpages\SimpleCacheBundle\Entity';
        }
        if (is_dir(__DIR__.'/_doctrine/config')) {
            $dir = __DIR__.'/_doctrine/config';
            $configPathList[] = $dir;
            $prefixList[$dir] = 'Kitpages\SimpleCacheBundle\Tests\TestEntities';
        }
        // create drivers (that reads xml configs)
        $driver = new \Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver($prefixList);

        // create config object
        $config = new Configuration();
        $config->setMetadataCacheImpl(new ArrayCache());
        $config->setMetadataDriverImpl($driver);
        $config->setProxyDir(__DIR__.'/TestProxies');
        $config->setProxyNamespace('Kitpages\SimpleCacheBundle\Tests\TestProxies');
        $config->setAutoGenerateProxyClasses(true);
        //$config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());

        // create entity manager
        $em = EntityManager::create(
            array(
                'driver' => 'pdo_sqlite',
                'path' => "/tmp/sqlite-test.db"
            ),
            $config,
            $eventManager
        );

        return $em;
    }

    protected function getDataSet()
    {
        return $this->createFlatXmlDataSet(__DIR__."/_doctrine/dataset/entityFixture.xml");
    }

}
