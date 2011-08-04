<?php
/**
* Spiffy Framework
*
* LICENSE
*
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.
* It is also available through the world-wide-web at this URL:
* http://www.spiffyjr.me/license
*
* @category   Spiffy
* @package    Spiffy_Service
* @copyright  Copyright (c) 2011 Kyle Spraggs (http://www.spiffyjr.me)
* @license    http://www.spiffyjr.me/license     New BSD License
*/

namespace Spiffy\Service\Factory\Zend;
use Zend_Controller_Front;

class ApplicationResource 
{
    /**
     * Gets a application resource. 
     * @param string $key
     * @return Zend_Application_Resource_ResourceAbstract
     */
    public function get($key) 
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam('bootstrap');
        
        return $bootstrap->getResource($key);
    }   
}