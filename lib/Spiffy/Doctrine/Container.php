<?php
namespace Spiffy\Doctrine;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;

class Container
{
	/**
	 * Array of Doctrine 2 entity managers.
	 *
	 * @var array
	 */
	protected static $_entityManagers = array();

	/**
	 * Container options.
	 * 
	 * @var array
	 */
	protected $_options = array();

	/**
	 * Constructor.
	 * 
	 * @param array $options
	 */
	public function __construct(array $options = array()) {
		$this->setOptions($options);
	}

	/**
	 * Gets an entity manager.
	 * 
	 * @param string $emName
	 * @return Doctrine\ORM\EntityManager
	 */
	public static function getEntityManager($emName = null) {
		$emName = $emName ? $emName : 'default';
		if (!isset(self::$_entityManagers[$emName])) {
			throw new Exception\InvalidEntityManager(
				"EntityManager with name '{$emName}' could not be found.");
		}
		return self::$_entityManagers[$emName];
	}

	/**
	 * Sets an entity manager for the given name.
	 *
	 * @param Doctrine\ORM\EntityManager
	 * @param string $emName
	 */
	public static function setEntityManager(EntityManager $em, $emName = null) {
		$emName = $emName ? $emName : 'default';
		self::$_entityManagers[$emName] = $em;
	}

	/**
	 * Set options.
	 * 
	 * @param array $options
	 */
	public function setOptions(array $options) {
		foreach ($options as $key => $value) {
			switch (trim(strtolower($key))) {
				case 'annotationfiles':
					$this->setAnnotationFiles($value);
					break;
				case 'annotationfile':
					$this->setAnnotationFile($value);
					break;
				default:
					throw new Exception\InvalidOption("Option '{$key}' is not a valid option.");
					break;
			}
		}
	}

	/**
	 * Registers annotation files.
	 * 
	 * @param array $files
	 */
	public function setAnnotationFiles(array $files) {
		foreach ($files as $file) {
			$this->setAnnotationFile($file);
		}
	}

	/**
	 * Sets an annotation file for Doctrine 2.
	 * 
	 * @param string $file
	 */
	public function setAnnotationFile($file) {
		AnnotationRegistry::registerFile($file);
	}
}
