<?php
namespace Spiffy\Domain;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use Zend_Filter;
use Zend_Filter_Word_UnderscoreToCamelCase;
use Zend_Loader;
use Zend_Validate;

class Model
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
	 * flag: should I throw exceptions when a getter is unavailable?
	 * @var boolean
	 */
	protected $__throwNoGetterExceptions = true;

	/**
	 * flag: should I throw exceptions when a setter is unavailable?
	 * @var boolean
	 */
	protected $__throwNoSetterExceptions = true;

	/**
	 * CamelCase Filter 
	 * @var Zend_Filter_Word_UnderscoreToCamelCase
	 */
	protected static $__filterCase = null;

	/**
	 * Doctrine annotation reader
	 * @var Doctrine\Common\Annotations\AnnotationReader
	 */
	protected static $__annotationReader = null;

	/**
	 * ReflectionClasses.
	 * @var array
	 */
	protected static $__reflClass = array();

	/**
	 * Array of propertie sfor use with toArray, fromArray, get, and set.
	 * @var array
	 */
	protected static $__properties = array();

	/**
	 * An array of properties with filter annotations.
	 * @var array
	 */
	protected static $__filterable = array();

	/**
	 * An array of properties with validator annotations.
	 * @var array
	 */
	protected static $__validatable = array();

	/**
	 * Error messages from last validation.
	 * @var array
	 */
	protected $__messages = array();

	/**
	 * Set throwNoGetterExceptions.
	 *
	 * @param boolean $value
	 */
	public function setThrowNoGetterExceptions($value) {
		$this->__throwNoGetterExceptions = $value;
	}

	/**
	 * Set throwNoSetterExceptions.
	 * 
	 * @param boolean $value
	 */
	public function setThrowNoSetterExceptions($value) {
		$this->__throwNoSetterExceptions = $value;
	}

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
		static::__initialize();

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
	 * Get the entity manager.
	 *
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager($emName = null) {
		$emName = $emName ? $emName : $this->__defaultEntityManager;
		return Container::getEntityManager($emName);
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
	 * Generic getter that applies filtering.
	 * 
	 * @param string $key
	 */
	public function filter($key, $value) {
		static::__initialize();

		$self = get_class($this);
		if (!isset(self::$__properties[$self][$key])) {
			throw new Exception\InvalidProperty("no such property '{$key}' exists for '{$self}'");
		}

		if (isset(self::$__filterable[$self][$key])) {
			$filterChain = &self::$__filterable[$self][$key]['chain'];
			if (null === $filterChain) {
				$filterChain = new Zend_Filter();
				foreach (self::$__filterable[$self][$key]['annotations'] as $annotation) {
					try {
						Zend_Loader::loadClass($annotation->class);
					} catch (Zend_Exception $e) {
						throw new Zend_Exception("failed to find filter '{$annotation->class}'");
					}
					if (empty($annotation->value)) {
						$filter = new $annotation->class();
					} else {
						$filter = new $annotation->class($annotation->value);
					}

					$filterChain->addFilter($filter);
				}
			}
			return $filterChain->filter($value);
		}
		return $value;
	}

	/**
	 * Set entity values from an array.
	 * 
	 * @param array $data
	 * @return void
	 */
	public function fromArray(array $data) {
		static::__initialize();

		foreach ($data as $key => $value) {
			if (isset(self::$__properties[get_class($this)][$key])) {
				$this->_set($key, $value);
			}
		}
	}

	/**
	 * Convert entity to an array.
	 * 
	 * @return array
	 */
	public function toArray() {
		static::__initialize();

		$values = array();
		foreach (self::$__properties[get_class($this)] as $property) {
			$values[$property->name] = $this->_get($property->name);
		}
		return $values;
	}

	/**
	 * Checks the entities validity.
	 * 
	 * @return boolean
	 */
	public function isValid() {
		static::__initialize();

		$valid = true;
		foreach (self::$__validatable[get_class($this)] as $name => &$props) {
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

			$value = $this->_get($name);
			$amValid = $validatorChain->isValid($value);
			if (!$amValid) {
				$this->__messages = $validatorChain->getMessages();
				$valid = false;
			}
		}

		return $valid;
	}

	/**
	 * Generic setter.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @throws Exception\NoSetter
	 * @return void
	 */
	protected function _set($name, $value) {
		$objectVars = get_object_vars($this);
		$setter = 'set' . ucfirst(self::$__filterCase->filter($name));
		if (method_exists($this, $setter)) {
			$this->$setter($value);
		} elseif (array_key_exists($name, $objectVars)) {
			$this->$name = $value;
		} elseif ($this->__throwNoSetterExceptions) {
			throw new Exception\NoSetter(
				"field '{$name}' is not accessible and no setter was found 
						- add {$setter}() perhaps?");
		}
	}

	/**
	 * Generic getter.
	 * 
	 * @param string $name
	 * @throws Exception\NoGetter
	 * @return mixed
	 */
	protected function _get($name) {
		$objectVars = get_object_vars($this);
		$getter = 'get' . ucfirst(self::$__filterCase->filter($name));
		if (method_exists($this, $getter)) {
			$value = $this->$getter();
		} elseif (array_key_exists($name, $objectVars)) {
			$value = $this->$name;
		} elseif ($this->__throwNoGetterExceptions) {
			throw new Exception\NoGetter(
				"field '{$name}' is not accessible and no getter was found 
					- add {$getter}() perhaps?");
		} else {
			$value = null;
		}

		return $value;
	}
}
