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

use SimpleIT\ClaireExerciseBundle\Entity\Test\Test;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestPosition;

/**
 * Class TestResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class TestResourceFactory
{

    /**
     * Create a TestResource collection
     *
     * @param array $tests
     *
     * @return array
     */
    public static function createCollection(array $tests)
    {
        $testResources = array();
        foreach ($tests as $test) {
            $testResources[] = self::create($test);
        }

        return $testResources;
    }

    /**
     * Create a TestResource
     *
     * @param Test $test
     *
     * @return TestResource
     */
    public static function create(Test $test)
    {
        $testResource = new TestResource();
        $testResource->setId($test->getId());
        $testResource->setTestModel(
            TestModelResourceFactory::create($test->getTestModel())
        );

        $exercises = array();
        foreach ($test->getTestPositions() as $position) {
            /** @var TestPosition $position */
            $exercises[$position->getPosition()] = $position->getExercise()->getId();
        }

        $ex = array();
        for ($i = 0; $i < count($exercises); $i++) {
            $ex[] = $exercises[$i];
        }
        $testResource->setExercises($ex);

        return $testResource;
    }
}
