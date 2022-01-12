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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseResource;

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseResource\ExerciseResource;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\InconsistentEntityException;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectConstraints;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectId;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ResourceResource;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\SharedEntity\SharedEntityServiceInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service which manages the exercise resources
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */

interface ExerciseResourceServiceInterface extends SharedEntityServiceInterface
{
    /**
     * Get an entity
     *
     * @param int $entityId
     *
     * @return ExerciseResource
     * @throws NonExistingObjectException
     */
    public function get($entityId);

    /**
     * Get the final parent entity (the one with content) pointed by an entity
     *
     * @param int $entityId
     *
     * @return ExerciseResource
     * @throws InconsistentEntityException
     */
    public function getParent($entityId);

    /**
     * Create an entity from a resource (no saving).
     * Required fields: type, title, [content or parent], draft, owner, author, archived, metadata
     * Must be null: id
     * Not used (computed) : required resources, required knowledge
     *
     * @param ResourceResource $resource
     *
     * @return ExerciseResource
     */
    public function createFromResource($resource);

    /**
     * Create and add an entity from a resource (saving).
     * Required fields: type, title, [content or parent], draft, owner, author, archived, metadata
     * Must be null: id
     *
     * @param ResourceResource $resource
     *
     * @return ExerciseResource
     */
    public function createAndAdd(
        $resource
    );

    /**
     * Add a new entity (saving). The id must be null.
     *
     * @param ExerciseResource $entity
     *
     * @return ExerciseResource
     */
    public function add(
        $entity
    );

    /**
     * Update an entity object from a Resource (no saving).
     * Only the fields that are not null in the resource are taken in account to edit the entity.
     * The id of an entity can never be modified (ignored if not null)
     *
     * @param ResourceResource $resourceResource
     * @param ExerciseResource $exerciseResource
     *
     * @return ExerciseResource
     */
    public function updateFromResource(
        $resourceResource,
        $exerciseResource
    );

    /**
     * Edit and save an entity given in form of a Resource object.
     * Only the fields that are not null in the resource are taken in account to edit the entity.
     * The id of the entity that must be modified is stored in the field id.
     * The id of an entity can never be modified.
     *
     * @param ExerciseResource $resource The resource corresponding to the entity
     * @param int              $userId   Id of the user who tries to edit
     *
     * @throws AccessDeniedException
     * @return ExerciseResource The edited and saved entity
     */
    public function edit(
        $resource,
        $userId
    );

    /**
     * Save an entity after modifications
     *
     * @param ExerciseResource $entity
     *
     * @return ExerciseResource
     */
    public function save($entity);

    /**
     * Get an entity by its id and by the owner id
     *
     * @param int $entityId
     * @param int $ownerId
     *
     * @return ExerciseResource
     */
    public function getByIdAndOwner($entityId, $ownerId);

    /**
     * Get a resource in the form of an ExerciseObject (useful for exercise generation)
     *
     * @param ObjectId $resId
     * @param AskerUser     $owner
     *
     * @throws ApiNotFoundException
     * @return ExerciseObject
     */
    public function getExerciseObject(ObjectId $resId, AskerUser $owner);

    /**
     * Returns a list of ExerciseObjects matching the constraints
     *
     * @param ObjectConstraints $oc The constraints
     * @param int               $numberOfObjects
     * @param AskerUser              $owner
     *
     * @return array An array of ExerciseObjects
     */
    public function getExerciseObjectsFromConstraints(
        ObjectConstraints $oc,
        $numberOfObjects,
        AskerUser $owner
    );

    /**
     * Get all the resources that match the constraint and belong to the owner
     *
     * @param ObjectConstraints $oc
     * @param AskerUser $owner
     * @param bool $publicOnly True if only the public resources must be returned
     *
     * @return array
     */
    public function getResourcesFromConstraintsByOwner(ObjectConstraints $oc, AskerUser $owner, $publicOnly = false);

    /**
     * Computes the required resources according to the content of the resource resource and
     * write it in the corresponding field of the output resource
     *
     * @param ResourceResource $resourceResource
     *
     * @throws InvalidTypeException
     * @return ResourceResource
     */
    public function computeRequiredResourcesFromResource($resourceResource);

    /**
     * Computes the required knowledges according to the content of the resource resource and
     * write it in the corresponding field of the output resource
     *
     * @param ResourceResource $resourceResource
     *
     * @throws InvalidTypeException
     * @return ResourceResource
     */
    public function computeRequiredKnowledgesFromResource($resourceResource);

    /**
     * Subscribe to an entity: the new entity is a pointer to the parent entity. It has no
     * content and no metadata because these elements rely on the parent.
     *
     * @param int $ownerId        The id of the owner who wants to get the new pointer
     * @param int $parentEntityId The id of the parent entity
     *
     * @return ExerciseResource
     */
    public function subscribe($ownerId, $parentEntityId);

    /**
     * Import an entity. The entity is duplicated and the required entities are also imported.
     *
     * @param int $userId
     * @param int $originalId The id of the original entity that must be duplicated
     *
     * @return ExerciseResource
     */
    public function import($userId, $originalId);
}
