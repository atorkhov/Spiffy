<?php
namespace Spiffy\Account\Service;
use DateTime,
    Spiffy\Auth\Entity\AbstractUser,
    Spiffy\Auth\Service\Security,
    Spiffy\Doctrine\Container,
    Zend_Mail;

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
     * Activates a user account if the code is correct. Decodes the "code"
     * to get the username and verification code.
     * 
     * @param string $code
     * @param string $entityClass
     * @return boolean
     */
    public function activateUser($code, $entityClass)
    {
        $data = unserialize(base64_decode(urldecode($code)));
        if (!$data || !is_array($data)) {
            return false;
        }
        
        $repo = $this->_doctrine->getEntityManager()->getRepository($entityClass);
        $user = $repo->findOneBy(array('username' => $data['username']));
        if (!$user) {
            throw new Exception\UserNotFound('The requested user does not exist');
        }
        
        if ($user->getVerificationCode() == $data['code']) {
            $user->setIsActive(true);
            
            return $user->save();
        }
        
        return false;
    }
    
    /**
     * Generates an encoded uri parameter from a username and
     * verification code.
     * 
     * @param string $username
     * @param string $code
     * @return string
     */
    public function generateUriParameter($username, $code)
    {
        return urlencode(base64_encode(serialize(array(
        	'username' => $username,
        	'code' => $code
        ))));
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
     * use to verify their email address is accurate.
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