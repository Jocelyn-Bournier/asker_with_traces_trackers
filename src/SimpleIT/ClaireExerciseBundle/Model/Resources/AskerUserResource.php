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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class AskerUserDirectoryResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AskerUserResource
{
    /**
     * @const RESOURCE_NAME = 'AskerUser'
     */
    const RESOURCE_NAME = 'AskerUser';

    /**
     * @var int $id Id of exercise
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "askerUserDirectory", "list"})
     */
    private $id;

    /**
     * @var string $username
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "askerUserDirectory"})
     */
    private $username;
    
    /**
     * @var string $isManager
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "directory"})
     */
    private $isManager;
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
    
    /**
     * Get username.
     *
     * @return username.
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Set username.
     *
     * @param username the value to set.
     */
    public function setUsername($username)
    {
        $this->username = $username;
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
}
