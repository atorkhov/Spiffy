<?php

class Spiffy_Controller_Action_Helper_ServiceContainer extends
	Zend_Controller_Action_Helper_Abstract
{
	/**
	 * Service container.
	 * 
	 * @var Spiffy\Service\Container
	 */
	public $serviceContainer = null;

	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action_Helper_Abstract::init()
	 */
	public function init() {
		$this->serviceContainer = Zend_Registry::get('Spiffy_Service_Container');
	}

	/**
	 * Proxy to serviceContainer->get()
	 */
	public function direct($service) {
		return $this->serviceContainer;
	}
}
