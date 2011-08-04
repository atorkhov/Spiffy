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
* @package    Spiffy_Form
* @copyright  Copyright (c) 2011 Kyle Spraggs (http://www.spiffyjr.me)
* @license    http://www.spiffyjr.me/license     New BSD License
*/

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Spiffy\Form;

class Spiffy_Form_Element_Entity extends Zend_Form_Element_Select
{
    /**
     * Entity class.
     * @var string
     */
    protected $_class;

    /**
     * Spiffy container.
     * @var Spiffy\Container
     */
    protected $_spiffyContainer;

    /**
     * Query builder.
     * @var Doctrine\ORM\QueryBuilder
     */
    protected $_queryBuilder;

    /**
     * (non-PHPdoc)
     * @see Zend_Form_Element::init()
     */
    public function init()
    {
        if (!Zend_Registry::isRegistered('Spiffy_Container')) {
            throw new Zend_Form_Exception('Spiffy\Container is required when using Spiffy\Form');
        }

        if (!$this->_class) {
            throw new Zend_Form_Element_Exception(get_class($this) . ' requires a class');
        }

        if (!$this->_queryBuilder instanceof Closure) {
            $this->_queryBuilder = function (EntityRepository $er)
            {
                return $er->createQueryBuilder('entity');
            };
        }

        $this->_spiffyContainer = Zend_Registry::get('Spiffy_Container');
        $this->options = $this->_spiffyContainer
            ->getMultiOptions($this->_class, $this->_queryBuilder);
    }

    /**
     * Get entity class.
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * Set entity class.
     * 
     * @param string $class
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }

    /**
     * Get query builder.
     * 
     * @return Closure
     */
    public function getQueryBuilder()
    {
        return $this->_queryBuilder;
    }

    /**
     * Set query builder.
     * 
     * @param Closure $qb
     */
    public function setQueryBuilder(Closure $qb)
    {
        $this->_queryBuilder = $qb;
    }
}
