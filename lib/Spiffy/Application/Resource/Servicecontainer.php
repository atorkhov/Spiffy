<?php

use Spiffy\Service\Container;

class Spiffy_Application_Resource_Servicecontainer extends
	Zend_Application_Resource_ResourceAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see Zend_Application_Resource_Resource::init()
	 */
	public function init() {
		return new Container($this->getOptions());
	}
}
