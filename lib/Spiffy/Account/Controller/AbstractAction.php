<?php
namespace Spiffy\Account\Controller;
use Spiffy\Doctrine\AbstractEntity,
    Zend_Controller_Action;

abstract class AbstractAction extends Zend_Controller_Action
{
    public function getFormOptions()
    {
        $opts = $this->getInvokeArg('spiffy');
        return isset($opts['account']['formOptions']) ? $opts['account']['formOptions'] : array();
    }
    
    public function getUserEntity()
    {
        $opts = $this->getInvokeArg('spiffy');
        if (!$opts['account']['userEntity']) {
            throw new Exception\InvalidEntity('You must define a user entity for this controller');
        }
        return $opts['account']['userEntity'];
    }
}