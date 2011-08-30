<?php

namespace Spiffy\Doctrine\Service;
use Zend_Registry;

class Doctrine
{
	public function get() 
	{
	    if (Zend_Registry::isRegistered('Spiffy_Doctrine')) {
		    return Zend_Registry::get('Spiffy_Doctrine');
	    }
	    return null;
	}
}
