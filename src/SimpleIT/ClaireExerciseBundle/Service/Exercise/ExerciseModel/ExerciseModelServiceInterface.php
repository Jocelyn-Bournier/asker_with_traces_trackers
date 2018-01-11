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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseModel;

use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Exception\InconsistentEntityException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResource;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\SharedEntity\SharedEntityServiceInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Interface for service which manages the exercise generation
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
interface ExerciseModelServiceInterface extends SharedEntityServiceInterface
{
    /**
     * Get an entity by its id
     *
     * @param int $entityId
     *
     * @return ExerciseModel
     * @throws NonExistingObjectException
     */
    public function get($entityId);

    /**
     * Get the final parent entity (the one with content) pointed by an entity
     *
     * @param int $entityId
     *
     * @return ExerciseModel
     * @throws InconsistentEntityException
     */
    public function getParent($entityId);

    /**
     * Create an entity from a resource (no saving).
     * Required fields: type, title, [content or parent], draft, owner, author, archived, metadata
     * Must be null: id
     * Not used (computed) : required resources, required knowledge
     *
     * @param ExerciseModelResource $resource
     *
     * @return ExerciseModel
     */
    public function createFromResource($resource);

    /**
     * Create and add an entity from a resource (saving).
     * Required fields: type, title, [content or parent], draft, owner, author, archived, metadata
     * Must be null: id
     *
     * @param ExerciseModelResource $resource
     *
     * @return ExerciseModel
     */
    public function createAndAdd(
        $resource
    );

    /**
     * Add a new entity (saving). The id must be null.
     *
     * @param ExerciseModel $entity
     *
     * @return ExerciseModel
     */
    public function add(
        $entity
    );

    /**
     * Update an entity object from a Resource (no saving).
     * Only the fields that are not null in the resource are taken in account to edit the entity.
     * The id of an entity can never be modified (ignored if not null)
     *
     * @param ExerciseModelResource $resource
     * @param ExerciseModel         $entity
     *
     * @return ExerciseModel
     */
    public function updateFromResource(
        $resource,
        $entity
    );

    /**
     * Edit and save an entity given in form of a Resource object.
     * Only the fields that are not null in the resource are taken in account to edit the entity.
     * The id of the entity that must be modified is stored in the field id.
     * The id of an entity can never be modified.
     *
     * @param ExerciseModel $resource The resource corresponding to the entity
     * @param int           $userId   Id of the user who tries to edit
     *
     * @throws AccessDeniedException
     * @return ExerciseModel The edited and saved entity
     */
    public function edit(
        $resource,
        $userId
    );

    /**
     * Save an entity after modifications
     *
     * @param ExerciseModel $entity
     *
     * @return ExerciseModel
     */
    public function save($entity);

    /**
     * Get an entity by its id and by the owner id
     *
     * @param int $entityId
     * @param int $ownerId
     *
     * @return ExerciseModel
     */
    public function getByIdAndOwner($entityId, $ownerId);

    /**
     * Get an exercise Model (business object, no entity)
     *
     * @param int $exerciseModelId
     *
     * @return CommonModel
     */
    public function getModel($exerciseModelId);

    /**
     * Get an exercise model from an entity (business object, no entity)
     *
     * @param ExerciseModel $entity
     *
     * @return CommonModel
     */
    public function getModelFromEntity(ExerciseModel $entity);

    /**
     * Subscribe to an entity: the new entity is a pointer to the parent entity. It has no
     * content and no metadata because these elements rely on the parent.
     *
     * @param int $ownerId        The id of the owner who wants to get the new pointer
     * @param int $parentEntityId The id of the parent entity
     *
     * @return ExerciseModel
     */
    public function subscribe($ownerId, $parentEntityId);

    /**
     * Import an entity. The entity is duplicated and the required entities are also imported.
     *
     * @param int $userId
     * @param int $originalId The id of the original entity that must be duplicated
     *
     * @return ExerciseModel
     */
    public function import($userId, $originalId);
}
