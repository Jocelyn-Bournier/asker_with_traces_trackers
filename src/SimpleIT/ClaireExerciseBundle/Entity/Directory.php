<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;

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

    /**
     * @var string
     */
    private $frameworkId;

    /**
     * @var boolean
     */
    private $isVisible;

    private $users;

    private $models;

    private $visibleExercise;

    private $parent;

    private $statViews;

    private $subs;

    private $owner;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->subs = new ArrayCollection();
        $this->statViews = new ArrayCollection();
        $this->models =  new ArrayCollection();
        $this->constructVisibleExercise();
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
    public function addUser(AskerUserDirectory $user)
    {
      $this->users[] = $user;
      return $this;
    }

    public function removeUser(AskerUserDirectory $user)
    {
      $this->users->removeElement($user);
    }

    /**
     * Get statViews.
     *
     * @return statViews.
     */
    public function getStatViews()
    {
        return $this->statViews;
    }
    public function addStatView(StatView $statView)
    {
      $this->statViews[] = $statView;
      return $this;
    }

    public function removeStatView(StatView $statView)
    {
      $this->statViews->removeElement($statView);
    }

    public function getManagers()
    {
        $managers = [];
        foreach($this->getUsers() as $user){
            if ($user->getUser()->getId()  !== $this->getOwner()->getId()){
                foreach($user->getUser()->getRoles() as $role){
                    if ($role->getName() == "ROLE_WS_CREATOR"){
                        $managers[] = $user;
                        break;
                    }
                }
            }
        }
        return $managers;
    }
    public function getReaders()
    {
        $readers = [];
        foreach($this->getUsers() as $user){
            if ($user->getUser()->getId()  !== $this->getOwner()->getId()){
                foreach($user->getUser()->getRoles() as $role){
                    if ($role->getName() == "ROLE_WS_CREATOR"){
                        $readers[] = $user;
                        break;
                    }
                }
            }
        }
        return $readers;
    }

    public function realUsers()
    {
        $realUsers = [];
        foreach($this->getUsers() as $user){
            $realUsers[] = $user->getUser();
        }
        return $realUsers;

    }

    public function hasManager(AskerUser $has)
    {
        foreach($this->getUsers() as $user){
            if ($has->getId() == $user->getUser()->getId()
                && $user->getIsManager()
            )
            {
                return true;
            }
        }
        return false;
    }

    public function hasUser(AskerUser $has){
        foreach($this->getUsers() as $user){
            if ($has->getId() == $user->getUser()->getId()){
                return true;
            }
        }
        return false;
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

   /*
    *  getLongName
    *  if subDirectory return parent's name + his name
    *  @return string
    */
    public function getLongName()
    {
        if ($this->getParent()){
            return $this->getParent()->getName().": " . $this->getName();
        }else{
            return $this->getName();

        }

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

    /**
     * Get frameworkId.
     *
     * @return frameworkId.
     */
    public function getFrameworkId()
    {
        return $this->frameworkId;
    }

    /**
     * Set frameworkId.
     *
     * @param frameworkId the value to set.
     */
    public function setFrameworkId($frameworkId)
    {
        $this->frameworkId = $frameworkId;
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

    public function getLastView()
    {
        $last = null;
        if(count($this->getStatViews()) == 0){
            return null;
        }else{
            foreach($this->getStatViews() as $view){
                if (empty($last)){
                    $last = $view;
                }else if ($view->getEndDate() > $last->getEndDate()){
                    $last = $view;
                }
            }
            return $last;
        }
    }

    /**
     * Get isVisible.
     *
     * @return isVisible.
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }

    /**
     * Set isVisible.
     *
     * @param isVisible the value to set.
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;
    }

    public function getVisibleExercise()
    {
        return $this->visibleExercise;
    }

    /**
     * Set visible[$index].
     *
     * @param visible the value to set.
     */
    public function setVisibleExercise($visibleExercise)
    {
        $this->visibleExercise = $visibleExercise;
    }

    public function constructVisibleExercise()
    {
        $this->visibleExercise =  new ArrayCollection();
    }

    public function addVisibleExercise($visible)
    {
        $this->visibleExercise[] = $visible;
    }
}


