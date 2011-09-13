<?php
class Spiffy_Zend_Dojo_View_Helper_MultiSelect extends Zend_Dojo_View_Helper_Dijit
{
   /**
    * Dijit being used
    * @var string
    */
    protected $_dijit  = 'dijit.form.MultiSelect';
    
    /**
     * HTML element type
     * @var string
     */
    protected $_elementType = 'text';
    
    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.form.MultiSelect';
    
   /**
    * dijit.form.MultiSelect
    *
    * @param  int $id
    * @param  mixed $value
    * @param  array $params  Parameters to use for dijit creation
    * @param  array $attribs HTML attributes
    * @param  array $options
    * @return string
    */
    public function multiSelect($id, $value = null, array $params = array(), array $attribs = array(), 
        array $options = null
    ) {
        // required for correct type casting in declerative mode 
        if (isset($params['autocomplete'])) {
            $params['autocomplete'] = ($params['autocomplete']) ? 'true' : 'false';
        }
        
        // do as normal select
        $attribs = $this->_prepareDijit($attribs, $params, 'element');
        return $this->view->formSelect($id, $value, $attribs, $options);
    }
}