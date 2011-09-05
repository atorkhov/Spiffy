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
     * Activates a user account if the code is correct.
     * 
     * @param AbstractUser $email
     * @param string $code
     */
    public function activateUser(AbstractUser $user, $code)
    {
        if ($user->getVerificationCode() == $code) {
            $user->setIsActive(true);
            
            return $user->save();
        }
        
        return false;
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
        $user->setJoinDate(new DateTime('now'));
        $user->setVerificationCode($this->_generateVerificationCode());
        $user->updateLastLogin();

        return $user->save();
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