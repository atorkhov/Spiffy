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
     * Initialize the entity. This is done to cache the properties so they
     * only have to be initialized once.
     */
    protected static function __initialize()
    {
        parent::__initialize();

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

            if ($annotations = self::_getPropertyAnnotations($property, self::FILTER_NAMESPACE)) {
                foreach ($annotations as $annotation) {
                    self::_addFilter($property->name, $annotation->class, $annotation->value);
                }
            }

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
}
