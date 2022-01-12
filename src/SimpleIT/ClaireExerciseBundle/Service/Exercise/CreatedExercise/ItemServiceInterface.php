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

use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;

/**
 * Interface for service which manages the stored exercises
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
interface ItemServiceInterface
{
    /**
     * Find an item by its id
     *
     * @param int $itemId
     *
     * @return Item
     */
    public function get($itemId);

    /**
     * Find the item and the correction (if corrected by this user)
     *
     * @param int $itemId
     * @param int $attemptId
     *
     * @return ItemResource
     */
    public function findItemAndCorrectionByAttempt(
        $itemId,
        $attemptId
    );

    /**
     * Get an item by attempt
     *
     * @param int $itemId
     * @param int $attemptId
     *
     * @return Item
     */
    public function getByAttempt($itemId, $attemptId);

    /**
     * Get all items
     *
     * @param int $exerciseId  Exercise id
     *
     * @return array
     */
    public function getAll($exerciseId = null);

    /**
     * Get all items by attempt Id
     *
     * @param int $attemptId
     * @param int $userId
     *
     * @return array
     */
    public function getAllByAttempt(
        $attemptId,
        $userId
    );

    /**
     * Find an item and its correction
     *
     * @param $itemId
     * @param $answerId
     *
     * @return ItemResource
     * @throws NonExistingObjectException
     */
    public function findItemAndCorrectionById($itemId, $answerId);
}
