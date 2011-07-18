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
	 * @var string
	 */
	const FILTER_NAMESPACE = 'Spiffy\\Doctrine\\Annotations\\Filters\\Filter';

	/**
	 * @var string
	 */
	const VALIDATOR_NAMESPACE = 'Spiffy\\Doctrine\\Annotations\\Validators\\Validator';

	/**
	 * CamelCase Filter 
	 * @var Zend_Filter_Word_UnderscoreToCamelCase
	 */
	protected static $__filterCase = null;

	/**
	 * @var Doctrine\Common\Annotations\AnnotationReader
	 */
	protected static $__annotationReader = null;

	/**
	 * @var array
	 */
	protected static $__reflClass = array();

	/**
	 * @var array
	 */
	protected static $__properties = array();

	/**
	 * Error messages from last validation.
	 * @var array
	 */
	protected $_messages = array();

	/**
	 * Initialize the entity. This is done to cache the properties so they
	 * only have to be initialized once.
	 */
	protected static function __initialize() {
		if (isset(self::$__properties[get_called_class()])) {
			return;
		}

		if (null === self::$__filterCase) {
			self::$__filterCase = new Zend_Filter_Word_UnderscoreToCamelCase();
		}

		if (null === self::$__annotationReader) {
			self::$__annotationReader = new AnnotationReader();
		}

		$reader = self::$__annotationReader;
		$reflClass = self::$__reflClass[get_called_class()] = new ReflectionClass(
			get_called_class());

		$properties = &self::$__properties[get_called_class()];
		foreach ($reflClass->getProperties() as $property) {
			if (substr($property->name, 0, 2) == '__') {
				continue;
			}

			$properties[$property->name]['reflClass'] = $property;
			$properties[$property->name]['validatorChain'] = null;
		}
	}

	/**
	 * Returns the class properties names only.
	 * 
	 * @return array
	 */
	public static function getProperties() {
		self::__initialize();
		return self::$__properties[get_called_class()];
	}

	/**
	 * Get the annotation reader.
	 * 
	 * @return Doctrine\Common\Annotations\AnnotationReader
	 */
	public static function getAnnotationReader() {
		self::__initialize();
		return self::$__annotationReader;
	}

	/**
	 * Returns the annotations for a given property.
	
	 * @param ReflectionClass $property
	 * @param null|string $namespace
	 */
	protected static function _getPropertyAnnotations($property, $namespace = null) {
		$annotations = array();
		$reader = self::getAnnotationReader();

		foreach ($reader->getPropertyAnnotations($property) as $annotation) {
			if ($annotation instanceof $namespace) {
				$annotations[] = $annotation;
			}
		}
		return $annotations;
	}

	/**
	 * Returns validators for a given property.
	 * 
	 * @param string $property
	 * @return array
	 */
	protected static function _getPropertyValidator($property) {
		self::__initialize();

		if (!isset(self::$__properties[get_called_class()][$property])) {
			throw new Zend_Exception("unable to find property by name '{$property}'");
		}

		$property = &self::$__properties[get_called_class()][$property];
		$validatorChain = &$property['validatorChain'];

		if (null === $validatorChain) {
			$annotations = self::_getPropertyAnnotations($property['reflClass'],
				self::VALIDATOR_NAMESPACE);

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
	 * Returns the error messages for the last validation attempt.
	 *
	 * @return array
	 */
	public function getValidatorMessages() {
		return $this->_messages;
	}

	/**
	 * Checks the entities validity.
	 * 
	 * @return boolean
	 */
	public function isValid() {
		self::__initialize();

		$valid = true;
		foreach (array_keys(self::getProperties()) as $property) {
			$validatorChain = self::_getPropertyValidator($property);

			if (empty($validatorChain)) {
				continue;
			}

			$objectVars = get_object_vars($this);
			$getter = 'get' . ucfirst(self::$__filterCase->filter($property));
			if (method_exists($this, $getter)) {
				$value = $this->$getter();
			} elseif (array_key_exists($property, $objectVars)) {
				$value = $this->$property;
			} else {
				throw new Zend_Exception(
					"property '{$property}' is not public and no getter was found 
						- add {$getter}() perhaps?");
			}

			$amValid = $validatorChain->isValid($value);
			if (!$amValid) {
				$this->_messages = $validatorChain->getMessages();
				$valid = false;
			}
		}

		return $valid;
	}
}
