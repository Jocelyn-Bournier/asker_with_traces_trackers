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
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiCreatedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Resources\TestModelResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\TestResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\TestResourceFactory;

/**
 * Class TestByTestModelController
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestByTestModelController extends BaseController
{
    /**
     * Generate a test from a test model
     *
     * @OA\Post(
     *          path="/api/test-models/{testModelId}/tests/",
     *          @OA\Parameter(in="path", name="testModelId", parameter="testModelId"),
     *          @OA\Response(response="200", description="confirmation of test creation"),
     *     tags={"tests-models"},
     *      )
     *
     * @param $testModelId
     *
     * @return ApiCreatedResponse
     * @throws ApiNotFoundException
     */
    public function createAction($testModelId)
    {
        try {
            $test = $this->get('simple_it.exercise.test')->add($testModelId);

            $testResource = TestResourceFactory::create($test);

            return new ApiCreatedResponse($testResource, array('test', 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(TestResource::RESOURCE_NAME);
        }
    }

    /**
     * List the tests for a test model
     *
     * @OA\Get(
     *          path="/api/test-models/{testModelId}/tests/",
     *          @OA\Parameter(in="query", name="collectionInformation", parameter="collectionInformation"),
     *          @OA\Parameter(in="path", name="testModelId", parameter="testModelId"),
     *          @OA\Response(response="200", description="confirmation of test creation"),
     *     tags={"tests-models"},
     *      )
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $testModelId
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function listAction(CollectionInformation $collectionInformation, $testModelId)
    {
        try {
            $tests = $this->get('simple_it.exercise.test')->getAll
                (
                    $collectionInformation,
                    $testModelId
                );

            $testResources = TestResourceFactory::createCollection($tests);

            return new ApiGotResponse($testResources, array('list', 'Default'));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(TestModelResource::RESOURCE_NAME);
        }
    }
}
