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
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResourceFactory;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;




/**
 * API Exercise controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseController extends BaseController
{
    /**
     * View a stored exercise
     *
     * @OA\Get(
     *     path="/api/exercises/{exerciseId}/",
     *     @OA\Parameter(in="path", name="exerciseId", parameter="exerciseId"),
     *     @OA\Response(response="200", description="An exercise"),
     *     tags={"exercises"},
     * )
     * @param int $exerciseId Exercise id
     *
     * @return ApiGotResponse
     * @throws ApiNotFoundException
     */
    public function viewAction($exerciseId)
    {
        try {
            $exercise = $this->get('simple_it.exercise.stored_exercise')->get($exerciseId);
            $exerciseResource = ExerciseResourceFactory::create($exercise);

            return new ApiGotResponse($exerciseResource, array("exercise", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ExerciseResource::RESOURCE_NAME);
        }
    }

    /**
     * Get all the exercises
     * @OA\Get(
     *     path="/api/exercises/{collectionInformation}/",
     *     @OA\Parameter(in="path", name="collectionInformation", parameter="collectionInformation"),
     *     @OA\Response(response="200", description="List of all exercises"),
     *     tags={"exercises"},
     * )
     * @param CollectionInformation $collectionInformation
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException
     * @return ApiGotResponse
     */
    public function listAction(CollectionInformation $collectionInformation)
    {
        try {
            if (!$this->get('security.context')->getToken()->getUser()->hasRole(
                'ROLE_WS_CREATOR'
            )
            ) {
                throw new AccessDeniedException();
            }

            $exercises = $this->get('simple_it.exercise.stored_exercise')->getAll(
                $collectionInformation
            );

            $exerciseResources = ExerciseResourceFactory::createCollection($exercises);

            return new ApiGotResponse($exerciseResources, array(
                'list',
                'Default'
            ));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ExerciseResource::RESOURCE_NAME);
        }
    }
}
