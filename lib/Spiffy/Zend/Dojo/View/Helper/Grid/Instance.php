<?php

class VetLogic_View_Helper_Grid_Instance extends Zend_Dojo_View_Helper_Dijit
{
	const TYPE_DATA = 'dojox.grid.DataGrid';
	const TYPE_ENHANCED = 'dojox.grid.EnhancedGrid';
	const TYPE_TREE = 'dojox.grid.TreeGrid';

	/**
	 * HTML id of the grid.
	 * @var string
	 */
	protected $_id;

	/**
	 * Field to set sortInfo from.
	 * @var string
	 */
	protected $_sort;

	/**
	 * Variable to keep track of script capture locks.
	 * @var boolean
	 */
	protected $_captureLock = false;

	/**
	 * Script capture info.
	 * @var array
	 */
	protected $_captureInfo = array();

	/**
	 * Captured script content.
	 * @var array
	 */
	protected $_capturedScripts = array();

	/**
	 * Parameters that should be JSON encoded as Zend_Json_Expr
	 * @var array
	 */
	protected $_jsonExprParams = array('formatter', 'canSort', 'get');

	/**
	 * Grid attributes.
	 * @var array
	 */
	protected $_attribs = array();

	/**
	 * Grid properties.
	 * @var array
	 */
	protected $_params = array();

	/**
	 * An array of plugins.
	 * @var array
	 */
	protected $_plugins = array();

	/**
	 * An array of fields.
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * The type of grid to create.
	 * @var string
	 */
	protected $_module = self::TYPE_DATA;

	/**
	 * Constructor.
	 * 
	 * @param string $id
	 * @param array $params
	 * @param array $attribs
	 * @throws Zend_Exception
	 */
	public function __construct($id, array $params = array(), array $attribs = array(),
		$module = self::TYPE_DATA) {
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
	 * 
	 * @return string
	 */
	public function __toString() {
		// set sort info based on field name
		if ($this->_sort) {
			$fields = array_keys($this->_fields);

			if (in_array($this->_sort, $fields)) {
				$this->_params['sortInfo'] = array_search($this->_sort, $fields) + 1;
			}
		}

		$this->_setPlugins();
		$this->_setStylesheet();

		// programmatic renders with a div and markup with a table
		$content = '';
		if ($this->_useProgrammatic()) {
			$this->setRootNode('div');
			$this->_contentProgrammatic();
		} else {
			$this->setRootNode('table');
			$content = $this->_contentDeclarative();
		}

		$html = $this
			->_createLayoutContainer($this->_id, $content, $this->_params, $this->_attribs,
				$this->_module);

		return $html;
	}

	/**
	 * Sets up plugin parameters.
	 * 
	 * @return void
	 */
	public function _setPlugins() {
		if ($this->_module != self::TYPE_ENHANCED) {
			return;
		}

		$plugins = array();
		foreach ($this->_plugins as $plugin => $params) {
			if (preg_match('/\.(?P<plugin>\w+)$/', $plugin, $matches)) {
				$this->dojo->requireModule($plugin);
				$plugins[lcfirst($matches['plugin'])] = $params;
			}
		}

        if (!empty($plugins)) {
    		$this->_params['plugins'] = Zend_Json::encode($plugins);
        }
	}

	/**
	 * Begins capturing for script content.
	
	 * @param string $method
	 * @param string $event
	 * @param array $attribs
	 * @throws Zend_Dojo_View_Exception
	 */
	public function scriptCaptureStart($method, $event, array $attribs = array()) {
		if ($this->_captureLock) {
			require_once 'Zend/Dojo/View/Exception.php';
			throw new Zend_Dojo_View_Exception(
				'You can only capture content for one script at a time');
		}

		$this->_captureLock = true;
		$this->_captureInfo['method'] = $method;
		$this->_captureInfo['event'] = $event;
		$this->_captureInfo['attribs'] = $attribs;

		ob_start();
	}

	/**
	 * Finishes capturing for script content.
	
	 * @throws Zend_Dojo_View_Exception
	 */
	public function scriptCaptureEnd() {
		if (!$this->_captureLock) {
			require_once 'Zend/Dojo/View/Exception.php';
			throw new Zend_Dojo_View_Exception('No capture lock exists; nothing to capture');
		}

		$this->_capturedScripts[] = array_merge(array('content' => ob_get_clean()),
			$this->_captureInfo);
		$this->_captureInfo = array();
		$this->_captureLock = false;
	}

	/**
	 * Generates programmatic grid content.
	 * 
	 * @return string
	 */
	protected function _contentProgrammatic() {
		$structure = array();
		foreach ($this->_fields as $data) {
			foreach ($data as $k => $v) {
				if (in_array($k, $this->_jsonExprParams)) {
					$data[$k] = new Zend_Json_Expr($v);
				}
			}

			$structure[] = $data;
		}

		$this->_params['structure'] = Zend_Json::encode($structure, false,
			array('enableJsonExprFinder' => true));

		$js = "function(){";
		foreach ($this->_capturedScripts as $script) {
			$content = explode("\n", $script['content']);
			foreach ($content as $k => $v) {
				if (!strlen(trim($v))) {
					unset($content[$k]);
					continue;
				}
				$content[$k] = "            {$v}";
			}
			$content = implode("\n", $content);

			$js .= "\n        "
				. "dojo.connect(dijit.byId('{$this->_params['jsId']}'),'{$script['event']}',function({$script['attribs']['args']}){\n"
				. "{$content}\n" . "        });";
		}
		$js .= "\n    }";

		$this->dojo->addOnLoad($js);
	}

	/**
	 * Generates declarative grid content.
	 * 
	 * @return string
	 */
	protected function _contentDeclarative() {
		$content = '<thead><tr>';
		foreach ($this->_fields as $data) {
			$name = $data['name'];
			unset($data['name']);

			$content .= '<th field="' . $data['field'] . '"' . $this->_htmlAttribs($data) . '>'
				. $name . '</th>';
		}
        $content .= '</tr></thead>';

		return $content;
	}

	/**
	 * Sets the stylesheet for the grid.
	 * 
	 * @return void
	 */
	protected function _setStylesheet() {
		if ($this->dojo->getLocalPath()) {
			$path = str_replace('/dojo/dojo.js', '', $this->dojo->getLocalPath());
		} else {
			$path = $this->dojo->getCdnBase() . $this->dojo->getCdnVersion();
		}

		// determine theme
		$theme = null;
		foreach ($this->dojo->getStylesheetModules() as $ss) {
			if (preg_match('/^dijit\.themes\.(?P<theme>\w+)$/', $ss, $matches)) {
				$theme = $matches['theme'];
				break;
			}
		}

		// set stylesheet from grid type
		switch ($this->_module) {
			case self::TYPE_ENHANCED:
				$sheet = sprintf('%s/dojox/grid/enhanced/resources/%sEnhancedGrid.css', $path,
					$theme ? "{$theme}/" : '');
				$this->dojo->addStylesheet($sheet);
				break;
			default:
				$this->dojo->addStylesheet("{$path}/dojox/grid/resources/Grid.css");
				$this->dojo->addStylesheet("{$path}/dojox/grid/resources/{$theme}Grid.css");
				break;
		}
	}

	/**
	 * Sets the selection mode.
	 * 
	 * @param string $mode
	 */
	public function setSelectionMode($mode) {
		$this->_params['selectionMode'] = $mode;
		return $this;
	}

	/**
	 * Sets the auto height.
	 * 
	 * @param string $height
	 */
	public function setAutoHeight($height) {
		$this->_params['autoHeight'] = $height;
		return $this;
	}

	/**
	 * Sets the grid datastore.
	 * 
	 * @param string $store
	 */
	public function setStore($store) {
		$this->_params['store'] = $store;
		return $this;
	}

	/**
	 * Sets the rows per page.
	 * 
	 * @param string $rowsPerPage
	 */
	public function setRowsPerPage($rowsPerPage) {
		$this->_params['rowsPerPage'] = $rowsPerPage;
		return $this;
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
	 * Sets client sort for grid.
	 * 
	 * @param array $clientSort
	 */
	public function setClientSort(array $clientSort) {
		$this->_attribs['clientSort'] = $clientSort;
		return $this;
	}

	/**
	 * Sets delay scroll for grid.
	 * 
	 * @param array $delayScroll
	 */
	public function setDelayScroll(array $delayScroll) {
		$this->_attribs['delayScroll'] = $delayScroll;
		return $this;
	}

	/**
	 * Sets query for grid.
	 * 
	 * @param array $query
	 */
	public function setQuery(array $query) {
		$this->_attribs['query'] = $query;
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

		if (isset($params['plugins'])) {
			$this->addPlugins($params['plugins']);
			unset($params['plugins']);
		}

		if (isset($params['fields'])) {
			$this->addFields($params['fields']);
			unset($params['fields']);
		}

		if (isset($params['sort'])) {
			$this->setSort($params['sort']);
			unset($params['sort']);
		}

		$this->_params = $params;
		return $this;
	}

	/**
	 * Sets the field by name to use for sorting which will be
	 * turned into sortInfo during render.
	 * 
	 * @param string $sort
	 */
	public function setSort($sort) {
		$this->_sort = $sort;
		return $this;
	}

	/**
	 * Adds a field to the grid.
	 * 
	 * @param string $field Field name.
	 * @param string $name Name to use for the field. If none is given then the id is used.
	 * @param array $params Optional properties for the field (width, formatter, etc).
	 * @return this
	 */
	public function addField($field, $name = null, array $params = array()) {
		(null !== $name) ? $params['name'] = $name : $params['name'] = $field;

		$this->_fields[] = $params;
		return $this;
	}

	/**
	 * Adds fields to the grid instance.
	 * 
	 * @param array $fields
	 * @return this
	 */
	public function addFields(array $fields = array()) {
		foreach ($fields as $data) {
			if (!isset($data['field']))
				throw new Zend_Exception('Each field requires a field property.');
			$field = $data['field'];

			if (isset($data['name'])) {
				$name = $data['name'];
				unset($data['name']);
			} else {
				$name = $field;
			}

			$this->addField($field, $name, $data);
		}

		return $this;
	}

	/**
	 * Adds plugins to the grid (Enhanced only)
	 * 
	 * @param array $plugins
	 * @return this
	 */
	public function addPlugins(array $plugins = array()) {
		foreach ($plugins as $plugin => $props) {
			if (!is_array($props)) {
				if (is_int($plugin)) {
					$plugin = $props;
					$props = array();
				} else {
					$props = array();
				}
			} else {
				if (is_int($plugin)) {
					if (!isset($props['plugin']))
						throw new Zend_Exception('Each plugin requires a plugin property.');
					$plugin = $props['plugin'];
				}
			}

			$this->addPlugin($plugin, $props);
		}

		return $this;
	}

	/**
	 * Adds a plugin to the grid (Enhanced only).
	 * 
	 * @param string $plugin Plugin name.
	 * @param array $props Optional properties for the plugin.
	 * @return this
	 */
	public function addPlugin($plugin, array $props = array()) {
		$this->_plugins[$plugin] = $props;
		return $this;
	}
}
