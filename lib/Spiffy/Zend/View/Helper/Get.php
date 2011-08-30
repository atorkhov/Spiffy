<?php
namespace Spiffy\Service\View\Helper;
use Zend_Controller_Front,
    Zend_View_Helper_Abstract;

class Get extends Zend_View_Helper_Abstract
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