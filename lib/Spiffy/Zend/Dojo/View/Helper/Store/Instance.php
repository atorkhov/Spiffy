<?php

class VetLogic_View_Helper_Store_Instance extends Zend_Dojo_View_Helper_Dijit
{
	/**
	 * HTML id of the store.
	 * @var string
	 */
	protected $_id;

	/**
	 * Store attributes.
	 * @var array
	 */
	protected $_attribs = array();

	/**
	 * Store properties.
	 * @var array
	 */
	protected $_params = array();

	/**
	 * Constructor.
	 * 
	 * @param string $id
	 * @param array $params
	 * @param array $attribs
	 * @throws Zend_Exception
	 */
	public function __construct($id, $module, array $params = array(), array $attribs = array()) {
		if (!$id) {
			throw new Zend_Exception('VetLogic_View_Helper_Grid_Instance requires an id');
		}

		$this->_id = $id;
		$this->_module = $module;

		$this->setParams($params);
		$this->setAttribs($attribs);
	}

	/**
	 * Magic method to render as a string.
	 */
	public function __toString() {
		return $this
			->_createLayoutContainer($this->_id, '', $this->_params, $this->_attribs,
				$this->_module);
	}

	/**
	 * Sets attributes for grid.
	 * 
	 * @param array $attribs
	 */
	public function setAttribs(array $attribs) {
		$this->_attribs = $attribs;
		return $this;
	}

	/**
	 * Sets params for the grid from an array.
	 * 
	 * @param array $params
	 */
	public function setParams(array $params) {
		if (!isset($params['jsId'])) {
			$params['jsId'] = $this->_normalizeId($this->_id);
		}

		$this->_params = $params;
		return $this;
	}
}
