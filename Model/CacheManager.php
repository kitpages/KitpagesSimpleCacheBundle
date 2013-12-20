<?php
namespace Kitpages\SimpleCacheBundle\Model;

use Doctrine\ORM\EntityManager;
use Kitpages\SimpleCacheBundle\Entity\Backend;

class CacheManager
{
    /** @var EntityManager */
    protected $em;
    protected $_expirationTime = null;
    
    /**
     *
     * @param EntityManager $em
     * @param int $expirationTime in seconds
     */
    public function __construct($em, $expirationTime)
    {
        $this->em = $em;
        $this->_expirationTime = $expirationTime;
    }

    /**
     * clear cache
     * @param string $id cache id, comparison with like and % if you want
     */
    public function clear($id)
    {
        // check if $id exists in cache
        $query = $this->em->createQuery("
            SELECT backend FROM Kitpages\SimpleCacheBundle\Entity\Backend backend
            WHERE backend.id like :backendId
        ")->setParameter('backendId', $id);
        $backendList = $query->getResult();
        foreach ($backendList as $backend) {
            $this->em->remove($backend);
        }
        $this->em->flush();
    }
    
    /**
     *
     * @param string $id
     * @param function $callback
     * @param int $expiration 
     */
    public function get($id, $callback, $params = array(), $expiration = null)
    {
        // check if $id exists in cache
        $query = $this->em->createQuery("
            SELECT backend FROM Kitpages\SimpleCacheBundle\Entity\Backend backend
            WHERE backend.id = :backendId
        ")->setParameter('backendId', $id);
        $backendList = $query->getResult();
        $cache = null;
        if (count($backendList) == 1 ) {
            $cache = $backendList[0];
        }
        
        if ($cache) {
            // check expiration date
            $now = new \DateTime();
            $expiredAt = $cache->getExpiredAt();
            if ( ($expiredAt instanceof \DateTime) && ($cache->getExpiredAt() < $now) ) {
                $data = $this->_execute($callback, $params);
                $cache->setData($data);
                $expiredAt = $this->_calculateExpiredAt($expiration);
                $cache->setExpiredAt($expiredAt);
                $this->em->persist($cache);
                $this->em->flush();
                return $data;
            }
            // cache ok
            return $cache->getData();
        }
        
        // check
        $data = $this->_execute($callback, $params);
        $cache = new Backend();
        $cache->setId($id);
        $cache->setData($data);
        $expiredAt = $this->_calculateExpiredAt($expiration);
        $cache->setExpiredAt($expiredAt);
        $this->em->persist($cache);
        $this->em->flush();
        return $data;
    }
    
    protected function _execute($callback, $params)
    {
        return call_user_func_array($callback, $params);
    }
        
    protected function _calculateExpiredAt($time) {
        $now = new \DateTime();
        if (!$time) {
            return null;
        }
        if ($time instanceof \DateInterval) {
            return $now->add($time);
        }
        if (is_int($time)) {
            $interval = new \DateInterval('P'.$time.'s');
            return $now->add($interval);
        }
        throw new \Exception('unknown parameter type. expiration time shoud be a int in seconds or a DateInterval');
    }
}