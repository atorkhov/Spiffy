<?php
namespace Spiffy\Zend\View\Helper;
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
        
        try {
            $svc = $bootstrap->getResource('service')->get($service);
        } catch (\Exception $e) {
            $svc = null;
        }
        
        return $svc;
    }
}