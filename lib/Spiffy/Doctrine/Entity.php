<?php
namespace Spiffy\Doctrine;
use Doctrine\Common\Annotations\AnnotationReader;
use Spiffy\Domain\Model;
use Spiffy\Doctrine\Container;
use Zend_Filter_Word_UnderscoreToCamelCase;

class Entity extends Model
{
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
		$entityManager = Container::getEntityManager();
		$metadata = $entityManager->getClassMetadata(get_called_class());

		// all properties of the class used for toArray(), fromArray(), get(), and set()
		foreach ($metadata->getReflectionProperties() as $property) {
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
}
