<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;

/**
 * Workspace
 */
class Workspace
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isPersonnal;


    /**
    * @ManyToMany(targetEntity="SimpleIT\ClaireExerciseBundle\Entity\AskerUser",mappedBy="workspaces")
    */
    //private $users;
    private $owner;



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
     * Set isPersonnal
     *
     * @param boolean $isPersonnal
     *
     * @return Workspace
     */
    public function setIsPersonnal($isPersonnal)
    {
        $this->isPersonnal = $isPersonnal;

        return $this;
    }

    /**
     * Get isPersonnal
     *
     * @return boolean
     */
    public function getIsPersonnal()
    {
        return $this->isPersonnal;
    }
    
    /**
     * Get users.
     *
     * @return users.
     */
    public function getUsers()
    {
        return $this->users;
    }
    
    /**
     * Set users.
     *
     * @param users the value to set.
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }
    
    /**
     * Get owner.
     *
     * @return owner.
     */
    public function getOwner()
    {
        return $this->owner;
    }
    
    /**
     * Set owner.
     *
     * @param owner the value to set.
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }
}

