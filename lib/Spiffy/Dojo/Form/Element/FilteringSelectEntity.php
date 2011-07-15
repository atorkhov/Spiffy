<?php

class Spiffy_Dojo_Form_Element_FilteringSelectEntity extends Spiffy_Dojo_Form_Element_ComboBoxEntity
{
	/**
	 * Use formSelect view helper by default
	 * @var string
	 */
	public $helper = 'FilteringSelect';

	/**
	 * Flag: autoregister inArray validator?
	 * @var bool
	 */
	protected $_registerInArrayValidator = true;
}
