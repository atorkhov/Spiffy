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
* @package    Spiffy_Doctrine
* @copyright  Copyright (c) 2011 Kyle Spraggs (http://www.spiffyjr.me)
* @license    http://www.spiffyjr.me/license     New BSD License
*/

namespace Spiffy\Doctrine;

use Closure;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use ReflectionClass;

class Container
{
    /**
     * Default cache key.
     * @var string
     */
    protected static $_defaultCacheKey = 'default';

    /**
     * Default connection key.
     * @var string
     */
    protected static $_defaultConnectionKey = 'default';
    
    /**
    * Default event manager key.
    * @var string
    */
    protected static $_defaultEventManagerKey = 'default';

    /**
     * Default entity manager key.
     * @var string
     */
    protected static $_defaultEntityManagerKey = 'default';

    /**
     * Cache instances.
     * @var array
     */
    protected $_caches = array();

    /**
     * Connection instances.
     * @var array
     */
    protected $_connections = array();
    
    /**
    * EventManager instances.
    * @var array
    */
    protected $_eventManagers = array();

    /**
     * EntityManager instances.
     * @var array
     */
    protected $_entityManagers = array();

    /**
     * Container options.
     * @var array
     */
    protected $_options = array();

    /**
     * Constructor.
     * 
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->setOptions($options);
    }

    /**
     * Set default cache key.
     * 
     * @param string $key
     */
    public static function setDefaultCacheKey($key)
    {
        self::$_defaultCacheKey = $key;
    }

    /**
     * Get default cache key.
     * 
     * @param string $key
     */
    public static function getDefaultCacheKey()
    {
        return self::$_defaultCacheKey;
    }

    /**
     * Set default connection key.
     *
     * @param string $key
     */
    public static function setDefaultConnectionKey($key)
    {
        self::$_defaultConnectionKey = $key;
    }

    /**
     * Get default connection key.
     *
     * @param string $key
     */
    public static function getDefaultConnectionKey()
    {
        return self::$_defaultConnectionKey;
    }
    
    /**
    * Set default event manager key.
    *
    * @param string $key
    */
    public static function setDefaultEventManagerKey($key)
    {
        self::$_defaultEventManagerKey = $key;
    }
    
    /**
     * Get default event manager key.
     *
     * @param string $key
     */
    public static function getDefaultEventManagerKey()
    {
        return self::$_defaultEventManagerKey;
    }

    /**
     * Set default entity manager key.
     *
     * @param string $key
     */
    public static function setDefaultEntityManagerKey($key)
    {
        self::$_defaultEntityManagerKey = $key;
    }

    /**
     * Get default entity manager key.
     *
     * @param string $key
     */
    public static function getDefaultEntityManagerKey()
    {
        return self::$_defaultEntityManagerKey;
    }

    /**
     * Sets options.
     * 
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch (strtolower(trim($key))) {
                default:
                    $setter = 'set' . ucfirst($key);
                    if (method_exists($this, $setter)) {
                        $this->$setter($value);
                        unset($options[$key]);
                    }
            }
        }
        $this->_options = $options;
    }

    /**
     * Get a cache instance.
     * 
     * @param string $cacheName
     * @return Doctrine\Common\Cache\AbstractCache
     */
    public function getCache($cacheName = null)
    {
        $cacheName = $cacheName ? $cacheName : self::getDefaultCacheKey();

        if (!isset($this->_caches[$cacheName])) {
            $this->_prepareCache($cacheName);
        }
        return $this->_caches[$cacheName];
    }

    /**
     * Get a connection instance.
     *
     * @param string $conName
     * @return Doctrine\DBAL\Connection
     */
    public function getConnection($conName = null)
    {
        $conName = $conName ? $conName : self::getDefaultConnectionKey();

        if (!isset($this->_connections[$conName])) {
            $this->_prepareConnection($conName);
        }
        return $this->_connections[$conName];
    }
    
    /**
    * Get an event manager instance.
    *
    * @param string $emName
    * @return Doctrine\Common\EventManager
    */
    public function getEventManager($evName = null)
    {
        $evName = $evName ? $evName : self::getDefaultEventManagerKey();
    
        if (!isset($this->_eventManagers[$evName])) {
            $this->_prepareEventManager($evName);
        }
        return $this->_eventManagers[$evName];
    }

    /**
     * Get an entity manager instance.
     * 
     * @param string $emName
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager($emName = null)
    {
        $emName = $emName ? $emName : self::getDefaultEntityManagerKey();

        if (!isset($this->_entityManagers[$emName])) {
            $this->_prepareEntityManager($emName);
        }
        return $this->_entityManagers[$emName];
    }
    
    /**
    * Gets multi options for Spiffy form elements.
    *
    * @param string $entityClass
    * @param Closure|null $qbClosure
    * @param string $emName
    * @return array
    */
    public function getMultiOptions($entityClass, $qbClosure = null, $emName = null)
    {
        if (!$qbClosure instanceof Closure) {
            $qbClosure = function (EntityRepository $er)
            {
                return $er->createQueryBuilder('entity');
            };
        }
        
        $options = array();
    
        $entityManager = $this->getEntityManager($emName);
        $mdata = $entityManager->getClassMetadata($entityClass);
        $repository = $entityManager->getRepository($entityClass);
    
        $qb = call_user_func($qbClosure, $repository);
        foreach ($qb->getQuery()->execute() as $row) {
            if (!is_object($row)) {
                throw new Exception\InvalidResult('row result must be an object');
            }
    
            $options[serialize($mdata->getIdentifierValues($row))] = (string) $row;
        }
        
        return $options;
    }

    /**
     * Prepares a cache instance.
     * 
     * @todo add additional parameters for configuring memcache.
     * @param string $cacheName
     */
    protected function _prepareCache($cacheName)
    {
        if (!isset($this->_options['cache'][$cacheName])) {
            throw new Exception\InvalidCache(
                "Cache with index '{$cacheName}' could not be located.");
        }

        $cacheOptions = $this->_options['cache'][$cacheName];
        $cache = new $cacheOptions['adapter']['class'];

        // put memcache options here

        $this->_caches[$cacheName] = $cache;
    }

    /**
     * Prepares a connecton instance.
     * 
     * @param string $conName
     */
    protected function _prepareConnection($conName)
    {
        if (!isset($this->_options['dbal']['connection'][$conName])) {
            throw new Exception\InvalidConnection(
                "Connection with index '{$conName}' could not be located.");
        }

        $conOptions = $this->_options['dbal']['connection'][$conName];
        $this->_connections[$conName] = DriverManager::getConnection(
            $conOptions,
            null,
            $this->getEventManager(
                $this->_options['dbal']['connection'][$conName]['eventManager']
            )
        );
    }
    
    /**
     * Prepares an eveent manager instance.
     * 
     * @param string $evName
     */
    protected function _prepareEventManager($evName)
    {
        if (!isset($this->_options['evm'][$evName])) {
            throw new Exception\InvalidEventManager(
                "EventManager with index '{$evName}' could not be located.");
        }
        
        $evmOptions = $this->_options['evm'][$evName];
        
        $evm = new EventManager();
        if (isset($evmOptions['subscribers']) && is_array($evmOptions['subscribers'])) {
            foreach($evmOptions['subscribers'] as $subscriber) {
                $instance = new $subscriber();
                $evm->addEventSubscriber($instance);
            }
        }
        
        $this->_eventManagers[$evName] = $evm;
    }

    /**
     * Prepares an entity manager instance.
     * 
     * @param string $emName
     */
    protected function _prepareEntityManager($emName)
    {
        if (!isset($this->_options['orm']['em'][$emName])) {
            throw new Exception\InvalidEntityManager(
                "EntityManager with index '{$emName}' could not be located.");
        }

        $emOptions = $this->_options['orm']['em'][$emName];
        $connection = isset($emOptions['connection']) ? $emOptions['connection']
            : self::getDefaultConnectionKey();

        $driverOptions = $emOptions['mdata']['driver'];
        $driverClass = $driverOptions['class'];
        $driver = null;

        $reflClass = new ReflectionClass($driverClass);

        // annotation driver has extra initialization options
        if ($reflClass->getName() == 'Doctrine\ORM\Mapping\Driver\AnnotationDriver'
            || $reflClass->isSubclassOf('Doctrine\ORM\Mapping\Driver\AnnotationDriver')) {
            if (!isset($driverOptions['reader']['class'])) {
                throw new Exception\InvalidMetadataDriver(
                    'AnnotationDriver was specified but no reader options exist');
            }

            $readerClass = $driverOptions['reader']['class'];
            $reader = new $readerClass();

            $driver = new $driverClass($reader, $driverOptions['paths']);
        } else {
            $driver = new $driverClass($driverOptions['paths']);
        }

        // register annotations
        if (isset($emOptions['mdata']['registry'])) {
            $regOptions = $emOptions['mdata']['registry'];

            // files
            if (isset($regOptions['files'])) {
                if (!is_array($regOptions['files'])) {
                    $regOptions['files'] = array(
                        $regOptions['files']
                    );
                }

                // sanity check
                if (!is_array($regOptions['files'])) {
                    throw new Exception\InvalidRegistryFile(
                        'Registry files must be an array of files');
                }

                foreach ($regOptions['files'] as $file) {
                    AnnotationRegistry::registerFile($file);
                }
            }

            // namespaces
            if (isset($regOptions['namespaces'])) {
                if (!is_array($regOptions['namespaces'])) {
                    $regOptions['namespaces'] = array(
                        $regOptions['namespaces']
                    );
                }

                if (!is_array($regOptions['namespaces'])) {
                    throw new Exception\InvalidRegistryNamespace(
                        'Registry namespaces must be an array of key => value pairs');
                }

                AnnotationRegistry::registerAutoloadNamespaces($regOptions['namespaces']);
            }
        }

        $config = new Configuration();
        $config->setProxyDir($emOptions['proxy']['dir']);
        $config->setProxyNamespace($emOptions['proxy']['namespace']);
        $config->setAutoGenerateProxyClasses($emOptions['proxy']['autoGenerate']);
        $config->setMetadataDriverImpl($driver);
        
        $config->setMetadataCacheImpl($this->getCache($emOptions['cache']['metadata']));
        $config->setQueryCacheImpl($this->getCache($emOptions['cache']['query']));
        $config->setResultCacheImpl($this->getCache($emOptions['cache']['result']));

        $em = EntityManager::create($this->getConnection($connection), $config);
        
        if (isset($emOptions['logger']['class'])) {
            $dbalConfig = $em->getConnection()->getConfiguration();
            
            $logger = new $emOptions['logger']['class']();
            $dbalConfig->setSqlLogger($logger);
        }

        $this->_entityManagers[$emName] = $em;
    }
}
