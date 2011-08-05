<?php
/**
* Spiffy Framework
*
* LICENSE
*
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.
* It is also available through the world-wide-web at this URL:
* http://www.spiffyjr.me/license
*
* @category   Spiffy
* @package    Spiffy_ZFDebug
* @copyright  Copyright (c) 2011 Kyle Spraggs (http://www.spiffyjr.me)
* @license    http://www.spiffyjr.me/license     New BSD License
*/

class Spiffy_ZFDebug_Doctrine2 extends ZFDebug_Controller_Plugin_Debug_Plugin 
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{

    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'database';

    /**
     * @var array
     */
    protected $_em = array();

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Variables
     *
     * @param Zend_Db_Adapter_Abstract|array $adapters
     * @return void
     */
    public function __construct(array $options = array())
    {
        if (isset($options['entityManagers'])) {
            $this->_em = $options['entityManagers'];
        }
    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        if (!$this->_em)
            return 'No entity managers';

        foreach ($this->_em as $em) {
            if ($logger = $em->getConnection()->getConfiguration()->getSqlLogger()) {
                $totalTime = 0;
                foreach($logger->queries as $query) {
                    $totalTime += $query['executionMS'];
                }
                $adapterInfo[] = count($logger->queries) . ' in ' . round($totalTime * 1000, 2) . ' ms';
            }
        }
        $html = implode(' / ', $adapterInfo);

        return $html;
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        if (!$this->_em)
            return '';
        
        $html = '<h4>Database queries</h4>';

        foreach ($this->_em as $name => $em) {
            $html .= "<h4>{$name}</h4>";
            if ($logger = $em->getConnection()->getConfiguration()->getSqlLogger()) {
                $html .= "<ol>";
                foreach($logger->queries as $query) {
                    $html .= '<li><strong>['.round($query['executionMS']*1000, 2).' ms]</strong> '
                             .htmlspecialchars($query['sql']).'</li>';
                }
                $html .= '</ol>';
            } else {
                $html .= "No logger enabled!";
            }
        }

        return $html;
    }

}