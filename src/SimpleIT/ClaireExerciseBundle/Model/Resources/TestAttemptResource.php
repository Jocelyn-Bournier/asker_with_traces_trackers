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
 * Class TestAttemptResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestAttemptResource
{
    /**
     * @const RESOURCE_NAME = 'Test Attempt'
     */
    const RESOURCE_NAME = 'Test Attempt';

    /**
     * @var integer $id Id of test attempt
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "test_attempt", "list"})
     */
    private $id;

    /**
     * @var \DateTime $createdAt
     * @Serializer\Type("DateTime")
     * @Serializer\Groups({"details", "test_attempt", "list"})
     */
    private $createdAt;

    /**
     * @var int $test
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "test_attempt", "list"})
     */
    private $test;

    /**
     * @var int $user
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "test_attempt", "list"})
     */
    private $user;

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
     * Set id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set test
     *
     * @param int $test
     */
    public function setTest($test)
    {
        $this->test = $test;
    }

    /**
     * Get test
     *
     * @return int
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * Set user
     *
     * @param int $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }
}
