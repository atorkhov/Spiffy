<?php 
namespace Spiffy\Zend;
use Doctrine\DBAL\Types\Type,
    Doctrine\ORM\Mapping\ClassMetadataInfo,
    Spiffy\Doctrine\AbstractEntity,
    Zend_Dojo,
    Zend_Form,
    Zend_Registry;

class Form extends Zend_Form
{
    /**
     * flag: is this form dojo enabled?
     * @var boolean
     */
    protected $_dojoEnabled = false;
    
    /**
     * Attached entity instance.
     * @var Spiffy\Doctrine\AbstractEntity
     */
    protected $_entity;
    
    /**
     * Default elements for Zend_Form.
     * @var array
     */
    protected $_defaultElements = array(
        Type::SMALLINT      => 'text',
        Type::BIGINT        => 'text',
        Type::INTEGER       => 'text',
        Type::BOOLEAN       => 'checkbox',
        Type::DATE          => 'text',
        Type::DATETIME      => 'text',
        Type::DATETIMETZ    => 'text',
        Type::DECIMAL       => 'text',
        Type::OBJECT        => null,
        Type::TARRAY        => null,
        Type::STRING        => 'text',
        Type::TEXT          => 'textarea',
        Type::TIME          => 'text',
        'TO_ONE'            => 'entity',
        'TO_MANY'			=> 'entity'
    );
    
    /**
    * Default elements for Zend_Dojo_Form.
    * @var array
    */
    protected $_defaultDojoElements = array(
        Type::SMALLINT      => 'NumberSpinner',
        Type::BIGINT        => 'NumberSpinner',
        Type::INTEGER       => 'NumberSpinner',
        Type::BOOLEAN       => 'CheckBox',
        Type::DATE          => 'DateTextBox',
        Type::DATETIME      => 'DateTextBox',
        Type::DATETIMETZ    => 'DateTextBox',
        Type::DECIMAL       => 'NumberSpinner',
        Type::OBJECT        => null,
        Type::TARRAY        => null,
        Type::STRING        => 'TextBox',
        Type::TEXT          => 'Textarea',
        Type::TIME          => 'TimeTextBox',
        'TO_ONE'            => 'Entity',
        'TO_MANY' 			=> 'Entity'
    );
    
    /**
     * Constructor.
     * 
     * @param string|object $entity
     * @param array|Zend_Config|null $options
     */
    public function __construct($entity = null, array $options = array())
    {
        $this->addPrefixPath(
        	'Spiffy_Zend_Form_Element',
        	'Spiffy/Zend/Form/Element', 
        	'element'
        );
        
        if ($entity) {
            $options['entity'] = $entity;
        }
        $defaultOptions = $this->getDefaultOptions();
        if (!is_array($defaultOptions)) {
            $defaultOptions = array();
        }
        $options = array_merge($defaultOptions, $options);
        
        parent::__construct($options);
        
        $this->_setDefaultsFromEntity();
    }
    
    /**
     * Adds an element to the form using metadata information to guess
     * element type and base parameters. Options are passed directly to 
     * addElement() so this method can be bypassed entirely when not needed.
     *  
     * @param string $name
     * @param string $element
     * @param array $options
     * @throws Form\Exception\NoFormElement
     */
    public function add($name, $element = null, array $options = array())
    {
        $entity = isset($options['entity']) ? $this->_normEntity($options['entity']) : $this->getEntity();
        $property = isset($options['property']) ? $options['property'] : $name;
        
        if ($entity) {
            $mapping = null;
            $metadata = $entity->getClassMetadata();
            
            if (isset($metadata->fieldMappings[$property])) {
                $mapping = $metadata->getFieldMapping($property); 
            } elseif (isset($metadata->associationMappings[$property])) {
                $mapping = $metadata->getAssociationMapping($property);
            }
            
            if (!$element || strtolower($element) == 'entity') {
                if ($mapping['type'] & ClassMetadataInfo::TO_ONE) {
                    if (!$element) {
                        $element = $this->_getDefaultElement('TO_ONE');
                    }
                    if (!isset($options['class'])) {
                        $options['class'] = $mapping['targetEntity'];
                    }
                } else if ($mapping['type'] & ClassMetadataInfo::TO_MANY) {
                    if (!$element) {
                        $element = $this->_getDefaultElement('TO_MANY');
                    }
                    if (!isset($options['class'])) {
                        $options['class'] = $mapping['targetEntity'];
                    }
                    $options['many'] = true;
                } else if (!$element) {
                    if (!$element) {
                        $element = $this->_getDefaultElement($mapping['type']);
                    }
                }
            }
            
            $options['filters'] = $entity->getPropertyFilters($property);
            $options['validators'] = $entity->getPropertyValidators($property);
            
            if (!isset($options['required'])) {
                if (in_array('NotEmpty', $options['validators'])) {
                    $options['required'] = true;
                } else {
                    foreach($options['validators'] as $validator) {
                        if (is_array($validator) && $validator['validator'] == 'NotEmpty') {
                            $options['required'] = true;
                            break;
                        }
                    }
                }
            }
        }
        
        if (!$element) {
            throw new Form\Exception\NoFormElement(
                sprintf(
                	'No form element was specified for "%s" and one not be determined automatically' . 
                	' from "%s".',
                	$name,
                	get_class($entity)
            	)
            );
        }
        
        if ($element === 'hidden') {
            $options['decorators'] = array(
            	'ViewHelper'
            );
        }
        
        if (!array_key_exists('label', $options) && $element != 'hidden') {
            $options['label'] = ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $name));
        }
        
        $this->addElement($element, $name, $options);
    }
    
    /**
     * Quick access to Spiffy_Service::get if registered.
     * 
     * @param string $service
     * @return object
     */
    public function get($service)
    {
        if (Zend_Registry::isRegistered('Spiffy_Service')) {
            return Zend_Registry::get('Spiffy_Service')->get($service);
        }
        throw new Form\Exception\ServiceNotRegistered('Spiffy_Service is not registered');
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Form::isValid()
     */
    public function isValid($data)
    {
       $valid = parent::isValid($data);

       if ($this->getEntity()) {
           $this->getEntity()->fromArray($this->getValues());
       }
       
       return $valid;
    }
    
    /**
     * Save the form.
     * 
     * @param boolean $flush
     * @return boolean
     */
    public function save(array $data, $flush = true)
    {
        if (!$this->isValid($data)) {
            return false;
        }
        
        exit;
        
        return $this->getEntity()->save($flush);
    }
    
    /**
     * Default options for this form. All options specified
     * here can be overridden via the constructor.
     * 
     * @return array
     */
    public function getDefaultOptions()
    {
        return array();
    }
    
    /**
     * Gets the attached entity instance.
     * 
     * @return Spiffy\Doctrine\AbstractEntity
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Set the dojo enabled flag. Once set, there is no way
     * to clear it.
     */
    public function setDojoEnabled()
    {
        Zend_Dojo::enableForm($this);
        
        $this->addPrefixPath(
        	'Spiffy_Zend_Dojo_Form_Element',
        	'Spiffy/Zend/Dojo/Form/Element', 
        	'element'
        );
        
        $this->_dojoEnabled = true;
    }
    
    /**
     * Sets the attached entity instance.
     *
     * @param string|Spiffy\Doctrine\AbstractEntity $entity
     */
    public function setEntity($entity)
    {
        $this->_entity = $this->_normEntity($entity);
        $this->_setDefaultsFromEntity();
    }
    
    /**
     * Normalizes an entity and returns AbstractEntity.

     * @param string|AbstractEntity $entity
     * @return AbstractEntity
     */
    protected function _normEntity($entity)
    {
        if (is_string($entity)) {
            $entity = new $entity();
        }
        
        if (!$entity instanceof AbstractEntity) {
            throw new Form\Exception\InvalidEntity(
            	'setEntity() expects instance of Spiffy\Doctrine\AbstractEntity'
            );
        }
        
        return $entity;
    }
    
    /**
     * Set form defaults from entity.
     */
    protected function _setDefaultsFromEntity()
    {
        if ($this->getEntity()) {
            // handles standard fields and ToOne
            $defaults = $this->getEntity()->toArray(false);
            $this->setDefaults($defaults);
            
            // custom code to handle ToMany fields
            $mdata = $this->getEntity()->getClassMetadata();
            foreach($mdata->associationMappings as $assName => $assMap) { // lulz, love assMaps
                if (($element = $this->getElement($assName)) &&    
                    ($assMap['type'] & ClassMetadataInfo::TO_MANY)
                ) {
                    $defaults = array();
                    $collection = $this->getEntity()->_get($element->getName());
                    if ($collection) {
                        foreach($collection as $entity) {
                            $defaults[] = $entity->getEntityIdentifier();
                        }
                        $this->setDefaults(array($assName => $defaults));
                    }
                }
            }
        }
    }
    
    /**
     * Gets the default element for a mapping type.
     * 
     * @param string $type
     */
    protected function _getDefaultElement($type)
    {
        $elements = ($this->_dojoEnabled) ? $this->_defaultDojoElements : $this->_defaultElements;
        return isset($elements[$type]) ? $elements[$type] : null;
    }
}