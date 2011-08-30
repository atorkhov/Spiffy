<?php
namespace Spiffy\Auth\Entity\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="auth_group_permission")
 */
class Permission
{
   /**
    * @ORM\Id
    * @ORM\ManyToOne(targetEntity="Spiffy\Auth\Entity\Permission")
    * @ORM\JoinColumn(name="permission_id")
    */
    protected $permission;
    
   /**
    * @ORM\Id
    * @ORM\ManyToOne(targetEntity="Spiffy\Auth\Entity\Group")
    * @ORM\JoinColumn(name="group_id")
    */
    protected $group;
    
    /**
     * @ORM\Column(type="array")
     */
    protected $privileges;
}