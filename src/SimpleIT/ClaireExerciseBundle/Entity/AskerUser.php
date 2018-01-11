<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * AskerUser
 */
class AskerUser implements UserInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $firstName;
    /**
     * @var string
     */
    private $lastName;
    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $salt;

    /**
     * @var integer
     */
    private $ldapEmployeeId;

    /**
     * @var boolean
     */
    private $isLdap;
    /**
     * @var boolean
     */
    private $isEnable;

    /**
     * @var string
     */
    private $ldapDn;

    private $workspaces;
    private $directories;

    private $roles;
    public function __construct()
    {
        $this->directories = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

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
     * Set username
     *
     * @param string $username
     *
     * @return AskerUser
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return AskerUser
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return AskerUser
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set ldapEmployeeId
     *
     * @param integer $ldapEmployeeId
     *
     * @return AskerUser
     */
    public function setLdapEmployeeId($ldapEmployeeId)
    {
        $this->ldapEmployeeId = $ldapEmployeeId;

        return $this;
    }

    /**
     * Get ldapEmployeeId
     *
     * @return integer
     */
    public function getLdapEmployeeId()
    {
        return $this->ldapEmployeeId;
    }

    /**
     * Set isLdap
     *
     * @param boolean $isLdap
     *
     * @return AskerUser
     */
    public function setIsLdap($isLdap)
    {
        $this->isLdap = $isLdap;

        return $this;
    }

    /**
     * Get isLdap
     *
     * @return boolean
     */
    public function getIsLdap()
    {
        return $this->isLdap;
    }

    /**
     * Set ldapDn
     *
     * @param string $ldapDn
     *
     * @return AskerUser
     */
    public function setLdapDn($ldapDn)
    {
        $this->ldapDn = $ldapDn;

        return $this;
    }

    /**
     * Get ldapDn
     *
     * @return string
     */
    public function getLdapDn()
    {
        return $this->ldapDn;
    }
    public function eraseCredentials()
    {
    }


    //public function equals(UserInterface $user)                                    
    //{       
    //    if (!$user instanceof LdapUser) {                                          
    //        return false;                                                          
    //    }                                                                          
    //
    //    if ($this->password !== $user->getPassword()) {                            
    //        return false;
    //    }
    //        
    //    if ($this->getSalt() !== $user->getSalt()) {                               
    //        return false;                                                          
    //    }
    //        
    //    if ($this->username !== $user->getUsername()) {                            
    //        return false; 
    //    }   
    //        
    //    return true;
    //} 
    
    /**
     * Get roles.
     *
     * @return roles.
     */
    public function getRoles()
    {
        //$roles = array();
        //foreach($this->roles as $role){
        //    $roles[] = $role->getName();
        //}
        return $this->roles;
    }
    public function addRole(Role $role)

    {
        $this->roles[] = $role;
        return $this;
    }

    public function removeRole( Role $role)
    {
        $this->role->removeElement($role);
    }
    
    /**
     * Set roles.
     *
     * @param roles the value to set.
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }
    
    /**
     * Get lastName.
     *
     * @return lastName.
     */
    public function getLastName()
    {
        return $this->lastName;
    }
    
    /**
     * Set lastName.
     *
     * @param lastName the value to set.
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
    
    /**
     * Get firstName.
     *
     * @return firstName.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }
    
    /**
     * Set firstName.
     *
     * @param firstName the value to set.
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }
    
    /**
     * Get workspaces.
     *
     * @return workspaces.
     */
    public function getWorkspaces()
    {
        return $this->workspaces;
    }

    public function getPersonalWorkspace()
    {
        return $this->workspaces;
    }
    
    /**
     * Set workspaces.
     *
     * @param workspaces the value to set.
     */
    public function setWorkspaces($workspaces)
    {
        $this->workspaces = $workspaces;
    }
    public function addDirectory(Directory $directory)

    {
        $directory->addUser($this);
        $this->directories[] = $directory;
        return $this;
    }

    public function removeDirectory( Directory $directory)
    {
        $directory->removeUser($this);
        $this->directories->removeElement($directory);
    }
    
    /**
     * Get directories.
     *
     * @return directories.
     */
    public function getDirectories()
    {
        return $this->directories;
    }
    
    /**
     * Get isEnable.
     *
     * @return isEnable.
     */
    public function getIsEnable()
    {
        return $this->isEnable;
    }
    
    /**
     * Set isEnable.
     *
     * @param isEnable the value to set.
     */
    public function setIsEnable($isEnable)
    {
        $this->isEnable = $isEnable;
    }
}

