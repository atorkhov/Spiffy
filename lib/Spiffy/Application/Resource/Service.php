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
        'autoloadHelpers' => true,
        'paths' => array(
            '/configs'
        ),
        'file' => 'services.yml'
    );
    
    /**
    * Array of helpers to autoload.
    * @var array
    */
    protected $helpers = array(
        'Spiffy_Controller_Action_Helper_ServiceContainer', 
        'Spiffy_Controller_Action_Helper_Get'
    );

    /**
     * (non-PHPdoc)
     * @see Zend_Application_Resource_Resource::init()
     */
    public function init()
    {
        $container = $this->getServiceContainer($this->getOptions());

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

        if ($options['autoloadHelpers']) {
            $this->_registerHelpers();
        }

        return $container;
    }
    
    /**
    * Register action helpers.
    */
    protected function _registerHelpers()
    {
        foreach ($this->helpers as $helperClass) {
            $helper = new $helperClass();
            Zend_Controller_Action_HelperBroker::addHelper($helper);
        }
    }
}
