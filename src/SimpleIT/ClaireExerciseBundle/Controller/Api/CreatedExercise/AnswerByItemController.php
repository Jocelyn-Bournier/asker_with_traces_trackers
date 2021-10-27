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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\CreatedExercise;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResourceFactory;

/**
 * API ItemByExercise controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AnswerByItemController extends BaseController
{
    /**
     * List the answers fot this item
     *
     * @OA\Get(
     *          path="/api/items/{itemId}/answers",
     *          @OA\Parameter(in="path", name="itemId", parameter="itemId"),
     *          @OA\Response(response="200", description="list of all answers corresponding to an item"),
     *     tags={"items"},
     *      )
     * @param $itemId
     *
     * @return ApiGotResponse
     * @throws ApiNotFoundException
     */
    public function listAction($itemId)
    {
        try {
            $answers = $this->get('simple_it.exercise.answer')->getAll(
                $itemId,
                null,
                $this->getUserId()
            );

            $answerResources = AnswerResourceFactory::createCollection($answers);

            return new ApiGotResponse($answerResources, array('list', 'Default'));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(AnswerResource::RESOURCE_NAME);
        }
    }

    /**
     * View action. View an item with its solution and the user's answer.
     * @OA\Get(
     *          path="/api/items/{itemId}/answers/{answerId}",
     *          @OA\Parameter(in="path", name="itemId", parameter="itemId"),
     *          @OA\Parameter(in="path", name="answerId", parameter="answerId"),
     *          @OA\Response(response="200", description="an item with its solution and the user's answer"),
     *     tags={"items"},
     *      )
     * @param $itemId
     * @param $answerId
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction($itemId, $answerId)
    {
        try {
            // Call to the item service to get the item and its correction if there is one
            $itemResource = $this->get('simple_it.exercise.item')
                ->findItemAndCorrectionById($itemId, $answerId);

            return new ApiGotResponse($itemResource, array('details', 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(AnswerResource::RESOURCE_NAME);
        }
    }
}
