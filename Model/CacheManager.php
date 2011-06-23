<?php
namespace Kitpages\SimpleCacheBundle\Model;

use Symfony\Bundle\DoctrineBundle\Registry;
use Kitpages\SimpleCacheBundle\Entity\Backend;

class CacheManager
{
    protected $_doctrine = null;
    protected $_expirationTime = null;
    
    /**
     *
     * @param Registry $doctrine
     * @param int $expirationTime in seconds
     */
    public function __construct($doctrine, $expirationTime)
    {
        $this->_doctrine = $doctrine;
        $this->_expirationTime = $expirationTime;
    }
    
    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     */
    protected function getDoctrine() {
        return $this->_doctrine;
    }
    
    /**
     *
     * @param string $id
     * @param function $callback
     * @param int $expiration 
     */
    public function get($id, $callback, $params = array(), $expiration = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        // check if $id exists in cache
        $query = $em->createQuery("
            SELECT backend FROM KitpagesSimpleCacheBundle:Backend backend
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
                $cache->setData($cache);
                $expiredAt = $this->_calculateExpiredAt($expiration);
                $cache->setExpiredAt($expiredAt);
                $em->persist($cache);
                $em->flush();
                return $data;
            }
            // cache ok
            return $cache->getData();
        }
        
        // check
        $data = $this->_execute($callback, $params);
        $cache = new Backend();
        $cache->setData($cache);
        $expiredAt = $this->_calculateExpiredAt($expiration);
        $cache->setExpiredAt($expiredAt);
        $em->persist($cache);
        $em->flush();
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
        throw new Exception('unknown parameter type. expiration time shoud be a int in seconds or a DateInterval');
    }
}