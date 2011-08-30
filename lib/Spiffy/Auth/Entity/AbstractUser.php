<?php
namespace Spiffy\Auth\Entity;
use Doctrine\ORM\Mapping as ORM,
    Spiffy\Doctrine\Annotations\Filters as Filter,
    Spiffy\Doctrine\Annotations\Validators as Assert,
    Spiffy\Doctrine\AbstractEntity;

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
    * @ORM\ManyToMany(targetEntity="Spiffy\Auth\Entity\Group")
    * @ORM\JoinTable
    * (
    *     name="auth_user_group",
    *     joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
    *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
    * )
    */
    protected $groups;
    
    /**
     * Set the username.
     * 
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    /**
     * Get the email.
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Set the email.
     * 
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
    
    /**
     * flag: is the user active?
     * 
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }
    
    /**
     * Set the active flag.
     * 
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->isActive = (bool) $active;
    }
}