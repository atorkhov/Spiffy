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
* @package    Spiffy_Controller
* @copyright  Copyright (c) 2011 Kyle Spraggs (http://www.spiffyjr.me)
* @license    http://www.spiffyjr.me/license     New BSD License
*/

namespace Spiffy\Service\Controller\Action\Helper;
use Zend_Controller_Action_Helper_Abstract,
    Zend_Registry;

class Get extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Service container.
     * @var Spiffy\Service\Container
     */
    public $serviceContainer = null;

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action_Helper_Abstract::init()
     */
    public function init()
    {
        $this->serviceContainer = Zend_Registry::get('Spiffy_Service');
    }

    /**
     * Proxy to serviceContainer->get()
     */
    public function direct($service)
    {
        return $this->serviceContainer->get($service);
    }
}
