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

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Attempt;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\StoredExercise;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestAttempt;

/**
 * Class to manage the creation of Attempts
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class AttemptFactory
{
    /**
     * Create a new Attempt
     *
     * @param StoredExercise $exercise
     * @param AskerUser           $user
     * @param TestAttempt    $testAttempt
     * @param int            $position
     *
     * @return TestAttempt
     */
    public static function create(
        StoredExercise $exercise,
        AskerUser $user,
        $testAttempt,
        $position = null
    )
    {
        $attempt = new Attempt();
        $attempt->setExercise($exercise);
        $attempt->setUser($user);
        $attempt->setCreatedAt(new \DateTime());

        $attempt->setPosition($position);

        if (is_null($testAttempt)) {
            $attempt->setTestAttempt(null);
        } else {
            $attempt->setTestAttempt($testAttempt);
        }

        return $attempt;
    }
}
