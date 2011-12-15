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
* @package    Spiffy_Application
* @copyright  Copyright (c) 2011 Kyle Spraggs (http://www.spiffyjr.me)
* @license    http://www.spiffyjr.me/license     New BSD License
*/

use Spiffy\Doctrine\Container;

class Spiffy_Zend_Application_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract
{
    protected $_options = array(
        'defaultCacheKey' => 'default',
        'defaultConnectionKey' => 'default',
        'defaultEventManagerKey' => 'default',
        'defaultEntityManagerKey' => 'default',
        'cache' => array(
            'default' => array(
                'adapter' => array(
                    'class' => 'Doctrine\Common\Cache\ArrayCache'
                )
            )
        ),
        'evm' => array(
            'default' => array(
                'class' => 'Doctrine\Common\EventManager',
                'subscribers' => array()
            )
        ),
        'dbal' => array(
            'connection' => array(
                'default' => array(
                    'eventManager' => 'default',
                    'dbname' => '',
                    'user' => 'root',
                    'password' => '',
                    'host' => 'localhost',
                    'driver' => 'pdo_mysql'
                )
            ),
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
                        'registry' => array(
                            'files' => array(
                                'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php',
                                'Spiffy/Doctrine/Annotations/Filters/Zend.php',
                                'Spiffy/Doctrine/Annotations/Validators/Zend.php'
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
                    ),
                    'customStringFunctions' => array(
                    )
                )
            )
        )
    );

    public function init()
    {
        $container = new Spiffy\Doctrine\Container($this->getOptions());

        Zend_Registry::set('Spiffy_Doctrine', $container);
        
        return $container;
    }
}
