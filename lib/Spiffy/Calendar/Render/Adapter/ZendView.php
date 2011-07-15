<?php
namespace Spiffy\Calendar\Render\Adapter;
use Zend_Exception;

class ZendView extends \Spiffy\Calendar\Render\Adapter\AbstractRender
{
	protected $_options = array('scriptPath' => '/', 'viewScript' => 'calendar.phtml');

	public function render() {
		$viewRenderer = \Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$view = clone $viewRenderer->view;
		$view->clearVars();
		$view->calendar = $this->getCalendar();
		$view->addScriptPath($this->_options['scriptPath']);

		try {
			return $view->render($this->_options['viewScript']);
		} catch (Zend_Exception $e) {
			return $e->getMessage();
		}
	}
}
