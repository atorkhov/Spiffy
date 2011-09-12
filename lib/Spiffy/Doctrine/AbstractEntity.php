<?php
namespace Spiffy\Doctrine;
use Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection,
    Doctrine\DBAL\Types\Type,
    Doctrine\ORM\Mapping\ClassMetadataInfo,
    ReflectionClass,
    ReflectionProperty,
    Zend_Filter,
    Zend_Filter_Input,
    Zend_Registry,
    Zend_Validate,
    Zend_Validate_NotEmpty;

abstract class AbstractEntity
{
    const FILTER = 1;
    const VALIDATOR = 2;
    
    const FILTER_ZEND_NAMESPACE = 'Zend_Filter_';
    const VALIDATOR_ZEND_NAMESPACE = 'Zend_Validate_';
    
    const FILTER_ANNOTATION_NAMESPACE = 'Spiffy\Doctrine\Annotations\Filters\Zend';
    const VALIDATOR_ANNOTATION_NAMESPACE = 'Spiffy\Doctrine\Annotations\Validators\Zend';
    
    /**
     * Annotation reader.
     * @var Doctrine\Common\Annotations\AnnotationReader
     */
    private static $_annotationReader__;
    
    /**
     * Cache of initialized classes.
     * @var array
     */
    protected static $_initialized__ = array();
    
    /**
     * Cache of filter information from annotations.
     * @var array
     */
    protected static $_filters__ = array();
    
    /**
     * Cache of validator information from annotations.
     * @var array
     */
    protected static $_validators__ = array();
    
    /**
	 * Cache of filter inputs.
	 * @var array
	 */ 
    protected static $_filterInput__ = array();
    
    /**
     * Cache of class metadata.
     * @var array
     */
    protected static $_metadata__ = array();
    
    /**
     * Initializes and caches Zend_Filter_Input for the entity.
     */
    protected static function _initialize()
    {
        $self = get_called_class();
        if (isset(self::$_initialized__[$self])) {
            return;
        }
        
        $validators = array();
        $filters = array();
        
        $metadata = self::getClassMetadata();
        foreach($metadata->getReflectionProperties() as $property) {
            if (isset($metadata->fieldMappings[$property->name])) {
                $mapping = $metadata->getFieldMapping($property->name);
            } else {
                $mapping = $metadata->getAssociationMapping($property->name);
            }
            
            // filter annotations
            $fAnnotations = self::_getPropertyAnnotation($property, self::FILTER_ANNOTATION_NAMESPACE);
            foreach($fAnnotations as $annotation) {
                $class = str_replace(self::FILTER_ZEND_NAMESPACE< '', $annotation->class);
                if (empty($annotation->value)) {
                    $filter = $class;
                } else {
                    $filter = array(
                        'filter' => $class, 
                        'options' => $annotation->value
                    );
                }
                self::$_filters__[$self][$property->name][] = $filter;
            }
            
            // validator annotations
            $vAnnotations = self::_getPropertyAnnotation($property, self::VALIDATOR_ANNOTATION_NAMESPACE);
            foreach($vAnnotations as $annotation) {
                $class = str_replace(self::VALIDATOR_ZEND_NAMESPACE, '', $annotation->class);
                if (empty($annotation->value)) {
                    $validator = $class;
                } else {
                    $validator = array(
                        'validator' => $class,
                        'breakChainOnFailure' => (bool) $annotation->breakChain,
                        'options' => $annotation->value
                    );
                }
                
                self::$_validators__[$self][$property->name][] = $validator;
            }
            
            // automatic filters/validators
            if (isset($mapping['nullable']) && !$mapping['nullable']) {
                $options = array();
                if ($mapping['type'] == Type::BOOLEAN) {
                    $options = array(
                        Zend_Validate_NotEmpty::ALL - 
                        Zend_Validate_NotEmpty::BOOLEAN - 
                        Zend_Validate_NotEmpty::INTEGER
                    );
                }
                self::_addAutomaticFilterValidator(
                    self::VALIDATOR,
                    $property,
                    'NotEmpty',
                    $options
                );
            }
            
            switch ($mapping['type']) {
                case Type::SMALLINT:
                case Type::INTEGER:
                case Type::BIGINT:
                    self::_addAutomaticFilterValidator(
                        self::FILTER,
                        $property,
                        'Int'
                    );
                    break;
                case Type::BOOLEAN:
                    self::_addAutomaticFilterValidator(
                        self::FILTER,
                        $property,
                        'Boolean'
                    );
                    break;
                case Type::TEXT:
                case Type::STRING:
                    self::_addAutomaticFilterValidator(
                        self::FILTER,
                        $property,
                        'StringTrim'
                    );
                    if (isset($mapping['length'])) {
                        self::_addAutomaticFilterValidator(
                            self::VALIDATOR,
                            $property,
                            'StringLength',
                            array('max' => (int) $mapping['length'])
                        );
                    }
                    break;
            }
        }
        
        self::$_initialized__[$self] = true;
    }
    
    /**
     * Adds automatic filter or validator based on the type passed. Will not
     * add if an annotation for the property already exists.
     * 
     * @param string $type
     * @param ReflectionProperty $property
     * @param string $class
     * @param array $options
     * @throws Exception\InvalidType
     */
    private static function _addAutomaticFilterValidator($type, ReflectionProperty $property, 
        $class, array $options = array()
    )
    {
        if ($type != self::FILTER && $type != self::VALIDATOR) {
            throw new Exception\InvalidType('Type must be FILTER or VALIDATOR');
        }
        
        $self = get_called_class();
        $dataName = ($type == self::FILTER) ? '_filters__' : '_validators__';
        
        if (isset(self::${$dataName}[$self][$property->name]) &&
            in_array($class, self::${$dataName}[$self][$property->name])
        ) {
            return;
        }
        if (empty($options)) {
            self::${$dataName}[$self][$property->name][] = $class;
        } else {
            self::${$dataName}[$self][$property->name][] = array(
                'validator' => $class,
                'options' => $options
            );
        }
    }
    
    /**
     * Gets the Doctrine annotation reader.
     * 
     * @return Doctrine\Common\Annotations\AnnotationReader
     */
    private static function _getAnnotationReader()
    {
        if (null === self::$_annotationReader__) {
            self::$_annotationReader__ = new AnnotationReader;
        }
        return self::$_annotationReader__;
    }
    
    /**
     * Gets the metadata mapping for a class property. Returns the
     * fieldMapping or associationMapping accordingly.
     * 
     * @param string $propertyName
     * @return array|null
     */
    public static function getPropertyMapping($propertyName)
    {
        $metadata = self::getClassMetadata();
        
        if (isset($metadata->fieldMappings[$propertyName])) {
            return $metadata->fieldMappings[$propertyName];
        } elseif (isset($metadata->associationMappings[$propertyName])) {
            return $metadata->associationMappings[$propertyName];
        }
        
        return null;
    }
    
    /**
     * Gets annotations of a given namespace for a property.
     * 
     * @param ReflectionProperty $property
     * @param string $annotationName
     * @return array
     */
    private static function _getPropertyAnnotation($property, $annotationName)
    {
        $reader = self::_getAnnotationReader();
        $annotations = $reader->getPropertyAnnotations($property);
        
        $result = array();
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                $result[] = $annotation;
            }
        }
        
        return $result;
    }
    
    /**
     * Gets the class metadata info.
     * 
     * @return Doctrine\ORM\Mapping\ClassMetadata
     */
    public static function getClassMetadata()
    {
        $self = get_called_class();
        if (!isset(self::$_metadata__[$self])) {
            self::$_metadata__[$self] = self::getEntityManager()->getClassMetadata($self);
        }
        
        return self::$_metadata__[$self];
    }
    
    /**
     * Get the entity manager.
     * 
     * @param string $emName
     * @return Doctrine\ORM\EntityManager
     */
    public static function getEntityManager($emName = null)
    {
        return Zend_Registry::get('Spiffy_Doctrine')->getEntityManager($emName);
    }
    
    /**
     * Gets the filter input for a class.
     * 
     * @return \Zend_Filter_Input
     */
    public static function getFilterInput()
    {
        self::_initialize();
        
        $self = get_called_class();
        if (!isset(self::$_filterInput__[$self])) {
            $filters = self::getClassFilters();
            $validators = self::getClassValidators();
            
            foreach($filters as $field => $fieldFilters) {
                foreach($fieldFilters as $key => $value) {
                    if (!is_array($value)) {
                        $filter = $value;
                    }
                    $filters[$field][$key] = $filter;
                }
            }
            
            foreach($validators as $field => $fieldValidators) {
                foreach($fieldValidators as $key => $value) {
                    if (!is_array($value)) {
                        $validator = $value;
                    }
                    $validators[$field][$key] = $validator;
                }
                
            }
            self::$_filterInput__[$self] = new Zend_Filter_Input($filters, $validators);
        }
        return self::$_filterInput__[$self];
    }
    
    /**
     * Get filters for a given property.
     *
     * @param string $name
     * @return array
     */
    public static function getPropertyFilters($name)
    {
        $filters = self::getClassFilters();
        if (isset($filters[$name])) {
            return $filters[$name];
        }
        return array();
    }
    
    /**
     * Get validators for a given property.
     * 
     * @param string $name
     * @return array
     */
    public static function getPropertyValidators($name)
    {
        $validators = self::getClassValidators();
        if (isset($validators[$name])) {
            return $validators[$name];
        }
        return array();
    }
    
    /**
     * Gets class filters.
     * 
     * @return array
     */
    public static function getClassFilters()
    {
        self::_initialize();
        return self::$_filters__[get_called_class()];
    }
    
    /**
     * Gets class validators.
     * 
     * @return array
     */
    public static function getClassValidators()
    {
        self::_initialize();
        return self::$_validators__[get_called_class()];
    }
    
    /**
     * Uses annotation validators to determine if the entity is valid.
     * The validator chain is cached and lazy-loaded to be as 
     * performant as possible.
     * 
     * @param string|null $field 
     * @return boolean
     */
    public function isValid($field = null)
    {
        $fi = self::getFilterInput();
        $fi->setData($this->toArray());
        
        return $fi->isValid($field);
    }
    
    /**
     * Convert and return entity as an array. Only converts
     * public methods and does not convert joined entities.
     * Joined entities will be returned as their identifier 
     * (which is an array in the case of composite keys).
     *
     * @param boolean $filter
     * @return array
     */
    public function toArray($filter = true)
    {
        $metadata = self::getClassMetadata();
        foreach($metadata->getFieldNames() as $field) {
            $result[$field] = $this->_get($field);
        }
        
        if ($filter) {
            $fi = self::getFilterInput();
            $fi->setData($result);
            $result = array_merge($result, $fi->getEscaped());
        }
        
        return $result;
    }
    
    /**
     * Set entity fields from data array.
     *
     * @param array $data
     */
    public function fromArray(array $data)
    {
        foreach($data as $key => $value) {
            $mapping = $this->getPropertyMapping($key);

            if ($mapping && 
                ($mapping['type'] & ClassMetadataInfo::TO_MANY) ||
                ($mapping['type'] & ClassMetadataInfo::TO_ONE)
            ) {
                if ($mapping['type'] & ClassMetadataInfo::TO_ONE) {
                    $value = $this->getEntityManager()->getReference(
                        $mapping['targetEntity'],
                        $this->_normalize($value)
                    );
                } else {
                    if (!is_array($value)) {
                        throw new Exception\InvalidMappingData(
                            'Data for ManyToMany relations must be an array'
                        );
                    }
                    
                    if (!$this->$key instanceof Collection) {
                        $this->$key = new ArrayCollection;
                    }

                    foreach($value as &$v) {
                        $v = $this->getEntityManager()->getReference(
                            $mapping['targetEntity'],
                            $this->_normalize($v)
                        );
                    }
                }
            }

            $this->_set($key, $value);
        }
    }
    
    /**
     * Get a field's value.
     * 
     * @param string $field
     * @return mixed
     */
    protected function _get($field)
    {
        $value = null;
        
        $getter = 'get' . ucfirst($field);
        if (method_exists($this, $getter)) {
            $value = $this->$getter();
        } elseif (isset($this->$field) || property_exists($this, $field)) {
            $mapping = $this->getPropertyMapping($field);
        
            // for booleans, check if an isser exists
            if ($mapping && $mapping['type'] == Type::BOOLEAN) {
                $isser = 'is' . ucfirst(preg_replace('/^is/', '', $field));
                if (method_exists($this, $isser)) {
                    $value = $this->$isser();
                }
            } else {
                $value = $this->$field;
            }
        } else {
            $value = null;
        }
        
        // sanitize field
        if (is_object($value)) {
            switch(get_class($value)) {
                case 'DateTime':
                    $value = $value->format('c');
                    break;
            }
        }
        
        return $value;
    }
    
    /**
     * Set a field's value.
     * 
     * @param string $key
     * @param mixed $value
     * @throws Exception\InvalidMappingData
     */
    protected function _set($key, $value)
    {
        $setter = 'set' . ucfirst($key);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (isset($this->$key) || property_exists($this, $key)) {
            if ($this->$key instanceof Collection) {
                if (!is_array($value)) {    
                    throw new Exception\InvalidMappingData(
                        'Data for Collections must be an array'
                    );
                }
                
                $this->$key->clear();
                foreach($value as $v) {
                    $this->$key->add($v);
                }
            } else {
                $this->$key = $value;
            }
        }
    }
    
    protected function _normalize($value)
    {
        if (is_object($value) || is_numeric($value)) {
            ; // intentionally left blank
        } else if ($this->_isSerialized($value)) {
            $value = unserialize($value);
        }
        
        return $value;
    }

    /**
     * Save the entity to persistance storage.
     * 
     * @param boolean $flush
     * @return true
     */
    public function save($flush = true)
    {
        $em = self::getEntityManager();
        $em->persist($this);
        
        if ($flush) {
            $em->flush();
        }
        
        return true;
    }
    
    /**
     * Determines if a string is serialized. This is a direct copy
     * from the WordPress is_serialized() method.
     * 
     * @param string $data
     */
    protected function _isSerialized($data)
    {
        if (!is_string($data)) {
            return false;
        }
        
        $data = trim( $data );
        if ( 'N;' == $data ) {
            return true;
        }
      
        $length = strlen( $data );
        if ($length < 4) {
            return false;
        }
        
        if (':' !== $data[1]) {
            return false;
        }
        
        $lastc = $data[$length-1];
        if (';' !== $lastc && '}' !== $lastc) {
            return false;
        }
        
        $token = $data[0];
        switch ($token) {
            case 's' :
                if ('"' !== $data[$length-2]) {
                    return false;
                }
            case 'a' :
            case 'O' :
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b' :
            case 'i' :
            case 'd' :
                return (bool) preg_match("/^{$token}:[0-9.E-]+;\$/", $data);
          }
          
          return false;
      }
}