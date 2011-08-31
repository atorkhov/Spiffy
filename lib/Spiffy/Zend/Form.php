<?php 
namespace Spiffy\Zend;
use Doctrine\DBAL\Types\Type,
    Doctrine\ORM\Mapping\ClassMetadataInfo,
    Zend_Form,
    Zend_Registry;

class Form extends Zend_Form
{
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
        'TO_ONE'            => 'entity'
    );
    
    /**
     * Constructor.
     * 
     * @param array $options
     */
    public function __construct($entity = null, array $options = array())
    {
        if ($entity) {
            $options['entity'] = $entity;
        }
        $options = array_merge($this->getDefaultOptions(), $options);
        
        parent::__construct($options);
        
        if ($this->getEntity()) {
            $this->setDefaults($this->getEntity()->toArray());
        }
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
        if ($this->getEntity()) {
            $mapping = null;
            $metadata = $this->getEntity()->getClassMetadata();

            if (isset($metadata->fieldMappings[$name])) {
                $mapping = $metadata->getFieldMapping($name); 
            } elseif (isset($metadata->assocationMappings[$name])) {
                $mapping = $metadata->getAssociationMapping($name);
            }
            
            if (!$element) {
                if ($mapping['type'] & ClassMetadataInfo::TO_ONE) {
                    // todo: implement automatic XXX_To_One entity   
                } else if (isset($this->_defaultElements[$mapping['type']])) {
                    $element = $this->_defaultElements[$mapping['type']];
                }
            }
            
            $options['filters'] = $this->getEntity()->getPropertyFilters($name);
            $options['validators'] = $this->getEntity()->getPropertyValidators($name);
        }
        
        if (!$element) {
            throw new Form\Exception\NoFormElement(
                sprintf(
                	'No form element was specified for "%s" and one not be determined automatically',
                	$name
            	)
            );
        }
        
        if (!array_key_exists('label', $options)) {
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
     * Sets the attached entity instance.
     *
     * @param string|Spiffy\Doctrine\AbstractEntity $entity
     */
    public function setEntity($entity)
    {
        if (is_string($entity)) {
            $entity = new $entity();
        }
        
        //if (!$entity instanceof AbstractEntity) {
        //    throw new Form\Exception\InvalidEntity(
        //    	'setEntity() expects instance of Spiffy\Doctrine\AbstractEntity'
        //    );
        //}
        $this->setDefaults($entity->toArray());
        $this->_entity = $entity;
    }
}