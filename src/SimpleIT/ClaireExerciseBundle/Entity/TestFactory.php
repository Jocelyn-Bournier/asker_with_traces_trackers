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

use Doctrine\Common\Collections\ArrayCollection;
use SimpleIT\ClaireExerciseBundle\Entity\Test\Test;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestModel;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestPosition;

/**
 * Class to manage the creation of Test
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class TestFactory
{
    /**
     * Create a new Test
     *
     * @param array     $positions
     * @param TestModel $testModel
     *
     * @return Test
     */
    public static function create(array $positions, TestModel $testModel)
    {
        $test = new Test();

        foreach ($positions as &$pos) {
            /** @var TestPosition $pos */
            $pos->setTest($test);
        }

        $test->setTestPositions(new ArrayCollection($positions));
        $test->setTestModel($testModel);

        return $test;
    }
}
