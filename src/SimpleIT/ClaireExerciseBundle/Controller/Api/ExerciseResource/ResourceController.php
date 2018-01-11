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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\ExerciseResource;

use Doctrine\DBAL\DBALException;
use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseResource\ExerciseResource;
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
use SimpleIT\ClaireExerciseBundle\Model\Resources\ResourceResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ResourceResourceFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * API Resource controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ResourceController extends BaseController
{
    /**
     * View action. View a resource.
     *
     * @param int $resourceId
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction($resourceId)
    {
        try {
            // Call to the resource service to get the resource
            $resourceResource = $this->get(
                'simple_it.exercise.exercise_resource'
            )->getContentFullResource($resourceId, $this->getUserId());

            return new ApiGotResponse($resourceResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        }
    }

    /**
     * Get all items
     *
     * @param CollectionInformation $collectionInformation
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException
     * @return ApiGotResponse
     */
    public function listAction(CollectionInformation $collectionInformation)
    {
        try {
            $resources = $this->get('simple_it.exercise.exercise_resource')->getAll(
                $collectionInformation,
                $this->getUserId()
            );

            $resourceResources = $this->get(
                'simple_it.exercise.exercise_resource'
            )->getAllContentFullResourcesFromEntityList(
                    $resources
                );

            return new ApiGotResponse($resourceResources, array(
                'details',
                'Default'
            ));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        }
    }

    /**
     * Create a new resource (without metadata)
     *
     * @param ResourceResource $resourceResource
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function createAction(
        ResourceResource $resourceResource
    )
    {
        try {
            $userId = $this->getUserId();

            $this->validateResource($resourceResource, array('create', 'Default'));

            $resourceResource->setAuthor($userId);
            $resourceResource->setOwner($userId);

            /** @var ExerciseResource $exerciseResource */
            $exerciseResource = $this->get('simple_it.exercise.exercise_resource')->createAndAdd
                (
                    $resourceResource,
                    $userId
                );

            $resourceResource = ResourceResourceFactory::create($exerciseResource);

            return new ApiCreatedResponse($resourceResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        } catch (NoAuthorException $nae) {
            throw new ApiBadRequestException($nae->getMessage());
        }
    }

    /**
     * Edit a resource
     *
     * @param ResourceResource $resourceResource   resource resource
     * @param int              $resourceId         Category if
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @throws ApiConflictException
     * @return ApiEditedResponse
     */
    public function editAction(ResourceResource $resourceResource, $resourceId)
    {
        try {
            $this->validateResource($resourceResource, array('edit', 'Default'));

            $resourceResource->setId($resourceId);
            $resource = $this->get('simple_it.exercise.exercise_resource')->edit
                (
                    $resourceResource,
                    $this->getUserId()
                );
            $resourceResource = ResourceResourceFactory::create($resource);

            return new ApiEditedResponse($resourceResource);

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        } catch (DBALException $eoe) {
            throw new ApiConflictException($eoe->getMessage());
        } catch (NoAuthorException $nae) {
            throw new ApiBadRequestException($nae->getMessage());
        }
    }

    /**
     * Delete a resource
     *
     * @param int $resourceId
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException
     * @return ApiDeletedResponse
     */
    public function deleteAction($resourceId)
    {
        try {
            $this->get('simple_it.exercise.exercise_resource')->remove(
                $resourceId,
                $this->getUserId()
            );

            return new ApiDeletedResponse();

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        } catch (EntityDeletionException $ede) {
            throw new ApiBadRequestException($ede->getMessage());
        }
    }

    /**
     * Subscribe to a resource
     *
     * @param int $resourceId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function subscribeAction($resourceId)
    {
        try {
            $resource = $this->get('simple_it.exercise.exercise_resource')->subscribe(
                $this->getUserId(),
                $resourceId
            );

            $resourceResource = $this->get('simple_it.exercise.exercise_resource')
                ->getContentFullResourceFromEntity($resource);


            return new ApiCreatedResponse($resourceResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        }
    }

    /**
     * Duplicate a resource
     *
     * @param int $resourceId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function duplicateAction($resourceId)
    {
        try {
            /** @var ExerciseResource $resource */
            $resource = $this->get('simple_it.exercise.exercise_resource')->duplicate(
                $resourceId,
                $this->getUserId()
            );

            $resourceResource = $this->get('simple_it.exercise.exercise_resource')
                ->getContentFullResourceFromEntity($resource);

            return new ApiCreatedResponse($resourceResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        }
    }

    /**
     * Import a resource
     *
     * @param int $resourceId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function importAction($resourceId)
    {
        try {
            /** @var ExerciseResource $resource */
            $resource = $this->get('simple_it.exercise.exercise_resource')->import(
                $this->getUserId(),
                $resourceId
            );

            $resourceResource = $this->get('simple_it.exercise.exercise_resource')
                ->getContentFullResourceFromEntity($resource);

            return new ApiCreatedResponse($resourceResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        }
    }

    /**
     * Upload an image in a picture resource (no resource update)
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException
     */
    public function uploadImageAction(Request $request)
    {
        /** @var UploadedFile $tmpFile */
        $tmpFile = $request->files->get('file');
        $fileName = $tmpFile->getClientOriginalName();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $size = filesize($tmpFile);
        if ($size > 2000000) {
            throw new ApiBadRequestException('File too big');
        }

        $hashName = sha1(uniqid());
//        $hashName = $this->container->get('claroline.utilities.misc')
//                ->generateGuid() . '.' . $extension;
//
        // copy the file
        $tmpFile->move(
            __DIR__."/../../../../../../web/uploads/claire_exercise/",
            $hashName
        );

        return new JsonResponse(array('fileName' => $hashName));
    }
}
