<?php

class VetLogic_View_Helper_EnhancedGrid extends Zend_Dojo_View_Helper_Dijit
{
	/**
	 * An array of grids.
	 * @var array
	 */
	protected $_grids = array();

	/**
	 * EnhancedGrid view helper.
	 * 
	 * @param string $id
	 * @param array $params
	 * @param array $attribs
	 * @throws Zend_Exception
	 */
	public function enhancedGrid($id, array $params = array(), array $attribs = array()) {
		if (!isset($this->_grids[$id])) {
			$this->_grids[$id] = new VetLogic_View_Helper_Grid_Instance($id, $params, $attribs,
				VetLogic_View_Helper_Grid_Instance::TYPE_ENHANCED);
			$this->_grids[$id]->setView($this->view);
		}

		return $this->_grids[$id];
	}
}
