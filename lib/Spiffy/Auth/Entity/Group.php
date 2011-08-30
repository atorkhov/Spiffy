<?php
namespace Spiffy\Auth\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="auth_group")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string",length="25")
     */
    protected $name;

   /**
    * @ORM\ManyToMany(targetEntity="Permission", mappedBy="groups")
    */
    protected $permissions;
}