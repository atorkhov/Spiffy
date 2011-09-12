<?php
namespace Spiffy\Acl\Entity;
use DateTime,
    Doctrine\ORM\Mapping as ORM,
    Spiffy\Acl\AbstractEntity,
    Spiffy\Doctrine\Annotations\Filters as Filter,
    Spiffy\Doctrine\Annotations\Validators as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractUser extends AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string",length="30")
     */
    protected $username;

   /**
    * @Assert\EmailAddress
    * @ORM\Column(type="string",length="75")
    */
    protected $email;
    
   /**
    * @ORM\Column(type="string",length="128")
    */
    protected $password;
    
    /**
     * @ORM\Column(type="string",length="128",name="verification_code")
     */
    protected $verificationCode;
    
    /**
     * @ORM\Column(type="boolean",name="is_active")
     */
    protected $isActive;

    /**
     * @ORM\Column(type="datetime",name="last_login")
     */
    protected $lastLogin;

    /**
     * @ORM\Column(type="datetime",name="join_date")
     */
    protected $joinDate;
    
   /**
    * @ORM\ManyToMany(targetEntity="Spiffy\Auth\Entity\Role")
    * @ORM\JoinTable
    * (
    *     name="auth_user_role",
    *     joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
    *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
    * )
    */
    protected $roles;

    /**
     * (non-PHPdoc)
     * @see Zend_Acl_Role_Interface::getRoleId()
     */
    public function getRoleId()
    {
        return $this->id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getVerificationCode()
    {
        return $this->verificationCode;
    }

    public function setVerificationCode($verificationCode)
    {
        $this->verificationCode = $verificationCode;
    }

    public function isActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    public function updateLastLogin()
    {
        $this->lastLogin = new DateTime('now');
    }

    public function getJoinDate()
    {
        return $this->joinDate;
    }

    public function setJoinDate($joinDate)
    {
        $this->joinDate = $joinDate;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }
}