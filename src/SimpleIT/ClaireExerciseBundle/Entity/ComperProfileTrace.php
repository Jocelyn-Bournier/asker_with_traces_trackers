<?php
/*
 * This file is part of CLAIRE.
 *
 * CLAIRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CLAIRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CLAIRE. If not, see <http://www.gnu.org/licenses/>
 */

namespace SimpleIT\ClaireExerciseBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;

/**
 * Comper Profile click trace
 * 
 * ANR COMPER
 * @author Rémi Casado <remi.casado@protonmail.com>
 */
class ComperProfileTrace
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var User
     */
    private $user;

    /**
     * @var int
     */
    private $contextDirectory;

    /**
     * @var string
     * 
     * Action of the user regarding his profile visualization.
     * So far, either "request" or "change to <OLM library view>".
     */
    private $action;

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param AskerUser $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Set contextDirectory
     *
     * @param int $contextDirectory
     */
    public function setContextDirectory($contextDirectory)
    {
        $this->contextDirectory = $contextDirectory;
    }

    /**
     * Set contextDirectory
     *
     * @param string $resourceLocation
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

}
