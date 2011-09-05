<?php
namespace Spiffy\Account\Form;
use Spiffy\Zend\Form as BaseForm;

class Register extends BaseForm
{
    /**
     * (non-PHPdoc)
     * @see Zend_Form::init()
     */
    public function init()
    {
        $passwordElement = ($this->_dojoEnabled) ? 'PasswordTextBox' : 'password';
        
        $this->add('username');
        $this->add('email');
        $this->add('password', $passwordElement);
        $this->add('confirmPassword', $passwordElement, array(
            'validators' => $this->password->getValidators(),
            'required' => true
        ));
        $this->add('register', ($this->_dojoEnabled) ? 'SubmitButton' : 'submit', array(
            'ignore' => true,
            'label' => 'Register'
        ));
    }
    
    /**
     * (non-PHPdoc)
     * @see Spiffy\Zend.Form::isValid()
     */
    public function isValid($data)
    {
        $valid = parent::isValid($data);
        
        $em = $this->get('doctrine')->getEntityManager();
        $repo = $em->getRepository(get_class($this->getEntity()));
        
        if ($repo->findOneBy(array('username' => $this->username->getValue()))) {
            $this->username->addErrorMessage('That username is already in use')->markAsError();
            $valid = false;
        }
        
        if ($repo->findOneBy(array('email' => $this->email->getValue()))) {
            $this->email->addErrorMessage('That email address is already in use')->markAsError();
            $valid = false;
        }
        
        if ($this->password->getValue() != $this->confirmPassword->getValue()) {
            $this->addErrorMessage('The passwords you entered do not match')->markAsError();
            $valid = false;
        }
        
        return $valid;
    }
}