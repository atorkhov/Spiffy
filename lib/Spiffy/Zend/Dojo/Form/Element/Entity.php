<?php
use Spiffy\Doctrine\AbstractEntity;
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
        $this->_setOptionsOrData();        

        if ($this->multiple) {
            $this->_isArray = true;
        }
    }
    
    /**
     * Get datastore information
     *
     * @return array
     */
    public function getStoreInfo()
    {
        if (!$this->hasDijitParam('store')) {
            $this->dijitParams['store'] = array();
        }
        return $this->dijitParams['store'];
    }
    
    /**
     * Set datastore identifier
     *
     * @param  string $identifier
     * @return Zend_Dojo_Form_Element_ComboBox
     */
    public function setStoreId($identifier)
    {
        $store = $this->getStoreInfo();
        $store['store'] = (string) $identifier;
        $store['type'] = "dojo.data.ItemFileReadStore";
        
        $this->setDijitParam('store', $store);
        return $this;
    }
    
    /**
     * Get datastore identifier
     *
     * @return string|null
     */
    public function getStoreId()
    {
        $store = $this->getStoreInfo();
        if (array_key_exists('store', $store)) {
            return $store['store'];
        }
        return null;
    }
    
    /**
     * Set datastore parameters
     *
     * @param  array $params
     * @return Zend_Dojo_Form_Element_ComboBox
     */
    public function setStoreParams(array $params)
    {
        $store = $this->getStoreInfo();
        $store['params'] = $params;
        $this->setDijitParam('store', $store);
        return $this;
    }
    
    /**
     * Get datastore params
     *
     * @return array
     */
    public function getStoreParams()
    {
        $store = $this->getStoreInfo();
        if (array_key_exists('params', $store)) {
            return $store['params'];
        }
        return array();
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
        $store = $this->getStoreInfo();
        $isStore = !empty($store);
        
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
        if (!$isStore && $this->getEmpty()) {
            $data[AbstractEntity::getEncodedValue(null)] = $this->getEmpty();
        }
        
        // build the query
        $qb = call_user_func($qb, $repository);
        if ($qb) {
            foreach ($qb->getQuery()->execute() as $row) {
                if (!is_object($row)) {
                    throw new Exception\InvalidResult('row result must be an object');
                }
                
                if ($isStore) {
                    $data[] = array_merge(array('id' => $row->getEntityIdentifier()), $row->toArray(true, false));
                }
                
                $value = $this->getProperty() ? $row->_get($this->getProperty()) : (string) $row;
                $this->options[$row->getEntityIdentifier()] = $value;                    
            }
        }
        
        // set data based on store or regular
        if ($isStore) {
            $data = new Zend_Dojo_Data('id', $data);
            $store['params']['data'] = $data->toArray();
            $this->setDijitParam('store', $store);
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