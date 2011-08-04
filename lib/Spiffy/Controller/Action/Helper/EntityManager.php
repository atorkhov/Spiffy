<?php

use Spiffy\Doctrine\Container;

class Spiffy_Controller_Action_Helper_EntityManager extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Service container.
     * @var Spiffy\Doctrine\Container
     */
    public $doctrine = null;

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action_Helper_Abstract::init()
     */
    public function init()
    {
        $this->doctrine = Zend_Registry::get('Spiffy_Doctrine');
    }

    /**
     * Proxy to doctrine->getEntityManager($emName)
     * 
     * @param string $emName Name of the entityManager instance to retrieve.
     */
    public function direct($emName = null)
    {
        $emName = $emName ? $emName : Container::getDefaultCacheKey();
        return $this->doctrine->getEntityManager($emName);
    }

    /**
     * Proxy to doctrine->getEntityManager('default')->getRepository()
     * 
     * @param string $repository
     */
    public function getRepository($repository)
    {
        return $this->doctrine->getEntityManager()->getRepository($repository);
    }
}
