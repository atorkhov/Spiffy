<?php
namespace Spiffy\Calendar;
use Zend_Date;

class Year extends Zend_Date
{
	protected $_months = array();

	public function getCalendarMonth($month) {
		if (!isset($this->_months[$month])) {
			$this->setCalendarMonth($month);
		}

		return $this->_months[$month];
	}

	public function setCalendarMonth($month, $date = null) {
		if (null === $date) {
			$date = clone $this;
			$date->addMonth($month - 1);
		}

		$this->_months[$month] = new \Spiffy\Calendar\Month($date);
		return $this;
	}
}
