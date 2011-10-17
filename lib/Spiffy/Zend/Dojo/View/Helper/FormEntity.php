<?php

class Spiffy_Zend_Dojo_View_Helper_FormEntity extends Zend_Dojo_View_Helper_Dijit
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
        
        $html = '';
        if (!array_key_exists('id', $attribs)) {
            $attribs['id'] = $id;
        }
        if (array_key_exists('store', $params) && is_array($params['store'])) {
            // using dojo.data datastore
            if (false !== ($store = $this->_renderStore($params['store'], $id))) {
                $params['store'] = $params['store']['store'];
                if (is_string($store)) {
                    $html .= $store;
                }
                $html .= $this->_createFormElement($id, $value, $params, $attribs);
                return $html;
            }
            unset($params['store']);
        } elseif (array_key_exists('store', $params)) {
            if (array_key_exists('storeType', $params)) {
                $storeParams = array(
                    'store' => $params['store'],
                    'type'  => $params['storeType'],
                );
                unset($params['storeType']);
                if (array_key_exists('storeParams', $params)) {
                    $storeParams['params'] = $params['storeParams'];
                    unset($params['storeParams']);
                }
                if (false !== ($store = $this->_renderStore($storeParams, $id))) {
                    if (is_string($store)) {
                        $html .= $store;
                    }
                }
            }
            $html .= $this->_createFormElement($id, $value, $params, $attribs);
            return $html;
        }
        
        // required for correct type casting in declerative mode
        if (isset($params['autocomplete'])) {
            $params['autocomplete'] = ($params['autocomplete']) ? 'true' : 'false';
        }
    
        $attribs = $this->_prepareDijit($attribs, $params, 'element');
        return $this->view->formSelect($id, $value, $attribs, $options);
    }

    /**
     * Render data store element
     *
     * Renders to dojo view helper
     *
     * @param  array $params
     * @return string|false
     */
    protected function _renderStore(array $params, $id)
    {
        if (!array_key_exists('store', $params) || !array_key_exists('type', $params)) {
            return false;
        }
    
        $this->dojo->requireModule($params['type']);
    
        $extraParams = array();
        $storeParams = array(
                'dojoType' => $params['type'],
                'jsId'     => $params['store'],
        );
    
        if (array_key_exists('params', $params)) {
            $storeParams = array_merge($storeParams, $params['params']);
            $extraParams = $params['params'];
        }
    
        if ($this->_useProgrammatic()) {
            if (!$this->_useProgrammaticNoScript()) {
                require_once 'Zend/Json.php';
                $this->dojo->addJavascript('var ' . $storeParams['jsId'] . ";\n");
                $js = $storeParams['jsId'] . ' = '
                . 'new ' . $storeParams['dojoType'] . '('
                .     Zend_Json::encode($extraParams)
                . ");\n";
                $js = "function() {\n$js\n}";
                $this->dojo->_addZendLoad($js);
            }
            return true;
        }
    
        return '<div' . $this->_htmlAttribs($storeParams) . '></div>';
    }
}