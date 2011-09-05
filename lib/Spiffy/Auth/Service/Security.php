<?php
namespace Spiffy\Auth\Service;
use Spiffy\Doctrine\Container as DoctrineContainer,
    Zend_Auth,
    Zend_Auth_Adapter_Interface,
    Zend_Auth_Result;

class Security implements Zend_Auth_Adapter_Interface
{
    /**
     * @var \Zend_Auth
     */
    protected $_auth;

    /**
     * @var Spiffy\Doctrine\Container
     */
    protected $_doctrine;

    /**
     * @var string
     */
    protected $_entityClass;
    
    /**
     * @var string
     */
    protected $_password;
    
    /**
     * @var string
     */
    protected $_username;

    /**
     * Constructor.
     *
     * @param DoctrineContainer $doctrine
     */
    public function __construct(DoctrineContainer $doctrine)
    {
        $this->_doctrine = $doctrine;
    }

    /**
     * Get the auth instance.
     *
     * @return Zend_Auth
     */
    public function getAuth()
    {
        if (null === $this->_auth) {
            $this->_auth = Zend_Auth::getInstance();
        }
        return $this->_auth;
    }

    /**
     * Get the authenticated user.
     *
     * @return Ambigous <mixed, NULL>
     */
    public function getUser()
    {
        return $this->getAuth()->getIdentity();
    }

    /**
     * Is the user authenticated?
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->_auth->hasIdentity();
    }

    /**
     * Finds a user.
     */
    public function findUser()
    {
        $em = $this->_doctrine->getEntityManager();
        $repo = $em->getRepository($this->_entityClass);
        
        return $repo->findOneBy(array('username' => $this->_username));
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Auth_Adapter_Interface::authenticate()
     */
    public function authenticate()
    {
        $user = $this->findUser();
        
        if ($user) {
            $password = explode('$', $user->getPassword());
            if ($this->transformPassword($this->_password, $password[1]) == $user->getPassword()) {
                if ($user->isActive()) {
                    return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $user);
                } else {
                    return new Zend_Auth_Result(
                        Zend_Auth_Result::FAILURE_UNCATEGORIZED,
                        $user,
                        array('Your account is currently inactive. This could be because an 
                        administrator disabled it or you have not verified your account email.')
                    );
                }
            } else {
                return new Zend_Auth_Result(
                    Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                    null,
                    array('The username or password you supplied was incorrect.')
                );
            }
        }
        
        return new Zend_Auth_Result(
            Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
            null,
            array('The username or password you supplied was incorrect.')
        );
    }

    /**
     * Login a user.
     *
     * @param string $username
     * @param string $password
     * @param string $entityClass
     * @return Zend_Auth_Result
     */
    public function login($username, $password, $entityClass)
    {
        $this->_entityClass = $entityClass;
        $this->_username = $username;
        $this->_password = $password;
        
        $result = $this->getAuth()->authenticate($this);

        if ($result->isValid()) {
            $this->getUser()->updateLastLogin();
        }

        return $result;
    }
    
    /**
     * Logs a user out.
     */
    public function logout()
    {
        $this->getAuth()->clearIdentity();
    }
    
    /**
     * Transforms a password with specified algorithm and returns
     * a string for storage.
     *
     * Passwords follow the Django format: algorithm$salt$password
     *
     * @param string $password
     * @param null|string $salt
     * @param string $algorithm
     * @return string
     */
    public function transformPassword($password, $salt = null, $algorithm = 'sha1')
    {
        if (null === $salt) {
            $salt = substr(sha1(microtime(true)), 0, 10);
        }

        $saltCount = strlen($salt);
        $count = strlen($password) - 1;
        $new = '';
        for($i = $count; $i >= 0; $i--) {
            $saltChar = $salt[($saltCount-1) - ($i % $saltCount)];
            $new .= $password[$count - $i] . $saltChar;
        }

        $new = sha1($new);
        return "{$algorithm}\${$salt}\${$new}";
    }
}