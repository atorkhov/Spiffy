<?php

use Spiffy\Container;

class Spiffy_Application_Resource_Spiffy extends Zend_Application_Resource_ResourceAbstract
{
	/**
	 * Class options.
	 * @var array
	 */
	protected $_options = array(
		'doctrineContainer' => array(
			'enabled' => true,
			'annotationFiles' => array(
				'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php',
				'Spiffy/Doctrine/Annotations/Filters/Filter.php',
				'Spiffy/Doctrine/Annotations/Validators/Validator.php')),
		'serviceContainer' => array(
			'autoloadHelpers' => true,
			'enabled' => true,
			'paths' => array('/configs'),
			'file' => 'services.yml'));

	/**
	 * (non-PHPdoc)
	 * @see Zend_Application_Resource_Resource::init()
	 */
	public function init() {
		$container = new Container($this->getOptions());

		Zend_Registry::set('Spiffy_Container', $container);

		return $container;
	}
}
