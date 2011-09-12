<?php
namespace Spiffy\Account\Form;
use Spiffy\Zend\Form as BaseForm;

class Activate extends BaseForm
{
    /**
     * (non-PHPdoc)
     * @see Zend_Form::init()
     */
    public function init()
    {
        $this->add('username');
        $this->add('verificationCode');
        $this->add('activate', ($this->_dojoEnabled) ? 'SubmitButton' : 'submit', array(
            'ignore' => true,
            'label' => 'Activate'
        ));
    }
}