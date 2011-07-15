<?php
namespace Spiffy\Calendar;
use Spiffy\Calendar, Zend_Loader, Zend_Exception;

final class Render
{
	const ADAPTER_ZENDVIEW = 'ZendView';
	const DEFAULT_ADAPTER = self::ADAPTER_ZENDVIEW;

	public static function factory(Calendar $calendar, array $options = array()) {
		if (!isset($options['adapter'])) {
			$options['adapter'] = self::DEFAULT_ADAPTER;
		}

		try {
			$adapter = "Spiffy\\Calendar\\Render\\Adapter\\{$options['adapter']}";
			Zend_Loader::loadClass($adapter);
		} catch (Zend_Exception $e) {
			return $e->getMessage();
		}

		return new $adapter($calendar, $options);
	}
}
