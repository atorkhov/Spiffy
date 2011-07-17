<?php
namespace Spiffy\Dojo;
use Doctrine\DBAL\Types\Type;
use Spiffy\Form as SpiffyForm;
use Zend_Dojo;

abstract class Form extends SpiffyForm
{
	/**
	 * Default elements for dojo enabled Zend_Form.
	 * 
	 * @var array
	 */
	protected $_defaultElements = array(
		Type::SMALLINT => 'NumberSpinner',
		Type::BIGINT => 'NumberSpinner',
		Type::INTEGER => 'NumberSpinner',
		Type::BOOLEAN => 'CheckBox',
		Type::DATE => 'DateTextBox',
		Type::DATETIME => 'DateTextBox',
		Type::DATETIMETZ => 'DateTextBox',
		Type::DECIMAL => 'NumberSpinner',
		Type::OBJECT => null,
		Type::TARRAY => null,
		Type::STRING => 'TextBox',
		Type::TEXT => 'Textarea',
		Type::TIME => 'TimeTextBox');

	/**
	 * Constructor
	 *
	 * @param  array|Zend_Config|null $options
	 * @return void
	 */
	public function __construct($options = null) {
		Zend_Dojo::enableForm($this);

		// enable spiffy namespace dojo elements
		$this
			->addPrefixPath('Spiffy_Dojo_Form_Decorator', 'Spiffy/Dojo/Form/Decorator', 'decorator')
			->addPrefixPath('Spiffy_Dojo_Form_Element', 'Spiffy/Dojo/Form/Element', 'element')
			->addElementPrefixPath('Spiffy_Dojo_Form_Decorator', 'Spiffy/Dojo/Form/Decorator',
				'decorator')
			->addDisplayGroupPrefixPath('Spiffy_Dojo_Form_Decorator', 'Spiffy/Dojo/Form/Decorator')
			->setDefaultDisplayGroupClass('Spiffy_Dojo_Form_DisplayGroup');

		// enable dojo view helpers
		$this->getView()->addHelperPath('Spiffy/Dojo/View/Helper', 'Spiffy_Dojo_View_Helper');

		parent::__construct($options);
	}
}
