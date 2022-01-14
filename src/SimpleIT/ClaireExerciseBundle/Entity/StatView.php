<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;

/**
 * StatView
 */
class StatView
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
    private $refPedagogic;

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    private $directory;



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
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return StatView
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return StatView
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
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
     * Get name.
     *
     * @return name.
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set name.
     *
     * @param name the value to set.
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Get refPedagogic.
     *
     * @return refPedagogic.
     */
    public function getRefPedagogic()
    {
        return $this->refPedagogic;
    }
    
    /**
     * Set refPedagogic.
     *
     * @param refPedagogic the value to set.
     */
    public function setRefPedagogic($refPedagogic)
    {
        $this->refPedagogic = $refPedagogic;
    }
}

