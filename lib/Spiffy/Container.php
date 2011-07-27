<?php

namespace Spiffy;

use Closure;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Zend_Controller_Action_HelperBroker;
use Zend_Registry;

class Container
{
	/**
	 * Container options.
	 * @var array
	 */
	protected $options;

	/**
	 * @var Spiffy\Doctrine\Container
	 */
	protected $doctrineContainer = null;

	/**
	 * @var Symfony\Component\DependencyInjection\ContainerBuilder
	 */
	protected $serviceContainer = null;

	/**
	 * Array of helpers to autoload.
	 * @var array
	 */
	protected $helpers = array(
		'Spiffy_Controller_Action_Helper_ServiceContainer',
		'Spiffy_Controller_Action_Helper_Get');

	/**
	 * Constructor.
	 * 
	 * @param array $options
	 */
	public function __construct(array $options) {
		$this->setOptions($options);
	}

	/**
	 * Get Doctrine container.
	 * 
	 * @return \Spiffy\Doctrine\Container
	 */
	public function getDoctrineContainer() {
		return $this->doctrineContainer;
	}

	/**
	 * Get Symfony service container.
	 * 
	 * @return Symfony\Component\DependencyInjection\ContainerBuilder
	 */
	public function getServiceContainer() {
		return $this->serviceContainer;
	}

	/**
	 * Gets multi options for Spiffy form elements.
	 * 
	 * @param string $entityClass
	 * @param Closure $qbClosure
	 * @param string $emName
	 * @return array
	 */
	public function getMultiOptions($entityClass, Closure $qbClosure, $emName = null) {
		if (null === $this->doctrineContainer) {
			throw new Exception\DoctrineContainerRequired(
				'Doctrine container is required for this method');
		}

		$options = array();

		$entityManager = $this->getDoctrineContainer()->getEntityManager($emName);
		$mdata = $entityManager->getClassMetadata($entityClass);
		$repository = $entityManager->getRepository($entityClass);

		$qb = call_user_func($qbClosure, $repository);
		foreach ($qb->getQuery()->execute() as $row) {
			if (!is_object($row)) {
				throw new Exception\InvalidResult('row result must be an object');
			}

			$id = $mdata->getIdentifierValues($row);
			if (count($mdata->getIdentifier()) > 1) {
				$id = serialize($id);
			} else {
				$id = current($id);
			}

			$options[$id] = (string) $row;
		}

		return $options;
	}

	/**
	 * Set options.
	 * 
	 * @param array $options
	 */
	public function setOptions(array $options) {
		if ($options['serviceContainer']['enabled']) {
			$this->_registerServiceContainer($options['serviceContainer']);
		}

		if ($options['doctrineContainer']['enabled']) {
			$this->_registerDoctrineContainer($options['doctrineContainer']);
		}
	}

	/**
	 * Register Spiffy doctrine container. Required for Spiffy\Entity and Spiffy\Form.
	 *
	 * @param array $options
	 */
	protected function _registerDoctrineContainer(array $options) {
		$this->doctrineContainer = new \Spiffy\Doctrine\Container($options);
	}

	/**
	 * Registers the Symfony service container (with DI).
	 *
	 * @param array $options
	 * @return Symfony\Component\DependencyInjection\ContainerBuilder
	 */
	protected function _registerServiceContainer(array $options) {
		$paths = $options['paths'];

		if (!is_array($paths)) {
			$paths = array($paths);
		}

		$locator = new FileLocator($paths);

		$container = new ContainerBuilder();

		$loader = new YamlFileLoader($container, $locator);
		$loader->load($options['file']);

		if ($options['autoloadHelpers']) {
			$this->_registerHelpers();
		}

		$this->serviceContainer = $container;
	}

	/**
	 * Register action helpers.
	 */
	protected function _registerHelpers() {
		foreach ($this->helpers as $helperClass) {
			$helper = new $helperClass();
			Zend_Controller_Action_HelperBroker::addHelper($helper);
		}
	}
}
