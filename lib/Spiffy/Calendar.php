<?php
namespace Spiffy;
use Zend_Date, Zend_Locale, Spiffy\Calendar\Render\Exception;

class Calendar
{
    /**
     * Zend_Date used for determining "center" of calendar
     * for default day, month, and year when none are specified
     * for the respective getter methods.
     *
     * @var Zend_Date
     */
    protected $_date;

    /**
     * @var array
     */
    protected $_dayNames = array();

    /**
     * @var array
     */
    protected $_options = array(
        'startOfWeek' => 0
    );

    /**
     * @var array
     */
    protected $_years = array();

    /**
     * Constructor. Sets options, generates week array, and
     * optionally runs child class initilization.
     *
     * @param array $options Array of options to setup on class instantiation.
     */
    public function __construct(array $options = array())
    {
        $this->_date = new Zend_Date();

        $this->setOptions($options);
        $this->init();
    }

    public function __toString()
    {
        try
        {
            return $this->render();
        } catch (Exception $e)
        {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return '';
    }

    /**
     * Initilization for children.
     * @return void
     */
    public function init()
    {
    }

    public function render()
    {
        return \Spiffy\Calendar\Render::factory($this, $this->_options['render'])->render();
    }

    public function getYear($year)
    {
        if (!isset($this->_years[$year])) {
            $this->_years[$year] = new \Spiffy\Calendar\Year(
                array(
                    'day' => '1', 'month' => '1', 'year' => $year
                ));
        }

        return $this->_years[$year];
    }

    public function getMonth($month = null, $year = null)
    {
        if (null === $year) {
            $year = $this->_date->get(Zend_Date::YEAR);
        }

        if (null === $month) {
            $month = $this->_date->get(Zend_Date::MONTH_SHORT);
        }

        return $this->getYear($year)->getCalendarMonth($month);
    }

    public function getWeek($week, $month, $year)
    {
        return $this->getYear($year)->getCalendarMonth($month)->getCalendarWeek($week);
    }

    public function getDay($day, $month, $year)
    {
        return $this->getYear($year)->getCalendarMonth($month)->getCalendarDay($day);
    }

    public function getDayNames(array $format = array())
    {
        if (empty($this->_dayNames)) {
            $dayNames = Zend_Locale::getTranslationList('day', $this->_date->getLocale(), $format);

            $start = $this->_options['startOfWeek'];
            while ($start--) {
                $tmp = array_shift($dayNames);
                $dayNames[] = $tmp;
            }

            $this->_dayNames = $dayNames;
        }

        return $this->_dayNames;
    }

    public function getDate()
    {
        return $this->_date;
    }

    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch (strtolower(trim($key))) {
                case 'day':
                    $this->_date->setDay($value);
                    break;
                case 'month':
                    $this->_date->setMonth($value);
                    break;
                case 'year':
                    $this->_date->setYear($value);
                    break;
            }
        }

        if (!isset($options['render'])) {
            $options['render'] = array();
        }

        $this->_options = array_merge($this->_options, $options);
        return $this;
    }
}
