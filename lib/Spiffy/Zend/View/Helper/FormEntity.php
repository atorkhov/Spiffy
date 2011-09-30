<?php

class Spiffy_Zend_View_Helper_FormEntity extends Zend_Dojo_View_Helper_Dijit
{
    protected $_dijit = 'dijit.form.FilteringSelect';
    protected $_module = 'dijit.form.FilteringSelect';
    
    public function formEntity($id, $value = null, array $params = array(), 
        array $attribs = array(), array $options = null
    )
    {
        $multiple = isset($attribs['multiple']) ? $attribs['multiple'] : false;
        $expanded = isset($attribs['expanded']) ? $attribs['expanded'] : false;
        
        if ($multiple && $expanded) {
            $attribs = $this->_prepareDijit($attribs, $params, 'element');
            return $this->view->formMultiCheckbox($id, $value, $attribs, $options);
        } elseif ($multiple) {
            return $this->view->multiSelect($id, $value, $params, $attribs, $options);
        } elseif ($expanded) {
            $attribs = $this->_prepareDijit($attribs, $params, 'element');
            return $this->view->formRadio($id, $value, $attribs, $options);
        }
        
        $attribs = $this->_prepareDijit($attribs, $params, 'element');
        return $this->view->formSelect($id, $value, $attribs, $options);
    }
}