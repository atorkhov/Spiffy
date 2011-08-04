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
 * @package    Spiffy_Doctrine
 * @copyright  Copyright (c) 2011 Kyle Spraggs (http://www.spiffyjr.me)
 * @license    http://www.spiffyjr.me/license     New BSD License
 */

namespace Spiffy\Doctrine;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Types\Type;
use Spiffy\Domain\Exception\InvalidProperty;
use Spiffy\Domain\Model;
use Spiffy\Doctrine\Container;
use Zend_Registry;

class Entity extends Model
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
     * Doctrine annotation reader
     * @var Doctrine\Common\Annotations\AnnotationReader
     */
    protected static $__annotationReader = null;

    /**
     * flag: enable automatic filter?
     * @var boolean
     */
    protected $__automaticFilters = true;

    /**
     * flag: enable automatic validators?
     * @var boolean
     */
    protected $__automaticValidators = true;

    /**
     * Initialize the entity. This is done to cache the properties so they
     * only have to be initialized once.
     */
    protected static function __initialize()
    {
        if (false === parent::__initialize()) {
            return false;
        }

        if (null === self::$__annotationReader) {
            self::$__annotationReader = new AnnotationReader();
        }

        $reader = self::$__annotationReader;
        $entityManager = Zend_Registry::get('Spiffy_Doctrine')->getEntityManager();
        $metadata = $entityManager->getClassMetadata(get_called_class());

        // all properties of the class used for toArray(), fromArray(), get(), and set()
        foreach ($metadata->getReflectionProperties() as $property) {
            if (substr($property->name, 0, 2) == '__') {
                continue;
            }

            if (isset($metadata->fieldMappings[$property->name])) {
                $fieldMapping = $metadata->fieldMappings[$property->name];
                self::$__properties[get_called_class()][$property->name] = $fieldMapping;

                // automatic filters
                switch ($fieldMapping['type']) {
                    case Type::SMALLINT:
                    case Type::INTEGER:
                    case Type::BIGINT:
                        self::_addFilter($property->name, 'Zend_Filter_Int', null, true);
                        break;
                    case Type::BOOLEAN:
                        self::_addFilter($property->name, 'Zend_Filter_Boolean', null, true);
                        break;
                    case Type::TEXT:
                    case Type::STRING:
                        self::_addFilter($property->name, 'Zend_Filter_StringTrim', null, true);
                        break;
                }

                // automatic validators
                if (false === $fieldMapping['nullable']) {
                    self::_addValidator($property->name, 'Zend_Validate_NotEmpty', null, false,
                    true);
                }

                switch ($fieldMapping['type']) {
                    case Type::STRING:
                        if ($fieldMapping['length']) {
                            self::_addValidator($property->name, 'Zend_Validate_StringLength',
                            array('max' => $fieldMapping['length']), false, true);
                        }
                        break;
                }
            }

            // annotation filters
            if ($annotations = self::_getPropertyAnnotations($property, self::FILTER_NAMESPACE)) {
                foreach ($annotations as $annotation) {
                    self::_addFilter($property->name, $annotation->class, $annotation->value);
                }
            }

            // annotation validators
            if ($annotations = self::_getPropertyAnnotations($property, self::VALIDATOR_NAMESPACE)) {
                foreach ($annotations as $annotation) {
                    self::_addValidator($property->name, $annotation->class, $annotation->value,
                    $annotation->breakChain);
                }
            }
        }
    }

    /**
     * Returns the annotations for a given property.

     * @param ReflectionClass $property
     * @param null|string $namespace
     */
    protected static function _getPropertyAnnotations($property, $namespace = null)
    {
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
     * Enable or disable automatic filters.
     *
     * @param boolean $flag
     */
    public function setAutomaticFilters($flag)
    {
        $this->__automaticFilters = (bool) $flag;
    }

    /**
     * Are automatic filters enabled?
     *
     * @return boolean
     */
    public function isAutomaticFilters()
    {
        return $this->__automaticFilters;
    }

    /**
     * Enable or disable automatic validators.
     *
     * @param boolean $flag
     */
    public function setAutomaticValidators($flag)
    {
        $this->__automaticValidators = (bool) $flag;
    }

    /**
     * Are automatic validators enabled?
     *
     * @return boolean
     */
    public function isAutomaticValidators()
    {
        return $this->__automaticValidators;
    }

    /**
     * Generic getter that applies filtering with optional automatic filters.
     *
     * @param string $key
     */
    public function filter($key, $value)
    {
        static::__initialize();

        $self = get_class($this);
        if (!self::classPropertyExists($key)) {
            throw new InvalidProperty("no such property '{$key}' exists for '{$self}'");
        }

        if (isset(self::$__filterable[$self][$key])) {
            $filterChain = self::$__filterable[$self][$key]['chain'];

            if ($this->isAutomaticFilters() && isset(self::$__filterable[$self][$key]['automatic'])) {
                foreach (self::$__filterable[$self][$key]['automatic'] as $filter) {
                    $filterChain->addFilter($filter);
                }
            }
            return $filterChain->filter($value);
        }
        return $value;
    }

    /**
     * Validation checker with optional automatic validators.
     *
     * @todo Should I handle associationMappings?
     * @return boolean
     */
    public function isValid()
    {
        static::__initialize();

        $valid = true;
        foreach (self::$__validatable[get_class($this)] as $name => $data) {
            $validatorChain = $data['chain'];

            if ($this->isAutomaticValidators() && isset($data['automatic'])) {
                foreach ($data['automatic'] as $automatic) {
                    $validatorChain->addValidator(
                        $automatic['validator'],
                        $automatic['breakOnChain']
                    );
                }
            }

            $value = $this->_get($name);
            $isValid = $validatorChain->isValid($value);

            if (!$isValid) {
                $this->__messages[$name] = $validatorChain->getMessages();
                $valid = false;
            }
        }

        return $valid;
    }
}
