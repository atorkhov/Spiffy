<?php
class Spiffy_Zend_Dojo_Form_Element_Entity extends Zend_Dojo_Form_Element_DijitMulti
{
   /**
     * Default helper.
     * @var string
     */
    protected $_defaultHelper = 'FilteringSelect';
    
    /**
     * Multiple expanded helper.
     * @var string
     */
    protected $_multipleExpandedHelper = 'CheckBox';

   /**
    * Expanded helper.
    * @var string
    */
    protected $_expandedHelper = 'RadioButton';
    
    /**
     * Multiple helper.
     * @var string
     */
    protected $_multipleHelper = 'MultiSelect';
    
    /**
     * Entity class.
     * @var string
     */
    protected $_class;
    
    /**
     * Sets the property to read for the class.
     * @var string
     */
    protected $_property;
    
    /**
     * flag: is field expanded?
     * @var boolean
     */
    protected $_expanded = false;
    
    /**
     * flag: is field multiple?
     * @var boolean
     */
    protected $_multiple = false;
    
    /**
     * Spiffy container.
     * @var Spiffy\Doctrine\Container
     */
    protected $_doctrine;

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
        
        $this->helper = $this->_defaultHelper;

        $this->_doctrine = Zend_Registry::get('Spiffy_Doctrine');
        $this->options = $this->_getOptions();
    }
    
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
        
        $entityManager = $this->_doctrine->getEntityManager();
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
    * Set the expanded flag.
    *
    * @param boolean $expanded
    */
    public function setExpanded($expanded)
    {
        $this->_expanded = $expanded;
    }
    
    /**
     * Get the expanded flag.
     *
     * @return boolean
     */
    public function getExpanded()
    {
        return $this->_expanded;
    }
    
    /**
     * Set the multiple flag.
     * 
     * @param boolean $multiple
     */
    public function setMultiple($multiple)
    {
        $this->_multiple = $multiple;
    }
    
    /**
     * Get the multiple flag.
     * 
     * @return boolean
     */
    public function getMultiple()
    {
        return $this->_multiple;
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
    
   /**
    * Render form element
    *
    * @param  Zend_View_Interface $view
    * @return string
    */
    public function render(Zend_View_Interface $view = null)
    {
        if ($this->getMultiple() && $this->getExpanded()) {
            $this->helper = $this->_multipleExpandedHelper;
            $this->_isArray = true;
        } else if ($this->getMultiple()) {
            $this->helper = $this->_multipleHelper;
            $this->_isArray = true;
        } else if ($this->getExpanded()) {
            $this->helper = $this->_expandedHelper;
            $this->_isArray = false;
        } else {
            $this->helper = $this->_defaultHelper;
            $this->_isArray = false;
        }
        
        if ($this->_isPartialRendering) {
            return '';
        }
    
        if (null !== $view) {
            $this->setView($view);
        }
    
        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }
        return $content;
    }
}