<?php
/**
 * Spiffy Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.spiffyjr.me/license
 *
 * @category   Spiffy
 * @package    Spiffy_Domain
 * @copyright  Copyright (c) 2011 Kyle Spraggs (http://www.spiffyjr.me)
 * @license    http://www.spiffyjr.me/license     New BSD License
 */

namespace Spiffy\Domain;
use ReflectionClass;
use Zend_Filter;
use Zend_Filter_Word_UnderscoreToCamelCase;
use Zend_Loader;
use Zend_Validate;

class Model
{
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
    public function setThrowNoGetterExceptions($value)
    {
        $this->__throwNoGetterExceptions = $value;
    }

    /**
     * Set throwNoSetterExceptions.
     *
     * @param boolean $value
     */
    public function setThrowNoSetterExceptions($value)
    {
        $this->__throwNoSetterExceptions = $value;
    }

    /**
     * Initialize the entity. This is done to cache the properties so they
     * only have to be initialized once.
     */
    protected static function __initialize()
    {
        if (isset(self::$__properties[get_called_class()])) {
            return false;
        }

        if (null === self::$__filterCase) {
            self::$__filterCase = new Zend_Filter_Word_UnderscoreToCamelCase();
        }

        $reflClass = self::$__reflClass[get_called_class()] = new ReflectionClass(get_called_class());

        // all properties of the class used for toArray(), fromArray(), get(), and set()
        foreach ($reflClass->getProperties() as $property) {
            if (substr($property->name, 0, 2) == '__') {
                continue;
            }

            self::$__properties[get_called_class()][$property->name] = $property->name;
        }
    }

    /**
     * Gets a class property.
     *
     * @param string $property
     * @return string
     */
    public static function getClassProperty($property)
    {
        $self = get_called_class();
        if (!self::classPropertyExists($property)) {
            throw new Exception\InvalidProperty("no such property '{$property}' exists for '{$self}'");
        }
        return self::$__properties[$self][$property];
    }

    /**
     * Gets class filterables (initialized filter chains).
     * 
     * @return array
     */
    public static function getClassFilterables()
    {
        return self::$__filterable[get_called_class()];
    }
    
    /**
    * Gets class validatables (initialized validator chains).
    *
    * @return array
    */
    public static function getClassValidatables()
    {
        return self::$__validatable[get_called_class()];
    }
    
    /**
     * Checks if a class property exists.
     *
     * @param string $property
     * @throws Exception\InvalidProperty
     */
    public static function classPropertyExists($property)
    {
        static::__initialize();

        $self = get_called_class();
        if (!array_key_exists($property, self::$__properties[$self])) {
            return false;
        }

        return true;
    }

    /**
     * Returns the error messages for the last validation attempt.
     *
     * @return array
     */
    public function getValidatorMessages()
    {
        return $this->__messages;
    }

    /**
     * Generic getter that applies filtering.
     *
     * @param string $key
     */
    public function filter($key, $value)
    {
        static::__initialize();

        $self = get_class($this);
        if (!self::classPropertyExists($key)) {
            throw new Exception\InvalidProperty("no such property '{$key}' exists for '{$self}'");
        }

        if (isset(self::$__filterable[$self][$key])) {
            return self::$__filterable[$self][$key]['chain']->filter($value);
        }
        return $value;
    }

    /**
     * Checks the entities validity.
     *
     * @return boolean
     */
    public function isValid()
    {
        static::__initialize();

        $valid = true;
        foreach (self::$__validatable[get_class($this)] as $name => $data) {
            $validatorChain = $data['chain'];

            $value = $this->_get($name);
            $isValid = $validatorChain->isValid($value);

            if (!$isValid) {
                $this->__messages[$name] = $validatorChain->getMessages();
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Set entity values from an array.
     *
     * @param array $data
     * @return void
     */
    public function fromArray(array $data)
    {
        static::__initialize();

        foreach ($data as $key => $value) {
            if (in_array($key, self::$__properties[get_class($this)])) {
                $this->_set($key, $value);
            }
        }
    }

    /**
     * Convert entity to an array.
     *
     * @param array $properties Array of fields to filter results with.
     * @param boolean $filter Whether or not to apply filtering to the result.
     * @return array
     */
    public function toArray(array $properties = array(), $filter = true)
    {
        static::__initialize();

        if (empty($properties)) {
            $properties = array_keys(self::$__properties[get_class($this)]);
        }

        $values = array();
        foreach ($properties as $property) {
            if (!self::classPropertyExists($property)) {
                continue;
            }

            $values[$property] = $this->_get($property);

            if ($filter) {
                $values[$property] = $this->filter($property, $values[$property]);
            }
        }
        return $values;
    }

    /**
     * Applies filters by calling fromArray() using toArray() with filters enabled.
     */
    public function applyFilters()
    {
        $get = $this->__throwNoGetterExceptions;
        $set = $this->__throwNoSetterExceptions;

        $this->setThrowNoGetterExceptions(false);
        $this->setThrowNoSetterExceptions(false);

        $this->fromArray($this->toArray());

        $this->setThrowNoGetterExceptions($get);
        $this->setThrowNoSetterExceptions($set);
    }

    /**
     * Generic setter.
     *
     * @param string $name
     * @param mixed $value
     * @throws Exception\NoSetter
     * @return void
     */
    protected function _set($name, $value)
    {
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
    protected function _get($name)
    {
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

    /**
     * Statically adds a filter to a property.
     *
     * @param string $property
     * @param string $class
     * @param mixed $options
     * @param boolean $automatic
     * @throws Zend_Exception
     */
    protected static function _addFilter($property, $class, $options = null, $automatic = false)
    {
        if (!isset(self::$__filterable[get_called_class()][$property]) || 
            null === self::$__filterable[get_called_class()][$property]['chain']
        ) {
            self::$__filterable[get_called_class()][$property]['chain'] = new Zend_Filter();
        }

        try {
            Zend_Loader::loadClass($class);
        } catch (Zend_Exception $e) {
            throw new Zend_Exception("failed to find filter '{$class}'");
        }

        $filter = empty($options) ? $filter = new $class() : $filter = new $class($options);

        if ($automatic) {
            self::$__filterable[get_called_class()][$property]['automatic'][] = $filter;
        } else {
            self::$__filterable[get_called_class()][$property]['chain']->addFilter($filter);
        }
    }

    /**
     * Statically adds a validator to a property.
     *
     * @param string $property
     * @param string $class
     * @param mixed $options
     * @param boolean $breakChain
     * @param boolean $automatic
     * @throws Zend_Exception
     */
    protected static function _addValidator($property, $class, $options = null, $breakChain = false,
        $automatic = false
    )
    {
        if (!isset(self::$__validatable[get_called_class()][$property]) || 
            null === self::$__validatable[get_called_class()][$property]['chain']
        ) {
            self::$__validatable[get_called_class()][$property]['chain'] = new Zend_Validate();
        }

        try  {
            Zend_Loader::loadClass($class);
        } catch (Zend_Exception $e) {
            throw new Zend_Exception("failed to find validator '{$class}'");
        }

        $validator = empty($options) ? $validator = new $class() : $validator = new $class($options);

        if ($automatic) {
            self::$__validatable[get_called_class()][$property]['automatic'][] = array(
                'validator' => $validator, 'breakOnChain' => $breakChain
            );
        } else {
            self::$__validatable[get_called_class()][$property]['chain']
                ->addValidator($validator, $breakChain);
        }
    }
}
