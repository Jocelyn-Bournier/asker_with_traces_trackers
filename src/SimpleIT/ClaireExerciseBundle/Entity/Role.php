<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Role
 */
class Role implements RoleInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $public;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get public.
     *
     * @return public.
     */
    public function getPublic()
    {
        return $this->public;
    }
    
    /**
     * Set public.
     *
     * @param public the value to set.
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    public function getRole()
    {
        return $this->name;
    }
}

