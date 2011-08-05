<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Spiffy_Application_Resource_Service extends Zend_Application_Resource_ResourceAbstract
{
    
    /**
     * Class options.
     * @var array
     */
    protected $_options = array(
        'autoloadActionHelpers' => true,
    	'autoloadViewHelpers' => true,
        'paths' => array(
            '/configs'
        ),
        'file' => 'services.yml'
    );
    
    /**
    * Array of action helpers to autoload.
    * @var array
    */
    protected $_actionHelpers = array(
        'Spiffy_Controller_Action_Helper_ServiceContainer', 
        'Spiffy_Controller_Action_Helper_Get'
    );
    
    /**
    * Array of view helpers to autoload.
    * @var array
    */
    protected $_viewHelpers = array(
        'get' => 'Spiffy_View_Helper_Get' 
    );

    /**
     * (non-PHPdoc)
     * @see Zend_Application_Resource_Resource::init()
     */
    public function init()
    {
        $options = $this->getOptions();
        
        if ($options['autoloadActionHelpers']) {
            $this->_registerActionHelpers();
            unset($options['autoloadActionHelpers']);
        }
        
        if ($options['autoloadViewHelpers']) {
            $this->_registerViewHelpers();
            unset($options['autoloadViewHelpers']);
        }
        
        $container = $this->getServiceContainer($options);

        Zend_Registry::set('Spiffy_Service', $container);

        return $container;
    }

    /**
     * Registers the Symfony service container (with DI).
     *
     * @param array $options
     * @return Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function getServiceContainer(array $options)
    {
        $paths = $options['paths'];

        if (!is_array($paths)) {
            $paths = array(
                $paths
            );
        }

        $locator = new FileLocator($paths);

        $container = new ContainerBuilder();

        $loader = new YamlFileLoader($container, $locator);
        $loader->load($options['file']);

        return $container;
    }
    
    /**
    * Register action helpers.
    */
    protected function _registerActionHelpers()
    {
        foreach ($this->_actionHelpers as $helperClass) {
            Zend_Controller_Action_HelperBroker::addHelper(new $helperClass());
        }
    }
    
    /**
    * Register action helpers.
    */
    protected function _registerViewHelpers()
    {
        $this->_bootstrap->bootstrap('view');
        $view = $this->_bootstrap->getResource('view');

        foreach ($this->_viewHelpers as $helper => $helperClass) {
            $view->registerHelper(new $helperClass(), $helper);
        }
    }
}
