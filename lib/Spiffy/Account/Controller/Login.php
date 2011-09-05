<?php
namespace Spiffy\Account\Controller;
use Spiffy\Account\Form\Login as LoginForm,
    Spiffy\Account\Controller\AbstractAction;

class Login extends AbstractAction
{
    public function indexAction()
    {
        $entity = $this->getUserEntity();
        
        $form = new LoginForm(new $entity, $this->getFormOptions());
        $request = $this->getRequest();
    
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $result = $this->_helper->get('security')->login(
                $form->getValue('username'),
                $form->getValue('password'),
                $entity
            );
    
            if ($result->isValid()) {
                $this->_helper->redirector->gotoSimple('index', 'index', 'web');
            } else {
                $form->addErrorMessages($result->getMessages());
            }
        }
        $this->view->form = $form;
    }
}