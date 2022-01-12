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

use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestModel;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestModelPosition;

/**
 * Class to manage the creation of TestModelPosition
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class TestModelPositionFactory
{
    /**
     * Create a new TestModelPosition
     *
     * @param ExerciseModel $em
     * @param TestModel     $testModel
     * @param int           $position
     *
     * @return TestModelPosition
     */
    public static function create(ExerciseModel $em, TestModel $testModel, $position)
    {
        $testModelPosition = new TestModelPosition();
        $testModelPosition->setPosition($position);
        $testModelPosition->setExerciseModel($em);
        $testModelPosition->setTestModel($testModel);

        return $testModelPosition;
    }
}
