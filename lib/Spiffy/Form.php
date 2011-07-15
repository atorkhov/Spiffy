<?php
namespace Spiffy;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Spiffy\Dojo\Form as SpiffyDojoForm;
use Zend_Dojo;
use Zend_Exception;
use Zend_Filter_Word_UnderscoreToCamelCase;
use Zend_Form;

abstract class Form extends Zend_Form
{
	/**
	 * Default entity manager if one is not specified.
	 * 
	 * @var EntityManager
	 */
	protected static $_defaultEntityManager = null;

	/**
	 * Case conversion filter.
	 * 
	 * @var Zend_Filter_Word_UnderscoreToCamelCase
	 */
	protected static $_caseFilter = null;

	/**
	 * Instance based entity manager.
	 * 
	 * @var EntityManager
	 */
	protected $_entityManager = null;

	/**
	 * Doctrine 2 entity, if any.
	 * 
	 * @var object
	 */
	protected $_entity = null;

	/**
	 * Doctrine 2 annotations reader.
	 * 
	 * @var AnnotationRedaer
	 */
	protected $_reader = null;

	/**
	 * Validator namespace for Spiffy Doctrine 2 annotations.
	 * 
	 * @var string
	 */
	protected $_zendValidatorNamespace = 'Spiffy\\Doctrine\\Annotations\\Zend\\Validator';

	/**
	 * Default elements for Zend_Form.
	 * 
	 * @var array
	 */
	protected $_defaultElements = array(
		'smallint' => 'text',
		'datetime' => 'text',
		'integer' => 'text',
		'boolean' => 'checkbox',
		'string' => 'text',
		'text' => 'textarea');

	/**
	 * Constructor
	 *
	 * @param object $entity
	 * @param array|Zend_Config|null $options
	 * @return void
	 */
	public function __construct($entity = null, $options = null) {
		// spiffy class prefixes
		$this->addPrefixPath('Spiffy_Form_Decorator', 'Spiffy/Form/Decorator', 'decorator')
			->addPrefixPath('Spiffy_Form_Element', 'Spiffy/Form/Element', 'element')
			->addElementPrefixPath('Spiffy_Form_Decorator', 'Spiffy/Form/Decorator', 'decorator')
			->addDisplayGroupPrefixPath('Spiffy_Form_Decorator', 'Spiffy/Form/Decorator')
			->setDefaultDisplayGroupClass('Spiffy_Form_DisplayGroup');

		// enable view helpers
		$this->getView()->addHelperPath('Spiffy/View/Helper', 'Spiffy_View_Helper');

		// filter for setters/getters
		self::$_caseFilter = new Zend_Filter_Word_UnderscoreToCamelCase();

		// set the entity prior to loading options in case there's a default entity set
		// this way, the entity called on construction overwrites the entity set in getDefaultOptions()
		if ($entity) {
			$this->setEntity($entity);
		}

		// used to read validation annotations if they exist
		$this->_reader = new AnnotationReader();

		// assemble form
		$options = array_merge(is_array($options) ? $options : array(), $this->getDefaultOptions());
		parent::__construct($options);

		// set entity defaults if an entity is present
		$this->setDefaults($this->entityToArray());
	}

	/**
	 * Add a new element
	 *
	 * @param  string $name
	 * @param  null|string|Zend_Form_Element $element
	 * @param  array|Zend_Config $options
	 * @throws Zend_Form_Exception on invalid element
	 * @return Zend_Form
	 */
	public function add($name, $element = null, $options = null) {
		if ($entity = $this->getEntity()) {
			$md = $this->getEntityMetadata();

			if (isset($md->reflFields[$name])) {
				if ($annotations = $this->_reader->getPropertyAnnotations($md->reflFields[$name])) {
					foreach ($annotations as $index => $annotation) {
						if (!$annotation instanceof $this->_zendValidatorNamespace) {
							continue;
						}

						$validator = str_replace('Spiffy\\Doctrine\\Annotations\\Zend\\', '',
							get_class($annotation));
						$options['validators'][$index]['validator'] = $validator;
						if (!empty($annotation->value)) {
							$options['validators'][$index]['options'] = $annotation->value;
						}
					}
				}
			}

			$map = null;
			if (isset($md->fieldMappings[$name])) {
				$map = $md->fieldMappings[$name];
			} elseif (isset($md->associationMappings[$name])) {
				$map = $md->associationMappings[$name];
			}

			if ($map) {
				$type = $map['type'];

				if (isset($map['nullable']) && (false === $map['nullable'])) {
					$options['required'] = true;
					if (!isset($options['missingMessage']) && $this instanceof SpiffyDojoForm) {
						$options['missingMessage'] = "{$name} is required and cannot be empty";
					}
				}

				if (!$element) {
					$element = isset($this->_defaultElements[$type]) ? $this
							->_defaultElements[$type] : null;
				}

				if (!isset($options['label'])) {
					$options['label'] = ucfirst($name);
				}
			}
		}

		if (!$element) {
			throw new Zend_Exception(
				"element type was not specified for '{$name}' and could not be guessed");
		}

		parent::addElement($element, $name, $options);
	}

	/**
	 * Getter for entity metadata.
	 *
	 * @return Doctrine\ORM\Mapping\ClassMetadata
	 */
	public function getEntityMetadata() {
		return $this->getEntityManager()->getClassMetadata(get_class($this->getEntity()));
	}

	/**
	 * Getter for entity.
	 * 
	 * @return object
	 */
	public function getEntity() {
		return $this->_entity;
	}

	/**
	 * Setter for entity.
	 * 
	 * @param string|object $entity
	 */
	public function setEntity($entity) {
		if (is_object($entity)) {
			;
		} elseif (is_string($entity)) {
			$entity = new $entity();
		} else {
			throw new Zend_Exception('Unknown input for setEntity()');
		}
		$this->_entity = $entity;
	}

	/**
	 * Getter for default entity manager.
	 * 
	 * @return EntityManager
	 */
	public static function getDefaultEntityManager() {
		return self::$_defaultEntityManager;
	}

	/**
	 * Setter for default entity manager.
	 * 
	 * @param EntityManager $em
	 */
	public static function setDefaultEntityManager(EntityManager $em) {
		self::$_defaultEntityManager = $em;
	}

	/**
	 * Getter for entity manager.
	 * 
	 * @return EntityManager
	 */
	public function getEntityManager() {
		$em = $this->_entityManager;
		if (null === $this->_entityManager) {
			if (null === self::getDefaultEntityManager()) {
				throw new Zend_Exception('entity manager was not set');
			}
			$em = self::getDefaultEntityManager();
		}
		return $em;
	}

	/**
	 * Setter for entity manager
	 * 
	 * @param EntityManager $em
	 */
	public function setEntityManager(EntityManager $em) {
		$this->_entityManager = $em;
	}

	/**
	 * Populates an entity from an array. If the entity has a fromArray() method
	 * it is used. Otherwise, metadata is used to populate the entity
	 * fields.
	 *
	 * Requires setters if fromArray is not present!
	 *
	 * @throws Zend_Exception
	 * @return void
	 */
	public function setEntityDefaults(array $data) {
		if (!$this->getEntity()) {
			return;
		}

		if (method_exists($this->getEntity(), 'fromArray')) {
			$this->getEntity()->fromArray($data);
			return;
		}

		$em = $this->getEntityManager();
		$md = $this->getEntityMetadata();

		foreach ($data as $key => $value) {
			if (isset($md->fieldNames[$key])) {
				;
			} elseif (isset($md->associationMappings[$key])) {
				$mapping = $md->associationMappings[$key];
				$value = $em->getReference($mapping['targetEntity'], $value);
			} else {
				continue;
			}

			$setter = 'set' . ucfirst(self::$_caseFilter->filter($key));
			if (method_exists($this->getEntity(), $setter)) {
				$this->getEntity()->$setter($value);
			} elseif (isset($this->{$key}) || property_exists($this->getEntity(), $key)) {
				$this->getEntity()->$key = $value;
			} else {
				throw new Zend_Exception(
					"property '{$key}' is not public: perhaps add {$setter}()?");
			}
		}
	}

	/**
	 * Converts the entity object into an array. If the entity has a toArray() method
	 * it is used. If not, metadata is used to convert the entity to an array.
	 *
	 * Requires getters if toArray is not present!
	 * 
	 * @return array
	 */
	public function entityToArray() {
		if (!$this->getEntity()) {
			return array();
		}

		if (method_exists($this->getEntity(), 'toArray')) {
			return $this->getEntity()->toArray();
		}

		$md = $this->getEntityMetadata();

		$output = array();
		foreach ($md->fieldNames as $field) {
			if (!$this->getElement($field)) {
				continue;
			}

			$getter = 'get' . ucfirst(self::$_caseFilter->filter($field));
			if (method_exists($this->getEntity(), $getter)) {
				$value = $this->getEntity()->$getter();
			} elseif (isset($this->{$key}) || property_exists($this->getEntity(), $key)) {
				$value = $this->getEntity()->$key;
			} else {
				throw new Zend_Exception(
					"property '{$field}' is not public: perhaps add {$getter}()?");
			}
			$output[$field] = $value;
		}

		return $output;
	}

	/**
	 * Gets the default options for the form.
	 * 
	 * @return array
	 */
	public function getDefaultOptions() {
		return array();
	}

	/**
	 * (non-PHPdoc)
	 * @see Zend_Form::setOptions()
	 */
	public function setOptions(array $options) {
		foreach ($options as $key => $value) {
			switch ($key) {
				case 'entity':
					if (!$this->getEntity()) {
						$this->setEntity($value);
					}
					break;
				default:
					throw new Zend_Exception("unknown option: '{$key}'");
			}

			parent::setOptions($options);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Zend_Form::isValid()
	 */
	public function isValid($data) {
		$this->setEntityDefaults($data);

		if (($valid = parent::isValid($data)) && $this->_autoPersist) {
			$this->getEntityManager()->persist($this->getEntity());
		}

		return $valid;
	}
}
