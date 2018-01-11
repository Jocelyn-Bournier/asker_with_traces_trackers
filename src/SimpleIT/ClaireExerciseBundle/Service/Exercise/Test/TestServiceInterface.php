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

use SimpleIT\ClaireExerciseBundle\Entity\Test\Test;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;

/**
 * Interface for service which manages the tests
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
interface TestServiceInterface
{
    /**
     * Find a test by its id
     *
     * @param int $testId Test Id
     *
     * @throws NonExistingObjectException
     * @return Test
     */
    public function get($testId);

    /**
     * Add a new test to the database. This test is linked to the test model.
     *
     * @param int $testModelId
     *
     * @return Test
     */
    public function add($testModelId);

    /**
     * Get all the tests
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $testModelId
     *
     * @return array
     */
    public function getAll($collectionInformation = null, $testModelId = null);
}
