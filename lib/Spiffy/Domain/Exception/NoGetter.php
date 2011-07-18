<?php
namespace Spiffy\Domain\Exception;
use Zend_Exception;

class NoGetter extends Zend_Exception
{
	public function __construct($msg = '') {
		parent::__construct($msg, 500);
	}
}
