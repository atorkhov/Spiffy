<?php
use Spiffy\Doctrine\Container;

class Spiffy_Application_Resource_Spiffy extends Zend_Application_Resource_ResourceAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see Zend_Application_Resource_Resource::init()
	 */
	public function init() {
		$container = new Container($this->getOptions());
		return $container;
	}
}
