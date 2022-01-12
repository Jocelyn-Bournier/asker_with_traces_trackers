<?php

namespace CRT\LdapBundle\Security\User;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\Mapping as ORM; 
 
/** 
 * ExerciseModel 
 * 
 * @ORM\Table()
 * @ORM\Entity()
 */


class LdapUser implements UserInterface
{
    private $username;
    private $password;
    private $salt;
    private $roles;
    private $uid;
    private $id;

    public function __construct($username, $password, $salt, array $roles, $uid,$id)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
        $this->uid = $uid;
        $this->id = $id;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }


    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }

    public function equals(UserInterface $user)
    {
        if (!$user instanceof LdapUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }
    /**
    * Get uid.
    *
    * @return uid.
    */
    public function getUid()
    {
       return $this->uid;
    }
    /**
    * Set uid.
    *
    * @param uid the value to set.
    */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }
    
    /**
     * Get id.
     *
     * @return id.
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set id.
     *
     * @param id the value to set.
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
