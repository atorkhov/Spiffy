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
	 * Array of propertie sfor use with toArray, fromArray, get, and set.
	 * 
	 * @var array
	 */
	protected static $__properties = array();

	/**
	 * An array of properties with filter annotations.
	 * 
	 * @var array
	 */
	protected static $__filterable = array();

	/**
	 * An array of properties with validator annotations.
	 * 
	 * @var array
	 */
	protected static $__validatable = array();

	/**
	 * Error messages from last validation.
	 * @var array
	 */
	protected $__messages = array();

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

		// all properties of the class used for toArray(), fromArray(), get(), and set()
		foreach ($reflClass->getProperties() as $property) {
			if (substr($property->name, 0, 2) == '__') {
				continue;
			}

			self::$__properties[get_called_class()][$property->name] = $property;

			if ($annotations = self::_getPropertyAnnotations($property, self::FILTER_NAMESPACE)) {
				self::$__filterable[get_called_class()][$property->name]['chain'] = null;
				self::$__filterable[get_called_class()][$property->name]['annotations'] = $annotations;
			}

			if ($annotations = self::_getPropertyAnnotations($property, self::VALIDATOR_NAMESPACE)) {
				self::$__validatable[get_called_class()][$property->name]['chain'] = null;
				self::$__validatable[get_called_class()][$property->name]['annotations'] = $annotations;
			}
		}
	}

	/**
	 * Returns the annotations for a given property.
	
	 * @param ReflectionClass $property
	 * @param null|string $namespace
	 */
	protected static function _getPropertyAnnotations($property, $namespace = null) {
		$annotations = array();
		$reader = self::$__annotationReader;

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
		return $this->__messages;
	}

	/**
	 * Checks the entities validity.
	 * 
	 * @return boolean
	 */
	public function isValid() {
		self::__initialize();

		$valid = true;
		foreach (self::$__validatable[get_called_class()] as $name => &$props) {
			$validatorChain = &$props['chain'];
			if (null === $validatorChain) {
				$validatorChain = new Zend_Validate();
				foreach ($props['annotations'] as $annotation) {
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

			$objectVars = get_object_vars($this);
			$getter = 'get' . ucfirst(self::$__filterCase->filter($name));
			if (method_exists($this, $getter)) {
				$value = $this->$getter();
			} elseif (array_key_exists($name, $objectVars)) {
				$value = $this->$property;
			} else {
				throw new Zend_Exception(
					"field '{$name}' is not public and no getter was found 
						- add {$getter}() perhaps?");
			}

			$amValid = $validatorChain->isValid($value);
			if (!$amValid) {
				$this->__messages = $validatorChain->getMessages();
				$valid = false;
			}
		}

		return $valid;
	}
}
