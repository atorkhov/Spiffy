<?php
namespace Spiffy\Auth\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="auth_resource")
 */
class Resource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
   /**
    * @ORM\Column(type="string",length="100")
    */
    protected $name;
    
   /**
    * @ORM\ManyToMany(targetEntity="Role", mappedBy="resources")
    */
    protected $roles;
}