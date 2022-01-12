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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\User;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Resources\UserResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\UserResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AskerUserResourceFactory;

/**
 * API user controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class UserController extends BaseController
{
    /**
     * Get a specific user resource
     *
     * @OA\Get(
     *          path="/api/users/{userId}",
     *          @OA\Parameter(in="path", name="userId", parameter="userId"),
     *          @OA\Response(response="200", description="specific user"),
     *     tags={"users"},
     *      )
     *
     * @param int $userId
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction($userId)
    {
        try {
            /** @var User $user */
            $user = $this->get('simple_it.exercise.user')->get($userId);

            $userResource = UserResourceFactory::create($user);

            return new ApiGotResponse($userResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(UserResource::RESOURCE_NAME);
        }
    }

    /**
     * Get all the users
     * @OA\Get(
     *          path="/api/users",
     *          @OA\Response(response="200", description="list of all users"),
     *     tags={"users"},
     *      )
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException
     * @return ApiGotResponse
     */
    public function listAction()
    {
        try {

            $users = $this->get('simple_it.exercise.user')->getAll();

            $userResources = UserResourceFactory::createCollection($users);

            return new ApiGotResponse($userResources, array(
                'details',
                'Default'
            ));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(UserResource::RESOURCE_NAME);
        }
    }

    /**
     * @OA\Get(
     *          path="/api/users/available/managers",
     *          @OA\Response(response="200", description=""),
     *     tags={"users"},
     *      )
     *
     */
    public function availableManagersAction()
    {
        try {
            $teachers = $this->get('simple_it.exercise.user')->allTeachers();

            $teachersResources = AskerUserResourceFactory::createCollectionFromArray($teachers);

            return new ApiGotResponse($teachersResources, array(
                'details',
                'Default'
            ));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(UserResource::RESOURCE_NAME);
        }
    }
}
