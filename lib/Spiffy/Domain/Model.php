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
            return;
        }

        if (null === self::$__filterCase) {
            self::$__filterCase = new Zend_Filter_Word_UnderscoreToCamelCase();
        }

        $reflClass = self::$__reflClass[get_called_class()] = new ReflectionClass(
            get_called_class());

        // all properties of the class used for toArray(), fromArray(), get(), and set()
        foreach ($reflClass->getProperties() as $property) {
            if (substr($property->name, 0, 2) == '__') {
                continue;
            }

            self::$__properties[get_called_class()][$property->name] = $property;
        }
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
        if (!isset(self::$__properties[$self][$key])) {
            throw new Exception\InvalidProperty("no such property '{$key}' exists for '{$self}'");
        }

        if (isset(self::$__filterable[$self][$key])) {
            return self::$__filterable[$self][$key]->filter($value);
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
        foreach (self::$__validatable[get_class($this)] as $name => $validatorChain) {
            $value = $this->_get($name);
            $isValid = $validatorChain->isValid($value);

            if (!$isValid) {
                $this->__messages = $validatorChain->getMessages();
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
            if (isset(self::$__properties[get_class($this)][$key])) {
                $this->_set($key, $value);
            }
        }
    }

    /**
     * Convert entity to an array.
     * 
     * @param array $properties Array of fields to filter results with.
     * @return array
     */
    public function toArray(array $properties = array())
    {
        static::__initialize();

        if (empty($properties)) {
            $properties = array_keys(self::$__properties[get_class($this)]);
        }

        $values = array();
        foreach ($properties as $property) {
            if (!isset(self::$__properties[get_class($this)][$property])) {
                continue;
            }

            $property = self::$__properties[get_class($this)][$property];
            $values[$property->name] = $this->_get($property->name);
        }
        return $values;
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
     * @throws Zend_Exception
     */
    protected static function _addFilter($property, $class, $options)
    {
        if (!isset(self::$__filterable[get_called_class()][$property]) || 
            null === self::$__filterable[get_called_class()][$property]
        ) {
            self::$__filterable[get_called_class()][$property] = new Zend_Filter();
        }

        try {
            Zend_Loader::loadClass($class);
        } catch (Zend_Exception $e) {
            throw new Zend_Exception("failed to find filter '{$class}'");
        }
        
        $filter = empty($options) ? $filter = new $class() : $filter = new $class($options); 

        self::$__filterable[get_called_class()][$property]->addFilter($filter);
    }

    /**
     * Statically adds a validator to a property.
     *
     * @param string $property
     * @param string $class
     * @param mixed $options
     * @param boolean $breakChain
     * @throws Zend_Exception
     */
    protected static function _addValidator($property, $class, $options, $breakChain = false)
    {
        if (!isset(self::$__validatable[get_called_class()][$property]) || 
            null === self::$__validatable[get_called_class()][$property]
        ) {
            self::$__validatable[get_called_class()][$property] = new Zend_Validate();
        }

        try {
            Zend_Loader::loadClass($class);
        } catch (Zend_Exception $e) {
            throw new Zend_Exception("failed to find validator '{$class}'");
        }
        
        $validator = empty($options) ? $validator = new $class() : $validator = new $class($options); 

        self::$__validatable[get_called_class()][$property]->addValidator($validator, $breakChain);
    }
}
