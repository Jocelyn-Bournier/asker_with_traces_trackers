<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;
/**
 * Log
 */

class Log
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $loggedAt;

    /**
     * @var AskerUser
     */
    private $user;

    public function __construct(AskerUser $user)
    {
        $this->loggedAt = new \DateTime();
        $this->user = $user;
    }
    /**
     * Set user
     *
     * @param SimpleIT\ClaireExerciseBundle\Entity\AskerUser $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return SimpleIT\ClaireExerciseBundle\Entity\AskerUser
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Get loggedAt.
     *
     * @return loggedAt.
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }
    
    /**
     * Set loggedAt.
     *
     * @param loggedAt the value to set.
     */
    public function setLoggedAt($loggedAt)
    {
        $this->loggedAt = $loggedAt;
    }
}
