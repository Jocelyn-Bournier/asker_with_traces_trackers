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

use SimpleIT\ClaireExerciseBundle\Entity\Test\TestAttempt;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource;

/**
 * Class TestAttemptResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class TestAttemptResourceFactory
{

    /**
     * Create a TestResource collection
     *
     * @param array $testAttempts
     *
     * @return array
     */
    public static function createCollection(array $testAttempts)
    {
        $testAttemptResources = array();
        foreach ($testAttempts as $testAttempt) {
            $testAttemptResources[] = self::create($testAttempt);
        }

        return $testAttemptResources;
    }

    /**
     * Create a TestAttemptResource
     *
     * @param TestAttempt $testAttempt
     *
     * @return TestAttemptResource
     */
    public static function create(TestAttempt $testAttempt)
    {
        $testAttemptResource = new TestAttemptResource();
        $testAttemptResource->setId($testAttempt->getId());
        $testAttemptResource->setCreatedAt($testAttempt->getCreatedAt());
        $testAttemptResource->setTest($testAttempt->getTest()->getId());

        if (is_null($testAttempt->getUser())) {
            $testAttemptResource->setUser(null);
        } else {
            $testAttemptResource->setUser($testAttempt->getUser()->getId());
        }

        return $testAttemptResource;
    }
}
