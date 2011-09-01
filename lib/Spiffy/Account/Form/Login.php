<?php
namespace Spiffy\Account\Form;
use Spiffy\Zend\Form as BaseForm;

class Login extends BaseForm
{
    /**
     * (non-PHPdoc)
     * @see Zend_Form::init()
     */
    public function init()
    {
        $this->add('username');
        $this->add('password', $this->_dojoEnabled ? 'PasswordTextBox' : 'password');
        $this->add('login', ($this->_dojoEnabled) ? 'SubmitButton' : 'submit', array(
            'ignore' => true,
            'label' => 'Register'
        ));
    }
}