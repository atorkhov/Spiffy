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
use Zend_Validate;
use Zend_Validate_Interface;

class Validate extends Zend_Validate 
{
    const CHAIN_APPEND  = 'append';
    const CHAIN_PREPEND = 'prepend';
    
    /**
    * Adds a validator to the  chain
    *
    * If $breakChainOnFailure is true, then if the validator fails, the next validator in the chain,
    * if one exists, will not be executed.
    *
    * @param  Zend_Validate_Interface $validator
    * @param  boolean                 $breakChainOnFailure
    * @param  string				  $placement
    * @return Zend_Validate Provides a fluent interface
    */
    public function addValidator(Zend_Validate_Interface $validator, $breakChainOnFailure = false,
        $placement = self::CHAIN_APPEND
    )
    {
        $validator = array(
            'instance' => $validator,
            'breakChainOnFailure' => (boolean) $breakChainOnFailure
        );
        if ($placement == self::CHAIN_PREPEND) {
            array_unshift($this->_validators, $validator);
        } else {
            $this->_validators[] = $validator;
        }
        return $this;
    }
    
    /**
    * Add a validator to the end of the chain
    *
    * @param  Zend_Validate_Interface $validator
    * @param  boolean                 $breakChainOnFailure
    * @return Zend_Validate Provides a fluent interface
    */
    public function appendValidator(Zend_Validate_Interface $validator, $breakChainOnFailure = false)
    {
        return $this->addValidator($validator, $breakChainOnFailure, self::CHAIN_APPEND);
    }
    
    /**
    * Add a validator to the start of the chain
    *
    * @param  Zend_Validate_Interface $validator
    * @param  boolean                 $breakChainOnFailure
    * @return Zend_Validate Provides a fluent interface
    */
    public function prependValidator(Zend_Validate_Interface $validator, $breakChainOnFailure = false)
    {
        return $this->addValidator($validator, $breakChainOnFailure, self::CHAIN_PREPEND);
    }
    
    /**
     * Get all the validators
     * 
     * @return array
     */
    public function getValidators()
    {
        return $this->_validators;
    }
}