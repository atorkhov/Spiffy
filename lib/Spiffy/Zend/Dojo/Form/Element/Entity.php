<?php
class Spiffy_Zend_Dojo_Form_Element_Entity extends Zend_Dojo_Form_Element_DijitMulti
{
    
    /**
     * Helper used to render element.
     * @var string
     */
    public $helper = 'formEntity';
    
    /**
     * flag: is the element expanded?
     * @var boolean
     */
    public $expanded = false;
    
    /**
     * flag: is the element multiple?
     * @var boolean
     */
    public $multiple = false;

    /**
     * Entity class.
     * @var string
     */
    protected $_class;
    
    /**
     * Empty label.
     * @var string
     */
    protected $_empty;
    
    /**
     * Sets the property to read for the class.
     * @var string
     */
    protected $_property;
    
    /**
     * Doctrine entity manager
     * @var Doctrine\ORM\EntityManager
     */
    protected $_entityManager;

    /**
     * Query builder.
     * @var Closure
     */
    protected $_queryBuilder;
    
    /**
     * (non-PHPdoc)
     * @see Zend_Form_Element::init()
     */
    public function init()
    {
        if (!Zend_Registry::isRegistered('Spiffy_Doctrine')) {
            throw new Zend_Form_Exception(
            	'Spiffy\Doctrine\Container is required when using Entity'
            );
        }

        if (!$this->getClass()) {
            throw new Zend_Form_Element_Exception(get_class($this) . ' requires a class');
        }
        
        $this->_entityManager = Zend_Registry::get('Spiffy_Doctrine')->getEntityManager();
        $this->options = $this->_getOptions();

        if ($this->multiple) {
            $this->_isArray = true;
        }
    }
    
    /**
     * Gets the options for the element.
     * 
     * @throws Exception\InvalidResult
     * @return array
     */
    protected function _getOptions()
    {
        $qb = $this->getQueryBuilder();
        if (!$qb instanceof Closure) {
            $qb = function ($er)
            {
                return $er->createQueryBuilder('entity');
            };
        }
        
        $options = array();
        
        if ($this->getEmpty()) {
            $options[serialize(null)] = $this->getEmpty();
        }
        
        $entityManager = $this->_entityManager;
        $mdata = $entityManager->getClassMetadata($this->getClass());
        $repository = $entityManager->getRepository($this->getClass());
        
        $qb = call_user_func($qb, $repository);
        foreach ($qb->getQuery()->execute() as $row) {
            if (!is_object($row)) {
                throw new Exception\InvalidResult('row result must be an object');
            }
        
            if ($this->getProperty()) {
                $value = $row->getValue($this->getProperty());
            } else {
                $value = (string) $row;
            }
            
            $id = null;
            $idValues = $mdata->getIdentifierValues($row);
            if (count($idValues) == 1) {
                $id = current($idValues);
            } else {
                $id = serialize($idValues);
            }
            $options[$id] = $value;
        }
        
        return $options;
    }
    
   /**
    * Set the property field.
    *
    * @param boolean $property
    */
    public function setProperty($property)
    {
        $this->_property = $property;
    }
    
    /**
     * Get the property field.
     *
     * @return boolean
     */
    public function getProperty()
    {
        return $this->_property;
    }

    /**
     * Get entity class.
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * Set entity class.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }
    
    /**
    * Set empty label.
    *
    * @param string $empty
    */
    public function setEmpty($empty)
    {
        $this->_empty = $empty;
    }
    
    /**
     * Get empty label.
     */
    public function getEmpty()
    {
        return $this->_empty;
    }

    /**
     * Get query builder.
     *
     * @return Closure
     */
    public function getQueryBuilder()
    {
        return $this->_queryBuilder;
    }

    /**
     * Set query builder.
     *
     * @param Closure $qb
     */
    public function setQueryBuilder(Closure $qb)
    {
        $this->_queryBuilder = $qb;
    }
}