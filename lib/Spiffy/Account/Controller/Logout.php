<?php
namespace Spiffy\Account\Controller;
use Zend_Controller_Action;

class Logout extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->get('security')->logout();
        $this->_helper->redirector->gotoSimple('index', 'index');
    }
}