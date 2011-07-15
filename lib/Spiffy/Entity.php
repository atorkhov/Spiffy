<?php
namespace Spiffy;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use Zend_Exception;
use Zend_Filter_Word_UnderscoreToCamelCase;
use Zend_Loader;
use Zend_Validate;

class Entity
{
	/**
	 * CamelCase Filter 
	 * @var Zend_Filter_Word_UnderscoreToCamelCase
	 */
	protected static $__filter = null;

	/**
	 * Error messages from last validation.
	 * @var array
	 */
	protected static $__messages = array();

	/**
	 * @var Doctrine\Common\Annotations\AnnotationReader
	 */
	protected static $__reader = null;

	/**
	 * @var array
	 */
	protected static $__reflClass = array();

	/**
	 * @var array
	 */
	protected static $__properties = array();

	/**
	 * Initialize the entity.
	 */
	protected static function __initialize() {
		$class = get_called_class();
		if (isset(self::$__properties[$class])) {
			return;
		}

		if (null === self::$__filter) {
			self::$__filter = new Zend_Filter_Word_UnderscoreToCamelCase();
		}

		if (null === self::$__reader) {
			self::$__reader = new AnnotationReader();
		}

		$reader = self::$__reader;
		$reflClass = self::$__reflClass[$class] = new ReflectionClass($class);
		foreach ($reflClass->getProperties() as $property) {
			$name = $property->name;
			self::$__properties[$class][$name]['annotations'] = $reader
				->getPropertyAnnotations($property);
			self::$__properties[$class][$name]['reflClass'] = $property;
			self::$__properties[$class][$name]['validator'] = null;
		}
	}

	/**
	 * Returns the error messages for the last validation attempt.
	 * 
	 * @return array
	 */
	public static function getValidatorMessages() {
		if (isset(self::$__messages[get_called_class()])) {
			return self::$__messages[get_called_class()];
		}
		return array();
	}

	/**
	 * Gets a properties annotations. If a namespace is specified then only annotations
	 * matching (regex) that namespace will be returned.
	 * 
	 * @param string $property
	 * @param string $annotation
	 * @return array
	 */
	public static function getPropertyAnnotations($property, $namespace = null) {
		$calledClass = get_called_class();

		if (!isset(self::$__properties[$calledClass][$property])) {
			throw new Zend_Exception("unable to find property by name '{$property}'");
		}

		if (null === $namespace) {
			return self::$__properties[$calledClass][$property];
		}

		$annotations = array();
		foreach (self::$__properties[$calledClass][$property]['annotations'] as $annotation) {
			$regex = '/' . preg_quote($namespace) . '/';
			if (preg_match($regex, get_class($annotation))) {
				$annotations[] = $annotation;
			}
		}
		return $annotations;
	}

	/**
	 * Returns the property names only.
	 * 
	 * @return array
	 */
	public static function getProperties() {
		return array_keys(self::$__properties[get_called_class()]);
	}

	/**
	 * Returns validators for a given property.
	 * 
	 * @param string $property
	 * @return array
	 */
	public static function getPropertyValidator($property) {
		$calledClass = get_called_class();

		if (!isset(self::$__properties[$calledClass][$property])) {
			throw new Zend_Exception("unable to find property by name '{$property}'");
		}

		$validatorChain = &self::$__properties[$calledClass][$property]['validator'];
		if (null === $validatorChain) {
			$namespace = 'Spiffy\Doctrine\Annotations\Zend';

			$annotations = self::getPropertyAnnotations($property, $namespace);

			if (empty($annotations)) {
				$validatorChain = array();
			} else {
				$validatorChain = new Zend_Validate();
				foreach ($annotations as $annotation) {
					try {
						Zend_Loader::loadClass($annotation->class);
					} catch (Zend_Exception $e) {
						throw new Zend_Exception("failed to find validator '{$annotation->class}'");
					}
					if (empty($annotation->value)) {
						$validator = new $annotation->class();
					} else {
						$validator = new $annotation->class($annotation->value);
					}

					$validatorChain->addValidator($validator, $annotation->breakChain);
				}
			}
		}

		return $validatorChain;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		self::__initialize();

		$this->init();
	}

	/**
	 * Child initialization.
	 */
	public function init() {
	}

	/**
	 * Checks the entities validity.
	 */
	public function isValid() {
		$valid = true;
		foreach (self::getProperties() as $property) {
			$validatorChain = self::getPropertyValidator($property);

			if (empty($validatorChain)) {
				continue;
			}

			$getter = 'get' . ucfirst(self::$__filter->filter($property));
			if (method_exists($this, $getter)) {
				$value = $this->$getter();
			} elseif (property_exists($this, $property)) {
				$value = $this->$property;
			} else {
				throw new Zend_Exception(
					"property '{$property}' is not public and no getter was found 
						- add {$getter}() perhaps?");
			}

			$amValid = $validatorChain->isValid($value);
			if (!$amValid) {
				self::$__messages[get_class($this)][] = $validatorChain->getMessages();
				$valid = false;
			}
		}

		return $valid;
	}
}
