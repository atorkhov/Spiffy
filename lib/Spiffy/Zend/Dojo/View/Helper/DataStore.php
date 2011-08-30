<?php

class VetLogic_View_Helper_DataStore extends Zend_Dojo_View_Helper_Dijit
{
	/**
	 * An array of stores.
	 * @var array
	 */
	protected $_stores = array();

	/**
	 * DataStore view helper.
	 * 
	 * @param string $id
	 * @param string $module
	 * @param array $params
	 * @param array $attribs
	 * @throws Zend_Exception
	 */
	public function dataStore($id, $module = null, array $params = array(),
		array $attribs = array()) {
		if (!isset($this->_stores[$id])) {
			if (!$module) {
				throw new Zend_Exception('Module type required');
			}

			$this->_stores[$id] = new VetLogic_View_Helper_Store_Instance(
			    $id,
			    $module,
			    $params,
                $attribs
            );
			$this->_stores[$id]->setView($this->view);
		}

		return $this->_stores[$id];
	}
}
