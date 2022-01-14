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
 * Class TestResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestResource
{
    /**
     * @const RESOURCE_NAME = 'Test'
     */
    const RESOURCE_NAME = 'Test';

    /**
     * @var integer $id Id of test
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "test", "list"})
     */
    private $id;

    /**
     * @var TestModelResource $testModel
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\TestModelResource")
     * @Serializer\Groups({"details", "test"})
     */
    private $testModel;

    /**
     * @var array $exercises
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "test"})
     */
    private $exercises;

    /**
     * Set exercises
     *
     * @param array $exercises
     */
    public function setExercises($exercises)
    {
        $this->exercises = $exercises;
    }

    /**
     * Get exercises
     *
     * @return array
     */
    public function getExercises()
    {
        return $this->exercises;
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
     * Set testModel
     *
     * @param TestModelResource $testModel
     */
    public function setTestModel($testModel)
    {
        $this->testModel = $testModel;
    }

    /**
     * Get testModel
     *
     * @return TestModelResource
     */
    public function getTestModel()
    {
        return $this->testModel;
    }
}
