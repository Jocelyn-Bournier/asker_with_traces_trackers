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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\Test;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiCreatedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Resources\TestAttemptResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\TestResource;

/**
 * Class TestAttemptByTestController
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestAttemptByTestController extends BaseController
{
    /**
     * Create a test attempt for a test
     *
     * @OA\Post(
     *          path="/api/tests/{testId}/test-attempts/",
     *          @OA\Parameter(in="path", name="testId", parameter="testId"),
     *          @OA\Response(response="200", description="confirmation of attempt for the test"),
     *     tags={"tests"},
     *      )
     *
     * @param int $testId
     *
     * @return ApiCreatedResponse
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     */
    public function createAction($testId)
    {
        try {
            $testAttempt = $this->get('simple_it.exercise.test_attempt')->add(
                $testId,
                $this->getUserId()
            );

            $testAttemptResource = TestAttemptResourceFactory::create($testAttempt);

            return new ApiCreatedResponse($testAttemptResource, array('details', 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(TestResource::RESOURCE_NAME);
        }
    }

    /**
     * List the test attempts for this test
     *
     * @OA\Get(
     *          path="/api/tests/{testId}/test-attempts",
     *          @OA\Parameter(in="path", name="testId", parameter="testId"),
     *          @OA\Response(response="200", description="attempts corresponding to a test"),
     *     tags={"tests"},
     *      )
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $testId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function listAction(
        CollectionInformation $collectionInformation,
        $testId
    )
    {
        try {
            $testAttempts = $this->get('simple_it.exercise.test_attempt')->getAll(
                $collectionInformation,
                $this->getUserId(),
                $testId
            );

            $testAttemptResources = TestAttemptResourceFactory::createCollection($testAttempts);

            return new ApiGotResponse($testAttemptResources, array(
                'list',
                'Default'
            ));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(TestResource::RESOURCE_NAME);
        }
    }
}
