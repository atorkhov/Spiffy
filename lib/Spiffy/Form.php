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
 * @package    Spiffy
 * @copyright  Copyright (c) 2011 Kyle Spraggs (http://www.spiffyjr.me)
 * @license    http://www.spiffyjr.me/license     New BSD License
 */

namespace Spiffy;
use Doctrine\DBAL\Types\Type;
use Spiffy\Dojo\Form as SpiffyDojoForm;
use Zend_Dojo;
use Zend_Form_Exception;
use Zend_Form;

abstract class Form extends Zend_Form
{
    /**
     * Doctrine 2 entity, if any.
     * @var object
     */
    protected $_entity = null;

    /**
     * flag: automatically persist valid entities?
     * @var boolean
     */
    protected $_automaticPersisting = false;

    /**
     * flag: has the form been submitted to validation?
     * @var boolean
     */
    protected $_validated = false;

    /**
     * Default elements for Zend_Form.
     * @var array
     */
    protected $_defaultElements = array(
    Type::SMALLINT => 'text',
    Type::BIGINT => 'text',
    Type::INTEGER => 'text',
    Type::BOOLEAN => 'checkbox',
    Type::DATE => 'text',
    Type::DATETIME => 'text',
    Type::DATETIMETZ => 'text',
    Type::DECIMAL => 'text',
    Type::OBJECT => null,
    Type::TARRAY => null,
    Type::STRING => 'text',
    Type::TEXT => 'textarea',
    Type::TIME => 'text'
    );

    /**
     * Constructor
     *
     * @param object $entity
     * @param array|Zend_Config|null $options
     * @return void
     */
    public function __construct($entity = null, $options = null)
    {
        // spiffy class prefixes
        $this->addPrefixPath('Spiffy_Form_Decorator', 'Spiffy/Form/Decorator', 'decorator')
        ->addPrefixPath('Spiffy_Form_Element', 'Spiffy/Form/Element', 'element')
        ->addElementPrefixPath('Spiffy_Form_Decorator', 'Spiffy/Form/Decorator', 'decorator')
        ->addDisplayGroupPrefixPath('Spiffy_Form_Decorator', 'Spiffy/Form/Decorator')
        ->setDefaultDisplayGroupClass('Spiffy_Form_DisplayGroup');

        // enable view helpers
        $this->getView()->addHelperPath('Spiffy/View/Helper', 'Spiffy_View_Helper');

        /*
         * Set the entity prior to loading options in case there's a default
        * entity set. This way, the entity called on construction overwrites
        * the entity set in getDefaultOptions().
        */
        if ($entity) {
            $this->setEntity($entity);
        }

        // assemble form
        $options = array_merge(is_array($options) ? $options : array(), $this->getDefaultOptions());
        parent::__construct($options);

        // set entity defaults if an entity is present
        $this->setDefaults($this->getEntity()->toArray());
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->getEntity() && $this->_automaticPersisting && $this->_validated) {
            $invalid = false;
            foreach ($this->getElements() as $element) {
                if ($invalid = $element->hasErrors()) {
                    break;
                }
            }

            $invalid = ($this->isErrors() || $invalid);
            if (!$invalid) {
                $this->getEntityManager()->persist($this->getEntity());
                $this->getEntityManager()->flush();
            }
        }
    }

    /**
     * Add a new element
     *
     * @param  string $name
     * @param  null|string|Zend_Form_Element $element
     * @param  array|Zend_Config $options
     * @throws Zend_Form_Exception on invalid element
     * @return Zend_Form
     */
    public function add($name, $element = null, $options = null)
    {
        if (!$this->getEntity()) {
            throw new Zend_Form_Exception('add() can only be used with a set entity');
        }

        // automatic type guessing if using Spiffy\Doctrine\Entity
        if ($this->getEntity() instanceof \Spiffy\Doctrine\Entity) {
            if ($this->getEntity()->classPropertyExists($name)) {
                $mdata = $this->getEntity()->getClassProperty($name);

                if (null === $element && isset($this->_defaultElements[$mdata['type']])) {
                    $element = $this->_defaultElements[$mdata['type']];
                }
            }
        }

        if (!isset($options['label'])) {
            $options['label'] = ucfirst($name);
        }

        if (!$element) {
            throw new Zend_Form_Exception(
                "element type was not specified for '{$name}' and could not be guessed");
        }

        parent::addElement($element, $name, $options);


        // automatic filters/validators from Spiffy\Doctrine\Entity
        if ($this->getEntity() instanceof \Spiffy\Doctrine\Entity) {

        }

        // add filters/validators from Spiffy\Model
    }

    /**
     * Sets automatic validators.
     *
     * @param boolean $value
     */
    public function setAutomaticPersisting($value)
    {
        $this->_automaticPersisting = $value;
    }

    /**
     * Getter for entity.
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Setter for entity.
     *
     * @param string|object $entity
     */
    public function setEntity($entity)
    {
        if (is_object($entity)) {
            ;
        } elseif (is_string($entity)) {
            $entity = new $entity();
        } else {
            throw new Zend_Form_Exception('Unknown input for setEntity()');
        }

        $this->_entity = $entity;
    }

    /**
     * Gets the default options for the form.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Form::isValid()
     */
    public function isValid($data)
    {
        $valid = parent::isValid($data);

        // populate entity
        $this->getEntity()->fromArray($this->getValues());

        // mark the form as validated for destructor persisting
        $this->_validated = true;

        return $valid;
    }
}
