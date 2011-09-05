<?php
namespace Spiffy\Account\Controller;
use Spiffy\Account\Form\Register as RegisterForm,
    Spiffy\Account\Controller\AbstractAction;

class Register extends AbstractAction
{
    public function indexAction()
    {
        $entity = $this->getUserEntity();
        
        $form = new RegisterForm(new $entity, $this->getFormOptions());
        $request = $this->getRequest();
    
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $this->_helper->get('account')->createUser($form->getEntity());
        }
        $this->view->form = $form;
    }
}