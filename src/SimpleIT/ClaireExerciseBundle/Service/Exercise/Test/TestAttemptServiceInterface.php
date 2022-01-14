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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\Test;

use SimpleIT\ClaireExerciseBundle\Entity\Test\TestAttempt;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;

/**
 * Service which manages the test attempts
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
interface TestAttemptServiceInterface
{
    /**
     * Find a test attempt by its id
     *
     * @param int $testAttemptId Test attempt Id
     * @param int $userId
     *
     * @return TestAttempt
     */
    public function get($testAttemptId, $userId = null);

    /**
     * Add a new test attempt to the database.
     *
     * @param int $testId
     * @param int $userId
     *
     * @return TestAttempt
     */
    public function add($testId, $userId);

    /**
     * Get all the test attempts
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $userId
     * @param int                   $testId
     *
     * @return array
     */
    public function getAll(
        $collectionInformation = null,
        $userId = null,
        $testId = null
    );
}
