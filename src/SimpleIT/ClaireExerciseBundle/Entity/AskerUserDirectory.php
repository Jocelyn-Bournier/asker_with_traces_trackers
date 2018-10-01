<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AskerUserDirectory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="SimpleIT\ClaireExerciseBundle\Entity\AskerUserDirectoryRepository")
 */
class AskerUserDirectory
{

    public function __construct()
    {
        $this->startDate = new \DateTime();
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;



    /**
     * @var \DateTime 
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime 
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isManager", type="boolean")
     */
    private $isManager;

    /**
     *
     *
     *  @ORM\JoinColumn(nullable=false)
     */
    private $directory;

    /**
     *
     *
     *  @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
     * Get directory.
     *
     * @return directory.
     */
    public function getDirectory()
    {
        return $this->directory;
    }
    
    /**
     * Set directory.
     *
     * @param directory the value to set.
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }
    
    /**
     * Get user.
     *
     * @return user.
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set user.
     *
     * @param user the value to set.
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    #public function isOnlyStudent()
    #{

    #    foreach($this->getUser()->getRoles() as $role){
    #        $name = $role->getName();
    #        if (!preg_match( "/.*ROLE_USER.*/",$name)){
    #            return false;
    #        }
    #    }
    #    return true;
    #}

    #public function getUserId()
    #{
    #    return $this->getUser()->getId();
    #}

    public function __toString()
    {
        return "objet AskerUserDirectory:".$this->getDirectory()->getName();
    }
    /**
     * Get isManager.
     *
     * @return isManager.
     */
    public function getIsManager()
    {
        return $this->isManager;
    }
    
    /**
     * Set isManager.
     *
     * @param isManager the value to set.
     */
    public function setIsManager($isManager)
    {
        $this->isManager = $isManager;
    }
    /**
     * Get startDate.
     *
     * @return startDate.
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    
    /**
     * Set startDate.
     *
     * @param startDate the value to set.
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }
    
    /**
     * Get endDate.
     *
     * @return endDate.
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
    
    /**
     * Set endDate.
     *
     * @param endDate the value to set.
     */
    public function setEndDate($endDate = null)
    {
        $this->endDate = $endDate;
    }
}
