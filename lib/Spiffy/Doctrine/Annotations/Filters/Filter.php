<?php
namespace Spiffy\Doctrine\Annotations\Filters;
use Doctrine\Common\Annotations\Annotation;

/** Zend Validator SuperClass */
class Filter extends Annotation
{
}

/** @Annotation */
final class Alnum extends Filter
{
    public $class = 'Zend_Filter_Alnum';
}

/** @Annotation */
final class Alpha extends Filter
{
    public $class = 'Zend_Filter_Alpha';
}

/** @Annotation */
final class BaseName extends Filter
{
    public $class = 'Zend_Filter_BaseName';
}

/** @Annotation */
final class Boolean extends Filter
{
    public $class = 'Zend_Filter_Boolean';
}

/** @Annotation */
final class Callback extends Filter
{
    public $class = 'Zend_Filter_Callback';
}

/** @Annotation */
final class Compress extends Filter
{
    public $class = 'Zend_Filter_Compress';
}

/** @Annotation */
final class Decompress extends Filter
{
    public $class = 'Zend_Filter_Decompress';
}

/** @Annotation */
final class Digits extends Filter
{
    public $class = 'Zend_Filter_Digits';
}

/** @Annotation */
final class Dir extends Filter
{
    public $class = 'Zend_Filter_Dir';
}

/** @Annotation */
final class Encrypt extends Filter
{
    public $class = 'Zend_Filter_Encrypt';
}

/** @Annotation */
final class Decrypt extends Filter
{
    public $class = 'Zend_Filter_Decrypt';
}

/** @Annotation */
final class HtmlEntities extends Filter
{
    public $class = 'Zend_Filter_HtmlEntities';
}

/** @Annotation */
final class Int extends Filter
{
    public $class = 'Zend_Filter_Int';
}

/** @Annotation */
final class LocalizedToNormalized extends Filter
{
    public $class = 'Zend_Filter_LocalizedToNormalized';
}

/** @Annotation */
final class NormalizedToLocalized extends Filter
{
    public $class = 'Zend_Filter_NormalizedToLocalized';
}

/** @Annotation */
final class Null extends Filter
{
    public $class = 'Zend_Filter_Null';
}

/** @Annotation */
final class PregReplace extends Filter
{
    public $class = 'Zend_Filter_PregReplace';
}

/** @Annotation */
final class RealPath extends Filter
{
    public $class = 'Zend_Filter_RealPath';
}

/** @Annotation */
final class StringToLower extends Filter
{
    public $class = 'Zend_Filter_StringToLower';
}

/** @Annotation */
final class StringToUpper extends Filter
{
    public $class = 'Zend_Filter_StringToUpper';
}

/** @Annotation */
final class StringTrim extends Filter
{
    public $class = 'Zend_Filter_StringTrim';
}

/** @Annotation */
final class StripNewLines extends Filter
{
    public $class = 'Zend_Filter_StripNewLines';
}

/** @Annotation */
final class StripTags extends Filter
{
    public $class = 'Zend_Filter_StripTags';
}
