<?php

namespace Spiffy\Zend\Dojo;
use Doctrine\DBAL\Types\Type,
    Spiffy\Zend\Form as BaseForm,
    Zend_Dojo;

abstract class Form extends BaseForm
{
    /**
     * Constructor
     *
     * @param  string|object $entity
     * @param  array|Zend_Config|null $options
     * @return void
     */
    public function __construct($entity = null, $options = null)
    {
        $options['dojoEnabled'] = true;
        parent::__construct($entity, $options);
    }
}
