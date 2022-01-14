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

namespace SimpleIT\ClaireExerciseBundle\Entity\Test;

use Doctrine\Common\Collections\Collection;

/**
 * Class Test for the test sessions containing stored exercises.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Test
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Collection
     */
    private $testPositions;

    /**
     * @var Collection
     */
    private $testAttempts;

    /**
     * @var TestModel
     */
    private $testModel;

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
     * @param TestModel $testModel
     */
    public function setTestModel($testModel)
    {
        $this->testModel = $testModel;
    }

    /**
     * Get testModel
     *
     * @return TestModel
     */
    public function getTestModel()
    {
        return $this->testModel;
    }

    /**
     * Set testPositions
     *
     * @param Collection $testPositions
     */
    public function setTestPositions($testPositions)
    {
        $this->testPositions = $testPositions;
    }

    /**
     * Get testPositions
     *
     * @return Collection
     */
    public function getTestPositions()
    {
        return $this->testPositions;
    }

    /**
     * Set testAttempts
     *
     * @param Collection $testAttempts
     */
    public function setTestAttempts($testAttempts)
    {
        $this->testAttempts = $testAttempts;
    }

    /**
     * Get testAttempts
     *
     * @return Collection
     */
    public function getTestAttempts()
    {
        return $this->testAttempts;
    }
}
