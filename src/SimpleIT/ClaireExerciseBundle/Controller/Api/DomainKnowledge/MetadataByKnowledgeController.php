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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\DomainKnowledge;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Metadata;
use SimpleIT\ClaireExerciseBundle\Entity\KnowledgeMetadataFactory;
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
 * API MetadataByKnowledge Controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class MetadataByKnowledgeController extends BaseController
{
    /**
     * Get all metadata
     *
     * @param mixed                 $knowledgeId
     * @param CollectionInformation $collectionInformation
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function listAction(
        $knowledgeId,
        CollectionInformation $collectionInformation
    )
    {
        try {
            $metadatas = $this->get('simple_it.exercise.knowledge_metadata')->getAll(
                $collectionInformation,
                $knowledgeId,
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
     * @param mixed $knowledgeId
     * @param mixed $metadataKey
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction($knowledgeId, $metadataKey)
    {
        try {
            $metadata = $this->get('simple_it.exercise.knowledge_metadata')->getByEntity(
                $knowledgeId,
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
     *
     * @param Request $request
     * @param mixed   $knowledgeId
     *
     * @throws ApiNotFoundException
     * @throws ApiConflictException;
     * @return ApiCreatedResponse
     */
    public function createAction(
        Request $request,
        $knowledgeId
    )
    {
        try {
            $metadata = $this->createMetadata($request);

            $metadata = $this->get('simple_it.exercise.knowledge_metadata')->addToEntity(
                $knowledgeId,
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
     *
     * @param MetadataResource $metadata
     * @param mixed            $knowledgeId
     * @param string           $metadataKey
     *
     * @throws ApiNotFoundException
     * @return ApiEditedResponse
     */
    public function editAction(
        MetadataResource $metadata,
        $knowledgeId,
        $metadataKey
    )
    {
        try {
            //$this->validateResource($metadata, array('edit'));

            $metadata = $this->get('simple_it.exercise.knowledge_metadata')->saveFromEntity(
                $knowledgeId,
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
     *
     * @param mixed $knowledgeId
     * @param mixed $metadataKey
     *
     * @throws ApiNotFoundException
     * @return ApiDeletedResponse
     */
    public function deleteAction($knowledgeId, $metadataKey)
    {
        try {
            $this->get('simple_it.exercise.knowledge_metadata')->removeFromEntity(
                $knowledgeId,
                $metadataKey,
                $this->getUserId()
            );

            return new ApiDeletedResponse();

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(MetadataResource::RESOURCE_NAME);
        }
    }

    /**
     * Edit the list of metadata for this knowledge
     *
     * @param ArrayCollection $metadatas
     * @param int             $knowledgeId
     *
     * @return ApiEditedResponse
     * @throws ApiNotFoundException
     * @throws ApiConflictException
     */
    public function editAllAction(ArrayCollection $metadatas, $knowledgeId)
    {
        try {
            $knowledge = $this->get('simple_it.exercise.knowledge')
                ->editMetadata
                (
                    $knowledgeId,
                    $metadatas,
                    $this->getUserId()
                );

            $knowledgeResource = MetadataResourceFactory::createCollection($knowledge);

            return new ApiEditedResponse($knowledgeResource);

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
        if (!is_array($content) || count($content) != 1) {
            throw new ApiBadRequestException('Metadata format is invalid');
        }

        foreach ($content as $key => $value) {
            $metadata = KnowledgeMetadataFactory::create($key, $value);
        }

        return $metadata;
    }
}
