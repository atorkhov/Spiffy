<?php
namespace Spiffy\Calendar;
use Zend_Date;

class Week extends Zend_Date
{
	protected $_days = array();

	public function getCalendarDays() {
		for ($day = 0; $day < 7; $day++) {
			if (!isset($this->_days[$day])) {
				$this->setDayOfWeek($day);
			}
		}

		return $this->_days;
	}

	public function getDayOfWeek($weekday) {
		if (!isset($this->_days[$weekday])) {
			$this->setDayOfWeek($weekday);
		}
		return $this->_days[$weekday];
	}

	public function setDayOfWeek($weekday, $date = null) {
		if (null === $date) {
			$date = clone $this;
			$date->addDay($weekday);
		}

		$this->_days[$weekday] = new \Spiffy\Calendar\Day($date);
		return $this;
	}

	protected function _generateDays() {
		$date = clone $this;
		for ($day = 0; $day < 7; $day++) {
			$this->_days[$day] = new \Spiffy\Calendar\Day($date);
			$date->addDay(1);
		}
	}
}
