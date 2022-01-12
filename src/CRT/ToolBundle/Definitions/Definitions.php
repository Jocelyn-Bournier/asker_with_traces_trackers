<?php

namespace CRT\ToolBundle\Definitions;

class Definitions
{
    //status request
    private $waiting = "En attente confirmation utilisateur";
    private $noAnswer = "La demande a expiré sans confirmation";
    private $confirmed = "Confirmation utilisateur reçue";
    private $expired = "L'utilisateur a confirmé la demande hors du délai autorisé";
    /**
    * Get waiting.
    *roro
    * @return waiting.
    */
    public function getWaiting()
    {
        return $this->waiting;
    }
    /**
    * Get noAnswer.
    *roro
    * @return noAnswer.
    */
    public function getNoAnswer()
    {
        return $this->noAnswer;
    }
    /**
    * Get confirmed.
    *roro
    * @return confirmed.
    */
    public function getConfirmed()
    {
        return $this->confirmed;
    }
    /**
    * Get expired.
    *roro
    * @return expired.
    */
    public function getExpired()
    {
        return $this->expired;
    }
}
