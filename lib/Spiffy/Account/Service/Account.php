<?php
namespace Spiffy\Account\Service;
use DateTime,
    Spiffy\Auth\Entity\AbstractUser,
    Spiffy\Auth\Service\Security,
    Spiffy\Doctrine\Container;

class Account
{
    /**
     * @var Spiffy\Doctrine\Container
     */
    protected $_doctrine;
    
    /**
     * @var Spiffy\Auth\Service\Security
     */
    protected $_security;
    
    /**
     * Constructor.
     * 
     * @param Doctrine $doctrine
     * @param Security $service
     */
    public function __construct(Container $doctrine, Security $security)
    {
        $this->_doctrine = $doctrine;
        $this->_security = $security;
    }
    
    /**
     * Creates a new user.
     * 
     * @param AbstractUser $user
     */
    public function createUser(AbstractUser $user)
    {
        $user->setPassword($this->_security->transformPassword($user->getPassword()));
        
        $user->setIsActive(false);
        $user->setLastLogin(new DateTime('now'));
        $user->setJoinDate(new DateTime('now'));
        $user->setVerificationCode($this->_generateVerificationCode());

        return $user->save(true);
    }
    
    /**
     * Generates a simple verification code that the user can
     * use to verify their email is accurate.
     * 
     * @return string
     */
    protected function _generateVerificationCode()
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $length = 6;
        $time = time();
        
        $code = '';
        for($i = $length; $i > 0; $i--) {
            $code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        
        return $code;
    }
}