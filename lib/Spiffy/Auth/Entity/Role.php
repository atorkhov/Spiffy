<?php
namespace Spiffy\Auth\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="auth_role")
 */
class Role
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
	 * @ORM\ManyToMany(targetEntity="Spiffy\Auth\Entity\Resource")
	 * @ORM\JoinTable(name="auth_role_resource",
	 *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")}
	 * )
	 */
	protected $resources;
}