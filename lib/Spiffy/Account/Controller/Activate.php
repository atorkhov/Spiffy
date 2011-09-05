<?php
namespace Spiffy\Account\Controller;
use Spiffy\Account\Controller\AbstractAction;

class Activate extends AbstractAction
{
    protected $_data = array();
    
    public function preDispatch()
    {
        $_GET['c'] = urlencode(base64_encode(serialize(array(
        	'username' => 'spiffyjr',
        	'code' => 'yd5tlq'
        ))));

        if (!$this->_request->c) {
            $this->_helper->redirector->gotoSimple('index', 'index');
        }
        
        if (!$this->_data = @unserialize(base64_decode(urldecode($this->_request->c)))) {
            $this->_helper->redirector->gotoSimple('index', 'index');
        }
    }
    
    public function indexAction()
    {
        $account = $this->_helper->get('account');
        $doctrine = $this->_helper->get('doctrine');
        
        $repo = $doctrine->getEntityManager()->getRepository($this->getUserEntity());
        $user = $repo->findOneBy(array('username' => $this->_data['username']));
        
        if (!$user) {
            throw new Exception\UserNotFound('The requested user does not exist');
        }
        
        if ($account->activateUser($user, $this->_data['code'])) {
            return $this->_forward('success');
        }
        
        $this->_forward('invalid');
    }
    
    public function invalidAction()
    {
    }
    
    public function successAction()
    {
    }
}