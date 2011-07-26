<?php

use Doctrine\ORM\EntityRepository;
use Spiffy\Form;

class Spiffy_Dojo_Form_Element_ComboBoxEntity extends Zend_Dojo_Form_Element_ComboBox
{
	/**
	 * Entity manager.
	 * 
	 * @var Doctrine\ORM\EntityManager
	 */
	protected $_entityManager = null;

	/**
	 * Entity class.
	 *
	 * @var string
	 */
	protected $_class = null;

	/**
	 * Query builder closure for fetching elements.
	 *
	 * @var Closure
	 */
	protected $_queryBuilder = null;

	/**
	 * Cached query result.
	 * 
	 * @var array
	 */
	protected $_queryResult = null;

	/**
	 * Flag: autoregister inArray validator?
	 * @var bool
	 */
	protected $_registerInArrayValidator = false;

	/**
	 * Case filter used for determining getters.
	 *
	 * @var Zend_Filter_Word_UnderscoreToCamelCase
	 */
	protected static $_caseFilter = null;

	/**
	 * Constructor
	 *
	 * @param  mixed $spec
	 * @param  mixed $options
	 * @return void
	 */
	public function __construct($spec, $options = null) {
		if (null === self::$_caseFilter) {
			self::$_caseFilter = new Zend_Filter_Word_UnderscoreToCamelCase();
		}

		$this->_entityManager = Zend_Registry::get('Spiffy_Container')->getEntityManager();
		parent::__construct($spec, $options);
	}

	/**
	 * Retrieve options array
	 *
	 * @return array
	 */
	protected function _getMultiOptions() {
		if (null === $this->options || !is_array($this->options)) {
			$this->options = array();
		}

		if (empty($this->options) && $this->getClass()) {
			if (null === $this->_queryResult) {
				$qb = call_user_func($this->getQueryBuilder(),
					$this->_entityManager->getRepository($this->_class));
				$this->_queryResult = $qb->getQuery()->execute();
			}

			$md = $this->_entityManager->getClassMetadata($this->_class);

			foreach ($this->_queryResult as $row) {
				if (is_array($row)) {
					throw new Zend_Exception(
						'formEntity expects a result of objects: did you for an array with select?');
				}

				$id = array();
				foreach ($md->identifier as $field) {
					$getter = 'get' . ucfirst(self::$_caseFilter->filter($field));
					if (method_exists($row, $getter)) {
						$value = $row->$getter();
					} elseif (isset($this->{$key}) || property_exists($row, $key)) {
						$value = $row->$key;
					} else {
						throw new Zend_Exception(
							"property '{$field}' is not public: perhaps add {$getter}()?");
					}

					$id[] = $value;
				}
				$this->options[implode(':', $id)] = (string) $row;
			}
		}

		return $this->options;
	}

	/**
	 * (non-PHPdoc)
	 * @see Zend_Form_Element::setOptions()
	 */
	public function setOptions(array $options) {
		if (isset($options['class'])) {
			if (isset($options['multiOptions'])) {
				unset($options['multiOptions']);
			}

			$this->setClass($options['class']);
			unset($options['class']);
		}

		if (isset($options['queryBuilder'])) {
			$this->setQueryBuilder($options['queryBuilder']);
			unset($options['queryBuilder']);
		} else {
			$qb = function (EntityRepository $er) {
				return $er->createQueryBuilder('e');
			};
			$this->setQueryBuilder($qb);
		}

		$this->_getMultiOptions();

		parent::setOptions($options);
	}

	/**
	 * Set query builder.
	 * 
	 * @param Closure $qb
	 */
	public function setQueryBuilder(Closure $qb) {
		$this->_queryBuilder = $qb;
	}

	/**
	 * Get query builder.
	 * 
	 * @return Closure
	 */
	public function getQueryBuilder() {
		return $this->_queryBuilder;
	}

	/**
	 * Set entity class.
	 *  
	 * @param string $class
	 */
	public function setClass($class) {
		$this->_class = $class;
	}

	/**
	 * Get entity class.
	 * 
	 * @return string
	 */
	public function getClass() {
		return $this->_class;
	}

	/**
	 * (non-PHPdoc)
	 * @see Zend_Form_Element::render()
	 */
	public function render(Zend_View_Interface $view = null) {
		return parent::render($view);
	}
}
