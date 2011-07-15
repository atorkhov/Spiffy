<?php
namespace Spiffy\Calendar;
use Zend_Date;

class Month extends Zend_Date
{
	protected $_weeks = array();

	public function getCalendarDays() {
		$days = array();
		foreach ($this->_weeks as $week) {
			$days = array_merge($days, $week->getCalendarDays());
		}
		return $days;
	}

	public function getCalendarWeeks() {
		// TODO: Change to number of weeks in the month
		$numDays = $this->get(Zend_Date::MONTH_DAYS);
		echo ($numDays + $this->get(Zend_Date::WEEKDAY_DIGIT)) / 7;
		exit;
		$numWeeks = floor(($numDays + $this->get(Zend_Date::WEEKDAY_DIGIT)) / 7) + 1;
		echo $numWeeks;
		exit();
		for ($week = 0; $week < 7; $week++) {
			if (!isset($this->_weeks[$week])) {
				$this->setCalendarWeek($week);
			}
		}
		return $this->_weeks;
	}

	public function getCalendarDay($day) {
		return $this->getCalendarWeek($this->_dayToWeekNum($day))
			->getDayOfWeek($this->_dayToWeekdayDigit($day));
	}

	public function getCalendarWeek($week) {
		if (!isset($this->_weeks[$week])) {
			$this->setCalendarWeek($week);
		}

		return $this->_weeks[$week];
	}

	public function setCalendarWeek($week, $date = null) {
		if (null === $date) {
			$date = clone $this;
			$date->addWeek($week - 1);
			$date->subDay($date->get(Zend_Date::WEEKDAY_DIGIT));
		}

		$this->_weeks[$week] = new \Spiffy\Calendar\Week($date);
		return $this;
	}

	protected function _dayToWeekdayDigit($day) {
		$date = clone $this;
		$date->setDay($day);
		return $date->get(Zend_Date::WEEKDAY_DIGIT);
	}

	protected function _dayToWeekNum($day) {
		$date = clone $this;
		$date->setDay($day);
		$timestamp = $date->getTimestamp();
		$mod = date('w', $timestamp - ($day * 24 * 60)) - 1;
		return floor((date('d', $timestamp) + $mod) / 7);
	}
}
