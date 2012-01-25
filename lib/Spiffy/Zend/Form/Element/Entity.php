<?php
use Spiffy\Doctrine\AbstractEntity;
class Spiffy_Zend_Form_Element_Entity extends Zend_Form_Element_Multi
{
    
    /**
     * Helper used to render element.
     * @var string
     */
    public $helper = 'formSelect';
    
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
     * Preloaded data.
     * @var array
     */
    protected $_preload;

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
        $this->_setOptionsOrData();        

        if ($this->multiple) {
            $this->_isArray = true;
        }
    }
    
    /**
     * Sets the options for the element or, if a Dojo store is enabled, the data for
     * the store.
     * 
     * @throws Exception\InvalidResult
     * @return array
     */
    protected function _setOptionsOrData()
    {
        $qb = $this->getQueryBuilder();
        if (!$qb instanceof Closure) {
            $qb = function ($er)
            {
                return $er->createQueryBuilder('entity');
            };
        }
        
        $entityManager = $this->_entityManager;
        $mdata = $entityManager->getClassMetadata($this->getClass());
        $repository = $entityManager->getRepository($this->getClass());
        
        $data = array();
        
        // empty value for non-store data
        if ($this->getEmpty()) {
            $this->options[AbstractEntity::getEncodedValue(null)] = $this->getEmpty();
        }
        
        if ($this->getPreload()) {
            foreach($this->getPreload() as $key => $value) {
                $this->options[$key] = $value;
            }
        }
        
        // build the query
        $qb = call_user_func($qb, $repository);
        if ($qb) {
            foreach ($qb->getQuery()->execute() as $row) {
                if (!is_object($row)) {
                    throw new Exception\InvalidResult('row result must be an object');
                }
                
                $value = $this->getProperty() ? $row->_get($this->getProperty()) : (string) $row;
                $this->options[$row->getEntityIdentifier()] = $value;
            }
        }
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
     * Set entity preload.
     *
     * @param array $preload
     */
    public function setPreload($preload)
    {
        $this->_preload = $preload;
    }

    /**
     * Get entity preload.
     */
    public function getPreload()
    {
        return $this->_preload;
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