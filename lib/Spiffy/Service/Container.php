<?php
namespace Spiffy\Service;

class Container
{

	/**
	 * Parameters parsed from the config file.
	 * 
	 * @var array
	 */
	protected $_parameters = array();

	/**
	 * Services parsed from the config file.
	 * 
	 * @var array
	 */
	protected $_services = array();

	/**
	 * Constructor.
	 * 
	 * @param array $options
	 */
	public function __construct(array $options = array()) {
		$this->setOptions($options);
	}

	/**
	 * Get a service.
	 * 
	 * @param string $service
	 * @throws Exception\InvalidService
	 */
	public function get($service) {
		if (!isset($this->_services[$service])) {
			throw new Exception\InvalidService("no such service '{$service}' exists");
		}
		$service = $this->_services[$service];
		$serviceClass = $service['class'];

		$argumentList = $this->_createArguments($service['arguments']);

		$eval = "return new {$serviceClass}({$argumentList});";
		return eval($eval);
	}

	/**
	 * Sets the configuration.
	 * 
	 * @param mixed $config
	 * @throws Exception\InvalidConfiguration
	 * @throws Exception\InvalidService
	 */
	public function setConfiguration($config) {
		if (is_string($config)) {
			if (!preg_match('/^.+\.(ini|xml|json|yml|yaml)$/i', $config, $matches)) {
				throw new Exception\InvalidConfiguration(
					'failed to identify the type of Zend_Config instance');
			}

			$type = strtolower($matches[1]);
			if ($type == 'yml') {
				$type = 'yaml';
			}

			$configClass = "Zend_Config_" . ucfirst($type);
			$config = new $configClass($config);
			$config = $config->toArray();
		} elseif (is_array($config)) {
			;
		} elseif ($config instanceof Zend_Config) {
			$config = $config->toArray();
		} else {
			throw new Exception\InvalidConfiguration('unknown configuration type');
		}

		foreach ($config['parameters'] as $parameter => $value) {
			$this->_parameters[$parameter] = $value;
		}

		foreach ($config['services'] as $service => $options) {
			if (!isset($options['class'])) {
				throw new Exception\InvalidService("class is a required option for '${service}'");
			}

			$options['arguments'] = isset($options['arguments']) ? $options['arguments'] : array();

			if (isset($options['name'])) {
				$service = $options['name'];
			}
			$this->_services[$service] = $this->_swapParameters($options);
		}
	}

	/**
	 * Sets options from an array.
	 * 
	 * @param array $options
	 * @throws Exception\InvalidOption
	 */
	public function setOptions(array $options) {
		foreach ($options as $key => $value) {
			switch (strtolower(trim($key))) {
				case 'configuration':
					$this->setConfiguration($value);
					break;
				default:
					throw new Exception\InvalidOption("unknown option '{$key}'");
			}
		}
	}

	/**
	 * Creates arguments from an array. Arguments can be prepended with special
	 * characters to denote special argument types. Prepending with "!" signifies
	 * the code should be ran as shown (ex: !Zend_Auth::getInstance()). Using an 
	 * "@" denotes that the argument is a service and should be loaded from the
	 * container.
	 * 
	 * @param array $args
	 * @throws Exception\InvalidParameter
	 * @return string
	 */
	protected function _createArguments(array $args) {
		$list = '';
		foreach ($args as $arg) {
			if (substr($arg, 0, 1) == '!') {
				$list .= trim($arg, '!');
			} elseif (substr($arg, 0, 1) == '@') {
				$arg = trim($arg, '@');
				if (!isset($this->_services[$arg])) {
					throw new Exception\InvalidParameter(
						"found service parameter '{$arg}' but no such service exists");
				}
			} else {
				$list .= "'" . addslashes($arg) . "'";
			}
		}
		return $list;
	}

	/**
	 * Recursively swap parameters.
	 * 
	 * @param array $array
	 * @throws Exception\InvalidParameter
	 * @return array
	 */
	protected function _swapParameters(array $array) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = $this->_swapParameters($value);
				continue;
			}

			if (preg_match('/^%(.+)%$/', $value, $matches)) {
				if (!isset($this->_parameters[$matches[1]])) {
					throw new Exception\InvalidParameter(
						"parameter '{$matches[1]}' was found but not defined");
				}
				$array[$key] = $this->_parameters[$matches[1]];
			}
		}

		return $array;
	}
}
