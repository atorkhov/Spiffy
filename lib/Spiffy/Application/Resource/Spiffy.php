<?php
use Spiffy\Doctrine\Container;

class Spiffy_Application_Resource_Spiffy extends Zend_Application_Resource_ResourceAbstract
{
	/**
	 * Array of helpers to autoload.
	 * @var array
	 */
	protected $helpers = array(
		'Spiffy_Controller_Action_Helper_ServiceContainer',
		'Spiffy_Controller_Action_Helper_Get');

	/**
	 * Class options.
	 * @var array
	 */
	protected $_options = array(
		'autoloadHelpers' => false,
		'annotationFiles' => array(
			'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php',
			'Spiffy/Doctrine/Annotations/Filters/Filter.php',
			'Spiffy/Doctrine/Annotations/Validators/Validator.php'));

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
		$options = $this->getOptions();
		if (!$options['autoloadHelpers']) {
			return;
		}

		foreach ($this->helpers as $helperClass) {
			$helper = new $helperClass();
			Zend_Controller_Action_HelperBroker::addHelper($helper);
		}
	}
}
