<?php
use Spiffy\Doctrine\Container;

class Spiffy_Application_Resource_Spiffy extends Zend_Application_Resource_ResourceAbstract
{
	protected $helpers = array(
		'Spiffy_Controller_Action_Helper_ServiceContainer',
		'Spiffy_Controller_Action_Helper_Get');

	/**
	 * (non-PHPdoc)
	 * @see Zend_Application_Resource_Resource::init()
	 */
	public function init() {
		$this->registerHelpers();

		$container = new Container($this->getOptions());

		// set registry instance
		Zend_Registry::set('Spiffy_Container', $container);

		return $container;
	}

	/**
	 * Register action helpers.
	 */
	public function registerHelpers() {
		foreach ($this->helpers as $helperClass) {
			$helper = new $helperClass();
			Zend_Controller_Action_HelperBroker::addHelper($helper);
		}
	}
}
