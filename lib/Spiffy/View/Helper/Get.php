<?php

class Spiffy_View_Helper_Get extends Zend_View_Helper_Abstract
{
    /**
     * Gets a service.
     * 
     * @param string $service
     * @return the service
     */
    public function get($service)
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam('bootstrap');
        
        return $bootstrap->getResource('service')->get($service);
    }
}