<?php
namespace Spiffy\Doctrine\Exception;
use Zend_Exception;

class InvalidConnection extends Zend_Exception
{
    public function __construct($msg = '')
    {
        parent::__construct($msg, 500);
    }
}