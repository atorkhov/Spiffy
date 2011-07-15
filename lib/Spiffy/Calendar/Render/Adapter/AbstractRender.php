<?php
namespace Spiffy\Calendar\Render\Adapter;
use Spiffy\Calendar, Zend_Loader, Zend_Exception;

class AbstractRender
{
	protected $_options = array();
	protected $_calendar;

	public function __construct($calendar, array $options = array()) {
		$this->_calendar = $calendar;
		$this->_options = $options;
	}

	public function getCalendar() {
		return $this->_calendar;
	}
}
