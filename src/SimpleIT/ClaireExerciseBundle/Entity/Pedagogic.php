<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Pedagogic
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="SimpleIT\ClaireExerciseBundle\Entity\PedagogicRepository")
 */
class Pedagogic
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20)
     */
    private $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer")
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="longName", type="string", length=100)
     */
    private $longName;

    /**
     * @var string
     *
     * @ORM\Column(name="period", type="string", length=50)
     */
    private $period;

    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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
     * Set code
     *
     * @param string $code
     *
     * @return Pedagogic
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Pedagogic
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set longName
     *
     * @param string $longName
     *
     * @return Pedagogic
     */
    public function setLongName($longName)
    {
        $this->longName = $longName;

        return $this;
    }

    /**
     * Get longName
     *
     * @return string
     */
    public function getLongName()
    {
        return $this->longName;
    }
    
    /**
     * Get period.
     *
     * @return period.
     */
    public function getPeriod()
    {
        return $this->period;
    }
    
    /**
     * Set period.
     *
     * @param period the value to set.
     */
    public function setPeriod($period)
    {
        $this->period = $period;
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

    public function __toString()
    {
        return "Apogee: ". $this->getCode() . " en " . $this->getYear() .
            " nom long " . $this->getLongName() . " si period: " . $this->getPeriod();
    }
}


