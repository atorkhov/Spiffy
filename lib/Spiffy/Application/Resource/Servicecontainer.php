<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class Spiffy_Application_Resource_Servicecontainer extends
	Zend_Application_Resource_ResourceAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see Zend_Application_Resource_Resource::init()
	 */
	public function init() {
		$locator = new FileLocator(array(APPLICATION_PATH . '/configs'));
		$container = new ContainerBuilder();

		$loader = new YamlFileLoader($container, $locator);
		$loader->load('services.yml');

		// add to zend registry for action helper
		Zend_Registry::set('Spiffy_Service_Container', $container);

		return $container;
	}
}
