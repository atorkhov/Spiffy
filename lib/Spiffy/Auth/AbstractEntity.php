<?php
namespace Spiffy\Auth;
use Spiffy\Doctrine\AbstractEntity as BaseAbstractEntity,
    Zend_Acl_Resource_Interface;

abstract class AbstractEntity extends BaseAbstractEntity implements Zend_Acl_Resource_Interface
{
    
}