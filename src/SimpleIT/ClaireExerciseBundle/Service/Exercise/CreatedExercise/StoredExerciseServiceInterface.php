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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\CreatedExercise;

use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\StoredExercise;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;

/**
 * Interface for service which manages the stored exercises
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
interface StoredExerciseServiceInterface
{
    /**
     * Find a storedExercise by its id
     *
     * @param int $storedExerciseId Stored Exercise Id
     *
     * @throws NonExistingObjectException
     * @return StoredExercise
     */
    public function get($storedExerciseId);

    /**
     * Get all the stored exercises corresponding to an exercise model (if specified)
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $exerciseModelId
     *
     * @return array
     */
    public function getAll(
        $collectionInformation = null,
        $exerciseModelId = null
    );

    /**
     * Get all by test attempt id
     *
     * @param $testAttemptId
     *
     * @return array
     */
    public function getAllByTestAttempt($testAttemptId);

    /**
     * Add a new exercise model by owner exercise model id
     *
     * @param $oemId
     *
     * @return StoredExercise
     */
    public function addByExerciseModel($oemId);
}
