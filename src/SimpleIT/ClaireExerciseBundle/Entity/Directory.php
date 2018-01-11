<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;

/**
 * Directory
 */
class Directory
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
    private $code;

    private $users;

    private $models;

    private $parent;

    private $subs;
    
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->subs = new ArrayCollection();
        $this->models =  new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Directory
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
     * Get users.
     *
     * @return users.
     */
    public function getUsers()
    {
        return $this->users;
    }
    public function addUser(AskerUser $user)
    {
      $this->users[] = $user;
      return $this;
    }

    public function removeUser(AskerUser $user)
    {
      $this->users->removeElement($user);
    }
    
    /**
     * Get models.
     *
     * @return models.
     */
    public function getModels()
    {
        return $this->models;
    }

    public function addModel(ExerciseModel $model)
    {
      $this->models[] = $model;
      return $this;
    }

    public function removeModel(ExerciseModel $model)
    {
      $this->models->removeElement($model);
    }
    
    public function __toString()
    {
        return $this->getName();
    }
    
    /**
     * Get parent.
     *
     * @return parent.
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * Set parent.
     *
     * @param parent the value to set.
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    /**
     * Get subs.
     *
     * @return subs.
     */
    public function getSubs()
    {
        return $this->subs;
    }
    public function addSub(Directory $sub)
    {
      $this->subs[] = $sub;
      return $this;
    }

    public function removeSub(Directory $sub)
    {
      $this->subs->removeElement($sub);
    }
    
    /**
     * Get code.
     *
     * @return code.
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * Set code.
     *
     * @param code the value to set.
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}

