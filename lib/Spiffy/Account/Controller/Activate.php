<?php
namespace Spiffy\Account\Controller;
use Spiffy\Account\Controller\AbstractAction,
    Spiffy\Account\Form\Activate as ActivateForm;

class Activate extends AbstractAction
{
    public function preDispatch()
    {
        $account = $this->_helper->get('account');
        $request = $this->getRequest();

        if ($request->getParam('c')) {
            if ($account->activateUser($request->getParam('c'), $this->getUserEntity())) {
                return $this->_forward('success');
            }
            return $this->_foward('invalid');
        }
    }
    
    public function indexAction()
    {
        $entity = $this->getUserEntity();
        
        $form = new ActivateForm(new $entity, $this->getFormOptions());
        $request = $this->getRequest();
        
        if ($request->getParam('u')) {
            $form->setDefaults(array('username' => $request->getParam('u')));
        }
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $account = $this->_helper->get('account');
            $code = $account->generateUriParameter(
                $form->getValue('username'),
                $form->getValue('verificationCode')
            );
            
            if ($account->activateUser($code, $this->getUserEntity())) {
                return $this->_forward('success');
            }
            $form->addErrorMessage('The code you attempted to use was invalid. Please try again.');
        }
        
        $this->view->form = $form;
    }
    
    public function invalidAction()
    {
    }
    
    public function successAction()
    {
    }
}