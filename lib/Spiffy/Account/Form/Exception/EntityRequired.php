<?php

namespace Spiffy\Auth\Form\Exception;
use Zend_Exception;

class EntityRequired extends Zend_Exception
{
    public function __construct($msg = '')
    {
        parent::__construct($msg, 500);
    }
}
