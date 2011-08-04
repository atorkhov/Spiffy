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

namespace Spiffy\Doctrine\Annotations\Validators;
use Doctrine\Common\Annotations\Annotation;

/** Zend Validator SuperClass */
class Validator extends Annotation
{
    public $breakChain = false;
}

/** @Annotation */
final class Alpha extends Validator
{
    public $class = 'Zend_Validate_Alpha';
}

/** @Annotation */
final class Barcode extends Validator
{
    public $class = 'Zend_Validate_Barcode';
}

/** @Annotation */
final class Between extends Validator
{
    public $class = 'Zend_Validate_Between';
}

/** @Annotation */
final class Callback extends Validator
{
    public $class = 'Zend_Validate_Callback';
}

/** @Annotation */
final class CreditCard extends Validator
{
    public $class = 'Zend_Validate_CreditCard';
}

/** @Annotation */
final class Ccnum extends Validator
{
    public $class = 'Zend_Validate_Ccnum';
}

/** @Annotation */
final class Date extends Validator
{
    public $class = 'Zend_Validate_Date';
}

/** @Annotation */
final class Digits extends Validator
{
    public $class = 'Zend_Validate_Digits';
}

/** @Annotation */
final class EmailAddress extends Validator
{
    public $class = 'Zend_Validate_EmailAddress';
}

/** @Annotation */
final class Float extends Validator
{
    public $class = 'Zend_Validate_Float';
}

/** @Annotation */
final class GreaterThan extends Validator
{
    public $class = 'Zend_Validate_GreaterThan';
}

/** @Annotation */
final class Hex extends Validator
{
    public $class = 'Zend_Validate_Hex';
}

/** @Annotation */
final class Hostname extends Validator
{
    public $class = 'Zend_Validate_Hostname';
}

/** @Annotation */
final class Iban extends Validator
{
    public $class = 'Zend_Validate_Iban';
}

/** @Annotation */
final class Identical extends Validator
{
    public $class = 'Zend_Validate_Identical';
}

/** @Annotation */
final class InArray extends Validator
{
    public $class = 'Zend_Validate_InArray';
}

/** @Annotation */
final class Int extends Validator
{
    public $class = 'Zend_Validate_Int';
}

/** @Annotation */
final class Isbn extends Validator
{
    public $class = 'Zend_Validate_Isbn';
}

/** @Annotation */
final class LessThan extends Validator
{
    public $class = 'Zend_Validate_LessThan';
}

/** @Annotation */
final class NotEmpty extends Validator
{
    public $class = 'Zend_Validate_NotEmpty';
}

/** @Annotation */
final class PostCode extends Validator
{
    public $class = 'Zend_Validate_PostCode';
}

/** @Annotation */
final class StringLength extends Validator
{
    public $class = 'Zend_Validate_StringLength';
}
