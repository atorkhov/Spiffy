<?php
namespace Spiffy\Domain\Exception;
use Zend_Exception;

class InvalidProperty extends Zend_Exception
{
	public function __construct($msg = '') {
		parent::__construct($msg, 500);
	}
}
