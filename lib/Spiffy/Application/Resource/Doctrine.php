<?php

use Spiffy\Doctrine\Container;

class Spiffy_Application_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract
{
    protected $_options = array(
        'defaultCacheKey' => 'default',
        'defaultConnectionKey' => 'default',
        'defaultEntityManagerKey' => 'default',
        'cache' => array(
            'default' => array(
                'adapter' => array(
                    'class' => 'Doctrine\Common\Cache\ArrayCache'
                )
            )
        ),
        'dbal' => array(
            'connection' => array(
                'default' => array(
                    'dbname' => '',
                    'user' => 'root',
                    'password' => '',
                    'host' => 'localhost',
                    'driver' => 'pdo_mysql'
                )
            )
        ),
        'orm' => array(
            'em' => array(
                'default' => array(
                    'connection' => 'default',
                    'proxy' => array(
                        'autoGenerate' => true, 'dir' => 'Proxy', 'namespace' => 'Proxy'
                    ),
                    'cache' => array(
                        'metadata' => 'default', 'query' => 'default', 'result' => 'default'
                    ),
                    'mdata' => array(
                        'annotation' => array(
                            'files' => array(
                                'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php',
                                'Spiffy/Doctrine/Annotations/Filters/Filter.php',
                                'Spiffy/Doctrine/Annotations/Validators/Validator.php'
                            ),
                            'namespaces' => array()
                        ),
                        'driver' => array(
                            'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                            'paths' => array(
                                'Entity'
                            ),
                            'reader' => array(
                                'class' => 'Doctrine\Common\Annotations\AnnotationReader',
                                'aliases' => array()
                            )
                        )
                    )
                )
            )
        )
    );

    public function init()
    {
        $this->registerActionHelper();

        $container = new Spiffy\Doctrine\Container($this->getOptions());

        Zend_Registry::set('Spiffy_Doctrine', $container);

        return $container;
    }

    /**
     * Register the Doctrine acton helpers.
     */
    public function registerActionHelper()
    {
        Zend_Controller_Action_HelperBroker::addHelper(
            new Spiffy_Controller_Action_Helper_EntityManager());
    }
}
