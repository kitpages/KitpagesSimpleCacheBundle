KitpagesSimpleCacheBundle
=========================

Very simple cache system for symfony2. The cache data are saved in one table in
database.

author : Philippe Le Van (twitter : @plv)
http://www.kitpages.fr/fr/cms/102/kitpagessimplecachebundle


Installation
============
hum... as usual...

put the code in vendors/Kitpages/SimpleCacheBundle

add vendors/ in the app/autoload.php

add the new Bundle in app/appKernel.php

You need to create a table in the database :

    CREATE TABLE `simple_cache_backend` (
      `id` varchar(255) NOT NULL,
      `data` longtext COMMENT '(DC2Type:array)',
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      `expired_at` datetime default NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8

(or you can use php app/console doctrine:schema:update)

Users Guide
===========

1/ record & retrieve into/from the cache
-----------------------------------------

    $cacheManager = $this->get('kitpages.simple_cache');
    $html = $cacheManager->get(
        'my-cache-uniq-id-12',
        function() {
            $output = "hello world";
            sleep (3);
            return $output;
        }
    );

2/ clear the cache
-------------------

    $cacheManager = $this->get('kitpages.simple_cache');
    $cacheManager->clear('my-cache-uniq-id-12');

3/ a more complex example : arguments given to the callback and expiration
---------------------------------------------------------------------------

    $html = $cacheManager->get(
        'my-cache-uniq-id-12',
        function($arg1, $arg2) {
            $output = "hello world";
            sleep (3);
            return $output;
        },
        array(12, 34),
        $myExpirationTimeInSeconds
    );

4/ multiple delete in the cache
--------------------------------

    $cacheManager = $this->get('kitpages.simple_cache');
    $cacheManager->clear('my-cache-%');
    // remove all the entries in the cache beginning by "my-cache-"
