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

namespace Spiffy\Doctrine\Annotations\Filters;
use Doctrine\Common\Annotations\Annotation;

/** Zend Validator SuperClass */
class Zend extends Annotation
{
}

/** @Annotation */
final class Alnum extends Zend
{
    public $class = 'Zend_Filter_Alnum';
}

/** @Annotation */
final class Alpha extends Zend
{
    public $class = 'Zend_Filter_Alpha';
}

/** @Annotation */
final class BaseName extends Zend
{
    public $class = 'Zend_Filter_BaseName';
}

/** @Annotation */
final class Boolean extends Zend
{
    public $class = 'Zend_Filter_Boolean';
}

/** @Annotation */
final class Callback extends Zend
{
    public $class = 'Zend_Filter_Callback';
}

/** @Annotation */
final class Compress extends Zend
{
    public $class = 'Zend_Filter_Compress';
}

/** @Annotation */
final class Decompress extends Zend
{
    public $class = 'Zend_Filter_Decompress';
}

/** @Annotation */
final class Digits extends Zend
{
    public $class = 'Zend_Filter_Digits';
}

/** @Annotation */
final class Dir extends Zend
{
    public $class = 'Zend_Filter_Dir';
}

/** @Annotation */
final class Encrypt extends Zend
{
    public $class = 'Zend_Filter_Encrypt';
}

/** @Annotation */
final class Decrypt extends Zend
{
    public $class = 'Zend_Filter_Decrypt';
}

/** @Annotation */
final class HtmlEntities extends Zend
{
    public $class = 'Zend_Filter_HtmlEntities';
}

/** @Annotation */
final class Int extends Zend
{
    public $class = 'Zend_Filter_Int';
}

/** @Annotation */
final class LocalizedToNormalized extends Zend
{
    public $class = 'Zend_Filter_LocalizedToNormalized';
}

/** @Annotation */
final class NormalizedToLocalized extends Zend
{
    public $class = 'Zend_Filter_NormalizedToLocalized';
}

/** @Annotation */
final class Null extends Zend
{
    public $class = 'Zend_Filter_Null';
}

/** @Annotation */
final class PregReplace extends Zend
{
    public $class = 'Zend_Filter_PregReplace';
}

/** @Annotation */
final class RealPath extends Zend
{
    public $class = 'Zend_Filter_RealPath';
}

/** @Annotation */
final class StringToLower extends Zend
{
    public $class = 'Zend_Filter_StringToLower';
}

/** @Annotation */
final class StringToUpper extends Zend
{
    public $class = 'Zend_Filter_StringToUpper';
}

/** @Annotation */
final class StringTrim extends Zend
{
    public $class = 'Zend_Filter_StringTrim';
}

/** @Annotation */
final class StripNewLines extends Zend
{
    public $class = 'Zend_Filter_StripNewLines';
}

/** @Annotation */
final class StripTags extends Zend
{
    public $class = 'Zend_Filter_StripTags';
}
