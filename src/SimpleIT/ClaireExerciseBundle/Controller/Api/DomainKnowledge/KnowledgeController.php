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

use Doctrine\DBAL\DBALException;
use SimpleIT\ApiResourcesBundle\Exception\InvalidKnowledgeException;
use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Knowledge;
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
use SimpleIT\ClaireExerciseBundle\Model\Resources\KnowledgeResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\KnowledgeResourceFactory;

/**
 * API Knowledge controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class KnowledgeController extends BaseController
{
    /**
     * View action. View a knowledge.
     *
     * @param int $knowledgeId
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction($knowledgeId)
    {
        try {
            /** @var KnowledgeResource $knowledge */
            $knowledgeResource = $this->get('simple_it.exercise.knowledge')->getContentFullResource(
                $knowledgeId,
                $this->getUserId()
            );

            return new ApiGotResponse($knowledgeResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(KnowledgeResource::RESOURCE_NAME);
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
            $knowledges = $this->get('simple_it.exercise.knowledge')->getAll(
                $collectionInformation,
                $this->getUserId()
            );

            $knowledgeResources = KnowledgeResourceFactory::createCollection($knowledges);

            return new ApiGotResponse($knowledgeResources, array(
                'details',
                'Default'
            ));
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(KnowledgeResource::RESOURCE_NAME);
        }
    }

    /**
     * Create a new knowledge (without metadata)
     *
     * @param KnowledgeResource $knowledgeResource
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function createAction(KnowledgeResource $knowledgeResource)
    {
        try {
            //$this->validateResource($knowledgeResource, array('create'));

            $userId = $this->getUserId();
            $knowledgeResource->setAuthor($userId);
            $knowledgeResource->setOwner($userId);

            /** @var Knowledge $knowledge */
            $knowledge = $this->get('simple_it.exercise.knowledge')->createAndAdd
                (
                    $knowledgeResource
                );

            $knowledgeResource = KnowledgeResourceFactory::create($knowledge);

            return new ApiCreatedResponse($knowledgeResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(KnowledgeResource::RESOURCE_NAME);
        } catch (NoAuthorException $nae) {
            throw new ApiBadRequestException($nae->getMessage());
        } catch (InvalidKnowledgeException $ike) {
            throw new ApiBadRequestException($ike->getMessage());
        }
    }

    /**
     * Edit a knowledge
     *
     * @param KnowledgeResource $knowledgeResource   knowledge resource
     * @param int               $knowledgeId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @throws ApiConflictException
     * @return ApiEditedResponse
     */
    public function editAction(KnowledgeResource $knowledgeResource, $knowledgeId)
    {
        try {
            //$this->validateResource($knowledgeResource, array('edit', 'Default'));

            $knowledgeResource->setId($knowledgeId);
            $knowledge = $this->get('simple_it.exercise.knowledge')->edit
                (
                    $knowledgeResource,
                    $this->getUserId()
                );
            $knowledgeResource = KnowledgeResourceFactory::create($knowledge);

            return new ApiEditedResponse($knowledgeResource);

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(KnowledgeResource::RESOURCE_NAME);
        } catch (DBALException $eoe) {
            throw new ApiConflictException($eoe->getMessage());
        } catch (NoAuthorException $nae) {
            throw new ApiBadRequestException($nae->getMessage());
        }
    }

    /**
     * Delete a knowledge
     *
     * @param int $knowledgeId
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException
     * @return \SimpleIT\ClaireExerciseBundle\Model\Api\ApiDeletedResponse
     */
    public function deleteAction($knowledgeId)
    {
        try {
            $this->get('simple_it.exercise.knowledge')->remove($knowledgeId, $this->getUserId());

            return new ApiDeletedResponse();

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(KnowledgeResource::RESOURCE_NAME);
        } catch (EntityDeletionException $ede) {
            throw new ApiBadRequestException($ede->getMessage());
        }
    }

    /**
     * Subscribe to a knowledge
     *
     * @param int $knowledgeId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function subscribeAction($knowledgeId)
    {
        try {
            $knowledge = $this->get('simple_it.exercise.knowledge')->subscribe(
                $this->getUserId(),
                $knowledgeId
            );

            $knowledgeResource = KnowledgeResourceFactory::create($knowledge);

            return new ApiCreatedResponse($knowledgeResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(KnowledgeResource::RESOURCE_NAME);
        }
    }

    /**
     * Duplicate a knowledge
     *
     * @param int $knowledgeId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function duplicateAction($knowledgeId)
    {
        try {
            /** @var Knowledge $knowledge */
            $knowledge = $this->get('simple_it.exercise.knowledge')->duplicate(
                $knowledgeId,
                $this->getUserId()
            );

            $knowledgeResource = KnowledgeResourceFactory::create($knowledge);

            return new ApiCreatedResponse($knowledgeResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(KnowledgeResource::RESOURCE_NAME);
        }
    }

    /**
     * Import a knowledge
     *
     * @param int $knowledgeId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function importAction($knowledgeId)
    {
        try {
            /** @var Knowledge $knowledge */
            $knowledge = $this->get('simple_it.exercise.knowledge')->import(
                $this->getUserId(),
                $knowledgeId
            );

            $knowledgeResource = KnowledgeResourceFactory::create($knowledge);

            return new ApiCreatedResponse($knowledgeResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(KnowledgeResource::RESOURCE_NAME);
        }
    }
}
