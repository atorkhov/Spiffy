<?php
namespace Spiffy;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Types\Type;
use Spiffy\Doctrine\Container as DoctrineContainer;
use Spiffy\Dojo\Form as SpiffyDojoForm;
use Zend_Dojo;
use Zend_Form_Exception;
use Zend_Filter_Word_UnderscoreToCamelCase;
use Zend_Form;
use Zend_Registry;

abstract class Form extends Zend_Form
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
     * Case conversion filter.
     * 
     * @var Zend_Filter_Word_UnderscoreToCamelCase
     */
    protected static $_caseFilter = null;

    /**
     * Doctrine 2 annotation reader.
     * 
     * @var Doctrine\Common\Annotations\AnnotationReader
     */
    protected static $_annotationReader = null;

    /**
     * Doctrine 2 entity, if any.
     * 
     * @var object
     */
    protected $_entity = null;

    /**
     * flag: automatically persist valid entities?
     *
     * @var boolean
     */
    protected $_automaticPersisting = false;

    /**
     * flag: use automatic filters?
     * 
     * @var boolean
     */
    protected $_automaticFilters = true;

    /**
     * flag: use automatic validators?
     * 
     * @var boolean
     */
    protected $_automaticValidators = true;

    /**
     * The default entity manager to use in case there are multiple
     * ones available. Set this using constructor options or the
     * getDefaultOptions() method.
     * 
     * @var string
     */
    protected $_defaultEntityManager = 'default';

    /**
     * The Spiffy service container. Only loaded if registered.
     * @var Spiffy_Application_Resource_Servicecontainer
     */
    protected $serviceContainer = null;

    /**
     * Default elements for Zend_Form.
     * 
     * @var array
     */
    protected $_defaultElements = array(
        Type::SMALLINT => 'text',
        Type::BIGINT => 'text',
        Type::INTEGER => 'text',
        Type::BOOLEAN => 'checkbox',
        Type::DATE => 'text',
        Type::DATETIME => 'text',
        Type::DATETIMETZ => 'text',
        Type::DECIMAL => 'text',
        Type::OBJECT => null,
        Type::TARRAY => null,
        Type::STRING => 'text',
        Type::TEXT => 'textarea',
        Type::TIME => 'text'
    );

    /**
     * Constructor
     *
     * @param object $entity
     * @param array|Zend_Config|null $options
     * @return void
     */
    public function __construct($entity = null, $options = null)
    {
        // spiffy class prefixes
        $this->addPrefixPath('Spiffy_Form_Decorator', 'Spiffy/Form/Decorator', 'decorator')
            ->addPrefixPath('Spiffy_Form_Element', 'Spiffy/Form/Element', 'element')
            ->addElementPrefixPath('Spiffy_Form_Decorator', 'Spiffy/Form/Decorator', 'decorator')
            ->addDisplayGroupPrefixPath('Spiffy_Form_Decorator', 'Spiffy/Form/Decorator')
            ->setDefaultDisplayGroupClass('Spiffy_Form_DisplayGroup');

        // enable view helpers
        $this->getView()->addHelperPath('Spiffy/View/Helper', 'Spiffy_View_Helper');

        // filter for setters/getters
        if (null === self::$_caseFilter) {
            self::$_caseFilter = new Zend_Filter_Word_UnderscoreToCamelCase();
        }

        // annotation reader
        if (null === self::$_annotationReader) {
            self::$_annotationReader = new AnnotationReader();
        }

        /*
         * Set the entity prior to loading options in case there's a default 
         * entity set. This way, the entity called on construction overwrites 
         * the entity set in getDefaultOptions().
         */
        if ($entity) {
            $this->setEntity($entity);
        }

        // assemble form
        $options = array_merge(is_array($options) ? $options : array(), $this->getDefaultOptions());
        parent::__construct($options);

        // set entity defaults if an entity is present
        $this->setDefaults($this->entityToArray());

        // register the service container if it's enabled
        $this->serviceContainer = Zend_Registry::get('Spiffy_Container')->getServiceContainer();
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
    public function add($name, $element = null, $options = null)
    {
        if (!$this->getEntity()) {
            throw new Zend_Form_Exception('add() can only be used with a set entity');
        }

        $md = $this->getEntityMetadata();
        if ($columnType = $md->getTypeOfField($name)) {
            $fieldMapping = $md->getFieldMapping($name);
            $refProperty = $md->getReflectionProperty($name);

            $filters = $this->_getPropertyAnnotations($refProperty, self::FILTER_NAMESPACE);
            foreach ($filters as $filter) {
                $class = str_replace('Zend_Filter_', '', $filter->class);
                $options['filters'][$class]['filter'] = $class;
                if (!empty($filter->value)) {
                    $options['filters'][$class]['options'] = $filter->value;
                }
            }

            $validators = $this->_getPropertyAnnotations($refProperty, self::VALIDATOR_NAMESPACE);
            foreach ($validators as $validator) {
                $class = str_replace('Zend_Validate_', '', $validator->class);
                $options['validators'][$class]['validator'] = $class;
                if (!empty($validator->value)) {
                    $options['validators'][$class]['options'] = $validator->value;
                }
            }

            // automatic filters based on column type
            if ($this->_automaticFilters) {
                $filters = array();
                switch ($columnType) {
                    case Type::SMALLINT:
                    case Type::INTEGER:
                    case Type::BIGINT:
                        $filters[] = 'Int';
                        break;
                    case Type::BOOLEAN:
                        $filters[] = 'Boolean';
                        break;
                    case Type::TEXT:
                    case Type::STRING:
                        $filters[] = 'StringTrim';
                        break;
                }

                foreach ($filters as $filter) {
                    if (!isset($options['filters'][$filter])) {
                        $options['filters'][$filter]['filter'] = $filter;
                    }
                }
            }

            // automatic validators based on column type
            if ($this->_automaticValidators) {
                if (!$fieldMapping['nullable']) {
                    $options['required'] = true;
                }

                $validators = array();
                switch ($columnType) {
                    case Type::STRING:
                        if ($fieldMapping['length']) {
                            $validators[] = array(
                                'name' => 'StringLength',
                                'options' => array(
                                    'max' => $fieldMapping['length']
                                )
                            );
                        }
                        break;
                }

                foreach ($validators as $validator) {
                    $vname = $validator['name'];
                    $voptions = isset($validator['options']) ? $validator['options'] : array();

                    if (!isset($options['validators'][$vname])) {
                        $options['validators'][$vname]['validator'] = $vname;
                        $options['validators'][$vname]['options'] = $voptions;
                    }
                }
            }

            if (!isset($options['label'])) {
                $options['label'] = ucfirst($name);
            }

            if (null === $element && isset($this->_defaultElements[$columnType])) {
                $element = $this->_defaultElements[$columnType];
            }
        }

        if (!$element) {
            throw new Zend_Form_Exception(
                "element type was not specified for '{$name}' and could not be guessed");
        }

        parent::addElement($element, $name, $options);
    }

    /**
     * Setter for default entity manager.
     * 
     * @param string $emName
     */
    public function setDefaultEntityManager($emName)
    {
        $this->_defaultEntityManager = $emName;
    }

    /**
     * Get the entity manager.
     * 
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager($emName = null)
    {
        $emName = $emName ? $emName : $this->_defaultEntityManager;
        return DoctrineContainer::getEntityManager($emName);
    }

    /**
     * Getter for entity metadata.
     *
     * @return Doctrine\ORM\Mapping\ClassMetadata
     */
    public function getEntityMetadata()
    {
        $em = $this->getEntityManager();
        return $this->getEntityManager()->getClassMetadata(get_class($this->getEntity()));
    }

    /**
     * Sets automatic validators.
     * 
     * @param boolean $value
     */
    public function setAutomaticValidators($value)
    {
        $this->_automaticValidators = $value;
    }

    /**
     * Sets automatic filters.
     *
     * @param boolean $value
     */
    public function setAutomaticFilters($value)
    {
        $this->_automaticFilters = $value;
    }

    /**
     * Sets automatic validators.
     *
     * @param boolean $value
     */
    public function setAutomaticPersisting($value)
    {
        $this->_automaticPersisting = $value;
    }

    /**
     * Getter for entity.
     * 
     * @return object
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Setter for entity.
     * 
     * @param string|object $entity
     */
    public function setEntity($entity)
    {
        if (is_object($entity)) {
            ;
        } elseif (is_string($entity)) {
            $entity = new $entity();
        } else {
            throw new Zend_Form_Exception('Unknown input for setEntity()');
        }
        $this->_entity = $entity;
    }

    /**
     * Populates an entity from an array. If the entity has a fromArray() method
     * it is used. Otherwise, metadata is used to populate the entity
     * fields.
     *
     * Requires setters if fromArray is not present!
     *
     * @throws Zend_Form_Exception
     * @return void
     */
    public function setEntityDefaults(array $data)
    {
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
                throw new Zend_Form_Exception(
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
    public function entityToArray()
    {
        if (!$this->getEntity()) {
            return array();
        }

        if (method_exists($this->getEntity(), 'toArray')) {
            return $this->getEntity()->toArray(array_keys($this->getElements()));
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
                throw new Zend_Form_Exception(
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
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Form::isValid()
     */
    public function isValid($data)
    {
        $valid = parent::isValid($data);

        $this->setEntityDefaults($this->getValues());
        
        if ($this->_automaticPersisting) {
            $invalid = false;
            foreach ($this->getElements() as $element) {
                if ($invalid = $element->hasErrors()) {
                    break;
                }
            }
            
            $invalid = ($this->isErrors() || $invalid);
            if (!$invalid) {
                $this->getEntityManager()->persist($this->getEntity());
                $this->getEntityManager()->flush();
            }
        }

        return $valid;
    }

    /**
     * Returns the annotations for a given property.
    
     * @param ReflectionClass $property
     * @param null|string $namespace
     */
    protected function _getPropertyAnnotations($property, $namespace = null)
    {
        $annotations = array();
        $reader = self::$_annotationReader;

        foreach ($reader->getPropertyAnnotations($property) as $annotation) {
            if ($annotation instanceof $namespace) {
                $annotations[] = $annotation;
            }
        }
        return $annotations;
    }
}
