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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\ExerciseModel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\Metadata;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModelMetadataFactory;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiConflictException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiCreatedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiDeletedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiEditedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResourceFactory;
use Symfony\Component\HttpFoundation\Request;




/**
 * API MetadataByExerciseModel Controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class MetadataByExerciseModelController extends BaseController
{
    /**
     * Get all metadata
     * @OA\Get(
     *          path="/api/exercise-models/{exerciseModelId}/metadatas/",
     *          @OA\Parameter(in="path", name="exerciseModelId", parameter="exerciseModelId"),
     *          @OA\Response(response="200", description="metadata of exercise model"),
     *     tags={"exercise-models"},
     *      )
     *
     * @param mixed                 $exerciseModelId
     * @param CollectionInformation $collectionInformation
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function listAction(
        $exerciseModelId,
        CollectionInformation $collectionInformation
    )
    {
        try {
            $metadatas = $this->get('simple_it.exercise.exercise_model_metadata')->getAll(
                $collectionInformation,
                $exerciseModelId,
                $this->getUserId()
            );

            $metadataResources = MetadataResourceFactory::createCollection($metadatas);

            return new ApiGotResponse($metadataResources);
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(MetadataResource::RESOURCE_NAME);
        }
    }

    /**
     * Get a metadata
     *
     * @OA\Get(
     *          path="/api/exercise-models/{exerciseModelId}/metadatas/{metadataKey}/",
     *          @OA\Parameter(in="path", name="exerciseModelId", parameter="exerciseModelId"),
     *          @OA\Parameter(in="path", name="metadataKey", parameter="metadataKey"),
     *          @OA\Response(response="200", description="metadata"),
     *     tags={"exercise-models"},
     *      )
     * @param mixed $exerciseModelId
     * @param mixed $metadataKey
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction($exerciseModelId, $metadataKey)
    {
        try {
            $metadata = $this->get('simple_it.exercise.exercise_model_metadata')
                ->getByEntity(
                    $exerciseModelId,
                    $metadataKey,
                    $this->getUserId()
                );

            $metadataResource = MetadataResourceFactory::create($metadata);

            return new ApiGotResponse($metadataResource);

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(MetadataResource::RESOURCE_NAME);
        }
    }

    /**
     * Create a metadata
     * @OA\Post(
     *          path="/api/exercise-models/{exerciseModelId}/metadatas/{metadataKey}/",
     *          @OA\Parameter(in="path", name="exerciseModelId", parameter="exerciseModelId"),
     *          @OA\Parameter(in="query", name="request", parameter="request"),
     *          @OA\Response(response="200", description="confirmation of creation"),
     *     tags={"exercise-models"},
     *      )
     *
     * @param Request $request
     * @param mixed   $exerciseModelId
     *
     * @throws ApiNotFoundException
     * @throws ApiConflictException;
     * @return ApiCreatedResponse
     */
    public function createAction(
        Request $request,
        $exerciseModelId
    )
    {
        try {
            $metadata = $this->createMetadata($request);

            $metadata = $this->get(
                'simple_it.exercise.exercise_model_metadata'
            )->addToEntity(
                    $exerciseModelId,
                    $metadata,
                    $this->getUserId()
                );

            $metadataResource = MetadataResourceFactory::create($metadata);

            return new ApiCreatedResponse($metadataResource);

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException();
        } catch (DBALException $eoe) {
            throw new ApiConflictException();
        }
    }

    /**
     * Edit a metadata
     * @OA\Put(
     *          path="/api/exercise-models/{exerciseModelId}/metadatas/{metadataKey}/",
     *          @OA\Parameter(in="path", name="exerciseModelId", parameter="exerciseModelId"),
     *          @OA\Parameter(in="path", name="metadataKey", parameter="metadataKey"),
     *          @OA\Parameter(in="query", name="metadata", parameter="metadata"),
     *          @OA\Response(response="200", description="confirmation of metadata edition"),
     *     tags={"exercise-models"},
     *      )
     *
     * @param MetadataResource $metadata
     * @param mixed            $exerciseModelId
     * @param string           $metadataKey
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiEditedResponse
     */
    public function editAction(
        MetadataResource $metadata,
        $exerciseModelId,
        $metadataKey
    )
    {
        try {
            $this->validateResource($metadata, array('edit'));

            $metadata = $this->get(
                'simple_it.exercise.exercise_model_metadata'
            )->saveFromEntity(
                    $exerciseModelId,
                    $metadata,
                    $metadataKey,
                    $this->getUserId()
                );

            $metadataResource = MetadataResourceFactory::create($metadata);

            return new ApiEditedResponse($metadataResource);

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(MetadataResource::RESOURCE_NAME);
        }
    }

    /**
     * Delete a metadata
     * @OA\Delete(
     *          path="/api/exercise-models/{exerciseModelId}/metadatas/{metadataKey}/",
     *          @OA\Parameter(in="path", name="exerciseModelId", parameter="exerciseModelId"),
     *          @OA\Parameter(in="path", name="metadataKey", parameter="metadataKey"),
     *          @OA\Response(response="200", description="confirmation of metadata deletion"),
     *     tags={"exercise-models"},
     *      )
     * @param mixed $exerciseModelId
     * @param mixed $metadataKey
     *
     * @throws ApiNotFoundException
     * @return ApiDeletedResponse
     */
    public function deleteAction($exerciseModelId, $metadataKey)
    {
        try {
            $this->get('simple_it.exercise.exercise_model_metadata')->removeFromEntity(
                $exerciseModelId,
                $metadataKey,
                $this->getUserId()
            );

            return new ApiDeletedResponse();

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(MetadataResource::RESOURCE_NAME);
        }
    }

    /**
     * Edit the list of metadata for this exercise model
     *
     * @OA\Put(
     *          path="/api/exercise-models/{exerciseModelId}/subscribe",
     *          @OA\Parameter(in="path", name="exerciseModelId", parameter="exerciseModelId"),
     *          @OA\Parameter(in="query", name="metadatas", parameter="metadatas"),
     *          @OA\Response(response="200", description="confirmation of metadata's edition"),
     *     tags={"exercise-models"},
     *      )
     * @param ArrayCollection $metadatas
     * @param int             $exerciseModelId
     *
     * @return ApiEditedResponse
     * @throws ApiNotFoundException
     * @throws ApiConflictException
     */
    public function editAllAction(ArrayCollection $metadatas, $exerciseModelId)
    {
        try {
            $resources = $this->get('simple_it.exercise.exercise_model')
                ->editMetadata
                (
                    $exerciseModelId,
                    $metadatas,
                    $this->getUserId()
                );

            $resourceResource = MetadataResourceFactory::createCollection($resources);

            return new ApiEditedResponse($resourceResource);

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(MetadataResource::RESOURCE_NAME);
        } catch (DBALException $eoe) {
            throw new ApiConflictException($eoe->getMessage());
        }
    }

    /**
     * Create a metadata from a request
     *
     * @param Request $request Request
     *
     * @return Metadata
     * @throws ApiBadRequestException
     */
    protected function createMetadata(Request $request)
    {
        $metadata = null;
        $content = json_decode($request->getContent(), true);
        if (!is_array($content) || count($content) !== 1) {
            throw new ApiBadRequestException('Metadata format is invalid');
        }

        foreach ($content as $key => $value) {
            $metadata = ExerciseModelMetadataFactory::create($key, $value);
        }

        return $metadata;
    }
}
