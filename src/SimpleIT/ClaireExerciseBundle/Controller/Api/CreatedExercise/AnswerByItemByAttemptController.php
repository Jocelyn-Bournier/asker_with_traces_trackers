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
use SimpleIT\ClaireExerciseBundle\Exception\AnswerAlreadyExistsException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidAnswerException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiResponse;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResourceFactory;



/**
 * API AnswerByItemByAttempt Controller
 * @OA\Info(title="AnswerByItemByAttempt API", version="1.0")
 * @OA\Server(url="http://asker.univ-lyon1.fr/")
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AnswerByItemByAttemptController extends BaseController
{
    /**
     * List the answers fot this item
     *
     * @OA\Get(
     *     path="/api/attempts/{attemptId}/items/{itemId}/answers",
     *     @OA\Parameter(in="path", name="attemptId", parameter="attemptId"),
     *     @OA\Parameter(in="path", name="itemId", parameter="itemId"),
     *     @OA\Response(response="200", description="List of answers corresponding to an item"),
     *     tags={"attempts"},
     * )
     *
     * @param int $attemptId
     * @param int $itemId
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function listAction($attemptId, $itemId)
    {
        try {

            $answers = $this->get('simple_it.exercise.answer')->getAll(
                $itemId,
                $attemptId,
                $this->getUserId()
            );

            $answerResources = AnswerResourceFactory::createCollection($answers);

            return new ApiGotResponse($answerResources, array('list', 'Default'));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(AnswerResource::RESOURCE_NAME);
        }
    }

    /**
     * Answer action. Create an answer for the given stored exercise.
     * @OA\Post(
     *     path="/api/attempts/{attemptId}/items/{itemId}/answers",
     *     @OA\Parameter(in="path", name="attemptId", parameter="attemptId"),
     *     @OA\Parameter(in="path", name="itemId", parameter="itemId"),
     *     @OA\Parameter(in="query", name="answerResource", parameter="answerResource"),
     *     @OA\Response(response="200", description="List of answers corresponding to an item"),
     *     tags={"attempts"},
     * )
     * @param int            $attemptId
     * @param int            $itemId
     * @param AnswerResource $answerResource
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function createAction($attemptId, $itemId, AnswerResource $answerResource)
    {
       try {
            $this->validateResource($answerResource, array('create', 'Default'));

            // send to the answer service in order to create the answer
            $itemResource = $this->get('simple_it.exercise.answer')
                ->add($itemId, $answerResource, $attemptId, $this->getUserId());

            // ANR COMPER : Create a statement to send to the LRS.
            // First retrieve the current user, then generate the statement & finally send it.
            // The function $statementFactory->generateAnswerStatement as a feature that checks if the user is attached to a directory with a frameworkId set.
            $user             = $this->get('security.context')->getToken()->getUser();
            $statementFactory = $this->container->get('app.statementFactoryService');
            $statement        = $statementFactory->generateAnswerStatement($user, $itemResource, $attemptId, $answerResource, $this->getDoctrine());
            $response         = $statementFactory->sendStatements($statement);

            return new ApiGotResponse($itemResource, array("corrected", 'Default'));


        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(AnswerResource::RESOURCE_NAME);
        } catch (InvalidAnswerException $iae) {
            throw new ApiBadRequestException($iae->getMessage());
        } catch (AnswerAlreadyExistsException $aaee) {
            throw new ApiBadRequestException($aaee->getMessage());
        }
    }
}
