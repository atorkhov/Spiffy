<?php
class Spiffy_Zend_Form_Element_Entity extends Zend_Form_Element_Multi
{
    /**
     * Default helper.
     * @var string
     */
    protected $_defaultHelper = 'formSelect';
    
    /**
     * Multiple expanded helper.
     * @var string
     */
    protected $_multipleExpandedHelper = 'formMultiCheckbox';

   /**
    * Expanded helper.
    * @var string
    */
    protected $_expandedHelper = 'formRadio';
    
    /**
     * Multiple helper.
     * @var string
     */
    protected $_multipleHelper = 'formSelect';
    
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
        $this->options = $this->_doctrine->getMultiOptions($this->_class, $this->_queryBuilder);
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
