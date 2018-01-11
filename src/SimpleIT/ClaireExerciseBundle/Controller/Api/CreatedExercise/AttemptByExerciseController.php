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
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidAnswerException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiCreatedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiResponse;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AttemptResourceFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * API AttemptByExercise controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AttemptByExerciseController extends BaseController
{
    /**
     * List the attempts fot this exercise
     *
     * @param Request               $request
     * @param CollectionInformation $collectionInformation
     * @param int                   $exerciseId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function listAction(
        Request $request,
        CollectionInformation $collectionInformation,
        $exerciseId
    )
    {
        try {
            // get the user
            $user = $request->get('user');

            $userId = null;
            if (is_null($user) || $user === 'me') {
                if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                    throw new ApiBadRequestException("A user must be authenticated");
                }
                $userId = $this->getUserId();
            } elseif ($user !== "all") {
                throw new ApiBadRequestException('Incorrect value for parameter user: ' . $user);
            }

            $attempts = $this->get('simple_it.exercise.attempt')->getAll(
                $collectionInformation,
                $userId,
                $exerciseId
            );

            $attemptResources = AttemptResourceFactory::createCollection($attempts);

            return new ApiGotResponse($attemptResources, array('list', 'Default'));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(AnswerResource::RESOURCE_NAME);
        }
    }

    /**
     * Create a new attempt for this exercise
     *
     * @param int $exerciseId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function createAction($exerciseId)
    {
        try {
            // get the user
            if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                throw new ApiBadRequestException("A user must be authenticated");
            }
            $userId = $this->getUserId();

            $attempt = $this->get('simple_it.exercise.attempt')->add($exerciseId, $userId);

            $attemptResource = AttemptResourceFactory::create($attempt);

            return new ApiCreatedResponse($attemptResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(AnswerResource::RESOURCE_NAME);
        } catch (InvalidAnswerException $iae) {
            throw new ApiBadRequestException(AnswerResource::RESOURCE_NAME);
        }
    }
}
