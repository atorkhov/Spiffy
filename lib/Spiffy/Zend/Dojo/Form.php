<?php

namespace Spiffy\Zend\Dojo;
use Doctrine\DBAL\Types\Type,
    Spiffy\Zend\Form as BaseForm,
    Zend_Dojo;

abstract class Form extends BaseForm
{
    /**
    * Default elements for Zend_Form.
    * @var array
    */
    protected $_defaultElements = array(
        Type::SMALLINT      => 'NumberSpinner',
        Type::BIGINT        => 'NumberSpinner',
        Type::INTEGER       => 'NumberSpinner',
        Type::BOOLEAN       => 'CheckBox',
        Type::DATE          => 'DateTextBox',
        Type::DATETIME      => 'DateTextBox',
        Type::DATETIMETZ    => 'DateTextBox',
        Type::DECIMAL       => 'NumberSpinner',
        Type::OBJECT        => null,
        Type::TARRAY        => null,
        Type::STRING        => 'TextBox',
        Type::TEXT          => 'Textarea',
        Type::TIME          => 'TimeTextBox',
        'TO_ONE'            => 'ForeignKey'
    );

    /**
     * Constructor
     *
     * @param  array|Zend_Config|null $options
     * @return void
     */
    public function __construct($options = null)
    {
        Zend_Dojo::enableForm($this);

        $this->addPrefixPath(
        	'Spiffy_Zend_Dojo_Form_Element',
        	'Spiffy/Zend/Dojo/Form/Element', 
        	'element'
    	);

        parent::__construct($options);
    }
}
