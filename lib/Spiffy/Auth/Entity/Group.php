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
	 * @ORM\ManyToMany(targetEntity="Spiffy\Auth\Entity\Permission")
	 * @ORM\JoinTable(name="auth_group_permission",
	 *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")}
	 * )
	 */
	protected $permissions;
}