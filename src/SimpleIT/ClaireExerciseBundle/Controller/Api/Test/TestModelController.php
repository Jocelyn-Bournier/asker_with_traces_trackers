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

use Doctrine\DBAL\DBALException;
use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiConflictException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\EntityDeletionException;
use SimpleIT\ClaireExerciseBundle\Exception\NoAuthorException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiCreatedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiDeletedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiEditedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiResponse;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Resources\TestModelResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\TestModelResourceFactory;

/**
 * API Test Model controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestModelController extends BaseController
{
    /**
     * Get a specific test Model resource
     *
     * @param int $testModelId Exercise Model id
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction($testModelId)
    {
        try {
            $testModel = $this->get('simple_it.exercise.test_model')->get($testModelId);
            $testModelResource = TestModelResourceFactory::create($testModel);

            return new ApiGotResponse($testModelResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(TestModelResource::RESOURCE_NAME);
        }
    }

    /**
     * Get the list of test models.
     *
     * @param CollectionInformation $collectionInformation
     *
     * @return ApiGotResponse
     */
    public function listAction(CollectionInformation $collectionInformation)
    {
        $testModels = $this->get('simple_it.exercise.test_model')->getAll(
            $collectionInformation
        );

        $testModelResources = TestModelResourceFactory::createCollection($testModels);

        return new ApiGotResponse($testModelResources, array('list', 'Default'));
    }

    /**
     * Create a new test model
     *
     * @param TestModelResource $testModelResource
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function createAction(
        TestModelResource $testModelResource
    )
    {
        try {
            $userId = null;
            if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                $userId = $this->getUserId();
            }

            $this->validateResource($testModelResource, array('create', 'Default'));

            $model = $this->get('simple_it.exercise.test_model')->createAndAdd
                (
                    $testModelResource,
                    $userId
                );

            $testModelResource = TestModelResourceFactory::create($model);

            return new ApiCreatedResponse($testModelResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(TestModelResource::RESOURCE_NAME);
        } catch (NoAuthorException $nae) {
            throw new ApiBadRequestException($nae->getMessage());
        }
    }

    /**
     * Edit a model
     *
     * @param TestModelResource $testModelResource
     * @param int               $testModelId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @throws ApiConflictException
     * @return ApiEditedResponse
     */
    public function editAction(TestModelResource $testModelResource, $testModelId)
    {
        try {
            $this->validateResource($testModelResource, array('edit', 'Default'));

            $resource = $this->get('simple_it.exercise.test_model')->edit
                (
                    $testModelResource,
                    $testModelId
                );
            $testModelResource = TestModelResourceFactory::create($resource);

            return new ApiEditedResponse($testModelResource);

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(TestModelResource::RESOURCE_NAME);
        } catch (DBALException $eoe) {
            throw new ApiConflictException($eoe->getMessage());
        } catch (NoAuthorException $nae) {
            throw new ApiBadRequestException($nae->getMessage());
        }
    }

    /**
     * Delete a model
     *
     * @param int $testModelId
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException
     * @return ApiDeletedResponse
     */
    public function deleteAction($testModelId)
    {
        try {
            $this->get('simple_it.exercise.test_model')->remove($testModelId);

            return new ApiDeletedResponse();

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(TestModelResource::RESOURCE_NAME);
        } catch (EntityDeletionException $ede) {
            throw new ApiBadRequestException($ede->getMessage());
        }
    }
}
