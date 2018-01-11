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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\SharedEntity;

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use JMS\Serializer\SerializationContext;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\Metadata;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\SharedEntity;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntityMetadataFactory;
use SimpleIT\ClaireExerciseBundle\Exception\EntityDeletionException;
use SimpleIT\ClaireExerciseBundle\Exception\InconsistentEntityException;
use SimpleIT\ClaireExerciseBundle\Exception\MissingIdException;
use SimpleIT\ClaireExerciseBundle\Exception\NoAuthorException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\SharedResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\SharedResourceFactory;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\SharedEntity\SharedEntityRepository;
use SimpleIT\ClaireExerciseBundle\Service\Serializer\SerializerInterface;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;
use SimpleIT\ClaireExerciseBundle\Service\User\UserService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service which manages the exercise generation
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class SharedEntityService extends TransactionalService implements SharedEntityServiceInterface
{
    const ENTITY_TYPE = '';

    /**
     * @var SharedEntityRepository $entityRepository
     */
    protected $entityRepository;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var SharedMetadataService
     */
    private $metadataService;

    /**
     * Set serializer
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Set SharedEntityRepository
     *
     * @param SharedEntityRepository $entityRepository
     */
    public function setEntityRepository($entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * Set userService
     *
     * @param UserService $userService
     */
    public function setUserService($userService)
    {
        $this->userService = $userService;
    }

    /**
     * Set metadataService
     *
     * @param SharedMetadataService $metadataService
     */
    public function setMetadataService($metadataService)
    {
        $this->metadataService = $metadataService;
    }

    /**
     * @inheritdoc
     */
    public function get($entityId)
    {
        $entity = $this->entityRepository->find($entityId);
        if (is_null($entity)) {
            throw new NonExistingObjectException();
        }

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function getParent($entityId)
    {
        $entity = $this->get($entityId);

        while ($entity->getContent() === null) {
            if ($entity->getParent() === null) {
                throw new InconsistentEntityException('The entity has no content and no parent');
            }
            $entity = $entity->getParent();
        }

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function getAll(
        $collectionInformation = null,
        $authenticatedUserId = null,
        $ownerId = null,
        $authorId = null,
        $parentEntityId = null,
        $forkFromEntityId = null,
        $isRoot = null,
        $isPointer = null
    )
    {
        $owner = null;
        if (!is_null($ownerId)) {
            $owner = $this->userService->get($ownerId);
        }

        $author = null;
        if (!is_null($authorId)) {
            $author = $this->userService->get($authorId);
        }

        $parentEntity = null;
        if (!is_null($parentEntityId)) {
            $parentEntity = $this->get($parentEntityId);
        }

        $forkFromEntity = null;
        if (!is_null($forkFromEntityId)) {
            $forkFromEntity = $this->get($forkFromEntityId);
        }

        return $this->entityRepository->findAll(
            $collectionInformation,
            $authenticatedUserId,
            $owner,
            $author,
            $parentEntity,
            $forkFromEntity,
            $isRoot,
            $isPointer
        );
    }

    /**
     * Fill an entity from a resource
     *
     * @param SharedEntity $entity
     * @param SharedResource $sharedResource
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NoAuthorException
     * @return SharedEntity
     */
    protected function fillFromResource(
        SharedEntity &$entity,
        SharedResource $sharedResource
    )
    {
        // complete
        $this->checkEntityComplete(
            $entity,
            $sharedResource->getType(),
            $sharedResource->getParent(),
            $sharedResource->getContent()
        );

        // author
        if (is_null($sharedResource->getAuthor())) {
            throw new NoAuthorException();
        } else {
            $authorId = $sharedResource->getAuthor();
        }
        $entity->setAuthor(
            $this->userService->get($authorId)
        );

        // owner
        if (is_null($sharedResource->getOwner())) {
            throw new NoAuthorException('No owner for this entity...');
        } else {
            $ownerId = $sharedResource->getOwner();
        }
        $entity->setOwner(
            $this->userService->get($ownerId)
        );

        // parent entity
        if (!is_null($sharedResource->getParent())) {
            $entity->setParent(
                $this->get($sharedResource->getParent())
            );
        }

        // fork from
        if (!is_null($sharedResource->getForkFrom())) {
            $entity->setForkFrom(
                $this->get($sharedResource->getForkFrom())
            );
        }

        // metadata and keywords
        $entity->setMetadata($this->metadataAndKeyWords($sharedResource, $entity));

        return $entity;
    }

    /**
     * Create an array of metadata entities from metadata and keyword in resource
     *
     * @param SharedResource $sharedResource
     * @param SharedEntity $entity
     *
     * @return ArrayCollection
     */
    private function metadataAndKeyWords($sharedResource, $entity)
    {
        $metadata = array();
        $resMetadata = $sharedResource->getMetadata();
        $resKeywords = $sharedResource->getKeywords();

        // add keywords if any
        if (!empty($resKeywords)) {
            $resMetadata[] = MetadataResourceFactory::createFromKeyValue(
                MetadataResource::MISC_METADATA_KEY,
                implode(';', $resKeywords)
            );
        }

        if (!empty($resMetadata)) {
            foreach ($resMetadata as $resMetaD) {
                $md = SharedEntityMetadataFactory::createFromResource(
                    static::ENTITY_TYPE,
                    $resMetaD
                );
                $md->setEntity($entity);
                $metadata[] = $md;
            }
        }

        return new ArrayCollection($metadata);
    }

    /**
     * @inheritdoc
     */
    abstract public function createFromResource($resource);

    /**
     * @inheritdoc
     */
    public function createAndAdd(
        $resource
    )
    {
        $entity = $this->createFromResource($resource);

        return $this->add($entity);
    }

    /**
     * @inheritdoc
     */
    public function add(
        $entity
    )
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    /**
     * Update an entity object from a Resource
     *
     * @param SharedResource $resource
     * @param SharedEntity $entity
     * @param string $storageGroup
     */
    protected function updateFromSharedResource(
        SharedResource $resource,
        SharedEntity &$entity,
        $storageGroup
    )
    {
        if (!is_null($resource->getTitle())) {
            $entity->setTitle($resource->getTitle());
        }

        if (!is_null($resource->getType())) {
            $entity->setType($resource->getType());
        }

        // public can be modified only to make public (not to make private)
        if ($resource->getPublic() === true) {
            $this->makePublic($entity);
        }

        if (!is_null($resource->getDraft())) {
            $entity->setDraft($resource->getDraft());
        }

        if (!is_null($resource->getKeywords() || !is_null($resource->getMetadata()))) {
            $this->metadataService->deleteAllByEntity($entity);
            $entity->setMetadata($this->metadataAndKeyWords($resource, $entity));
        }

        $content = $resource->getContent();
        if (!is_null($content)) {
            $context = SerializationContext::create();
            $context->setGroups(array($storageGroup, 'Default'));
            $entity->setContent(
                $this->serializer->jmsSerialize($content, 'json', $context)
            );

            $this->validateType($content, $entity->getType());

            if ($entity->getParent() === null) {
                $parentId = null;
            } else {
                $parentId = $entity->getParent()->getId();
            }
            // Check if the entity is complete with the new content
            $oldComplete = $entity->getComplete();
            $this->checkEntityComplete(
                $entity,
                $entity->getType(),
                $parentId,
                $content
            );

            // update children if necessary
            if ($oldComplete != $entity->getComplete()) {
                $this->updateChildrenComplete($entity, $entity->getComplete());
            }
        }
    }

    /**
     * Update the complete property of all the children of an entity
     *
     * @param SharedEntity $entity
     * @param bool $complete
     */
    private function updateChildrenComplete($entity, $complete)
    {
        /** @var SharedEntity $child */
        foreach ($entity->getChildren() as $child) {
            $child->setComplete($complete);

            if (count($child->getChildren()) > 0) {
                $this->updateChildrenComplete($child, $complete);
            }
        }
    }

    /**
     * @inheritdoc
     */
    abstract public function updateFromResource(
        $resource,
        $entity
    );

    /**
     * @inheritdoc
     */
    public function edit(
        $resource,
        $userId
    )
    {
        if (is_null($resource->getId())) {
            throw new MissingIdException();
        }

        $entity = $this->get($resource->getId());
        if ($entity->getOwner()->getId() !== $userId) {
            throw new AccessDeniedException();
        }

        $entity = $this->updateFromResource(
            $resource,
            $entity
        );
        //die('saved');

        //btw there is a flush in updateFromResource
        return $this->save($entity);
    }

    /**
     * @inheritdoc
     */
    public function save($entity)
    {
        $entity = $this->entityRepository->update($entity);
        //btw there is a flush in updateFromResource
        $this->em->flush();

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function remove($entityId, $userId)
    {
        $entity = $this->entityRepository->find($entityId);

        if ($entity->getOwner()->getId() !== $userId) {
            throw new AccessDeniedException();
        }

        if (!$this->canBeRemoved($entity)) {
            throw new EntityDeletionException('This entity is needed and cannot be deleted');
        }

        try {
            $this->entityRepository->delete($entity);
            $this->em->flush();
        } catch (DBALException $dbale) {
            throw new EntityDeletionException('This entity is needed and cannot be deleted');
        }
    }

    /**
     * @inheritdoc
     */
    abstract public function canBeRemoved($entity);

    /**
     * @inheritdoc
     */
    public function editMetadata($entityId, ArrayCollection $metadatas, $userId)
    {
        /** @var SharedEntity $entity */
        $entity = $this->entityRepository->find($entityId);
        if ($entity->getOwner()->getId() !== $userId) {
            throw new AccessDeniedException();
        }

        $this->metadataService->deleteAllByEntity($entity);

        $metadataCollection = array();
        foreach ($metadatas as $key => $value) {
            $md = SharedEntityMetadataFactory::create(static::ENTITY_TYPE, $key, $value);
            $md->setEntity($entity);
            $metadataCollection[] = $md;
        }
        $entity->setMetadata(new ArrayCollection($metadataCollection));

        $metadatas = $this->save($entity)->getMetadata();

        $this->em->flush();

        return $metadatas;
    }

    /**
     * @inheritdoc
     */
    public function getByIdAndOwner($entityId, $ownerId)
    {
        $owner = $this->userService->get($ownerId);

        return $this->entityRepository->findByIdAndOwner($entityId, $owner);
    }

    /**
     * Get a resource view of the entity with a content. If the entity has a content,
     * it just gets the resource view of the entity. If the entity has no content (and thus a
     * parent), the content of the resource is filled with the parent content
     *
     * @param int $entityId
     * @param     $userId
     *
     * @throws AccessDeniedException
     * @return SharedResource
     */
    public function getContentFullResource($entityId, $userId)
    {
        $entity = $this->get($entityId);
        if (!$entity->getPublic() &&
            $entity->getOwner()->getId() !== $userId
        ) {
            throw new AccessDeniedException();
        }

        return $this->getContentFullResourceFromEntity($entity);
    }

    /**
     * Get a resource view of the entity with a content. If the entity has a content,
     * it just gets the resource view of the entity. If the entity has no content (and thus a
     * parent), the content of the resource is filled with the parent content
     *
     * @param SharedEntity $entity
     *
     * @return SharedResource
     */
    public function getContentFullResourceFromEntity($entity)
    {
        $resource = SharedResourceFactory::createFromEntity($entity, static::ENTITY_TYPE);

        return $this->getContentFullResourceFromResource($resource);
    }

    /**
     * Get a list of resource with content.
     * For each entity, if it has a content, it just gets the resource view of the entity. If it
     * has no content (and thus a parent), the content of the resource is filled with the parent
     * content
     *
     * @param CollectionInformation $collectionInformation
     * @param int $ownerId
     * @param int $authorId
     * @param int $parentEntityId
     * @param int $forkFromEntityId
     * @param int $isRoot
     * @param int $isPointer
     * @param boolean $ignoreArchived
     * @param int $publicExceptUser Get the public entities that are not owned by this user
     * @param boolean $complete
     *
     * @return array An array of SharedResource
     */
    public function getAllContentFullResources(
        $collectionInformation = null,
        $ownerId = null,
        $authorId = null,
        $parentEntityId = null,
        $forkFromEntityId = null,
        $isRoot = null,
        $isPointer = null,
        $ignoreArchived = true,
        $publicExceptUser = null,
        $complete = null
    )
    {
        $entities = $this->getAll(
            $collectionInformation,
            $ownerId,
            $authorId,
            $parentEntityId,
            $forkFromEntityId,
            $isRoot,
            $isPointer,
            $ignoreArchived,
            $publicExceptUser,
            $complete
        );

        return $this->getAllContentFullResourcesFromEntityList($entities);
    }

    /**
     * Get a list of resource with content from a list of entities in a array.
     * For each entity, if it has a content, it just gets the resource view of the entity. If it
     * has no content (and thus a parent), the content of the resource is filled with the parent
     * content
     *
     * @param array $entities
     *
     * @return array
     */
    public function getAllContentFullResourcesFromEntityList(array $entities)
    {
        $resources = SharedResourceFactory::createFromEntityCollection(
            $entities,
            static::ENTITY_TYPE
        );

        // find a content for every pointer resource
        /** @var SharedResource $resource */
        foreach ($resources as &$resource) {
            if ($resource->getContent() === null) {
                $resource = $this->getContentFullResourceFromResource($resource);
            }
        }

        return $resources;
    }

    /**
     * Get a resource with a content. If the resource has a content,
     * it just gets the resource view of the entity. If the resource has no content (and thus a
     * parent), the content of the resource is filled with the parent content
     *
     * @param SharedResource $resource
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InconsistentEntityException
     * @return SharedResource
     */
    private function getContentFullResourceFromResource(SharedResource $resource)
    {
        $entity = $this->get($resource->getId());

        while ($entity->getContent() === null) {
            if ($entity->getParent() === null) {
                throw new InconsistentEntityException('Entity must have a content or a parent... Wrong is ' . $entity->getId());
            }
            $entity = $entity->getParent();
        }

        $parentResource = SharedResourceFactory::createFromEntity(
            $entity,
            static::ENTITY_TYPE
        );
        $resource->setContent($parentResource->getContent());
        $resource->setMetadata($parentResource->getMetadata());

        return $resource;
    }

    /**
     * Check if the content of an entity is sufficient to use it to generate exercises.
     *
     * @param SharedEntity $entity
     * @param string $type
     * @param int $parentId
     * @param              $content
     *
     * @internal param \SimpleIT\ClaireExerciseBundle\Model\Resources\SharedResource $resource
     * @return boolean True if the model is complete
     */
    abstract protected function checkEntityComplete($entity, $type, $parentId, $content);

    /**
     * Throws an exception if the content does not match the type
     *
     * @param $content
     * @param $type
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException
     */
    abstract protected function validateType(
        $content,
        $type
    );

    /**
     * @inheritdoc
     */
    public function subscribe($ownerId, $parentEntityId)
    {
        $owner = $this->userService->get($ownerId);
        $parent = $this->get($parentEntityId);

        if (!$parent->getPublic()) {
            throw new BadRequestHttpException('The imported object must be public');
        }

        $entity = clone($parent);
        $entity->setId(null);
        if (get_class(
                $entity
            ) === 'SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel'
        ) {
            /** @var ExerciseModel $entity */
            $entity->deleteResourceNode();
        }
        $entity->setContent(null);
        $entity->setArchived(false);
        $entity->setOwner($owner);
        $entity->setMetadata(new ArrayCollection());
        $entity->setForkFrom(null);
        $entity->setParent($parent);
        $entity->setPublic(false);

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    /**
     * Duplicate an entity (same owner).
     *
     * @param int $originalId The id of the entity to be duplicated
     * @param int $userId
     *
     * @throws AccessDeniedException
     * @return SharedEntity
     */
    public function duplicate($originalId, $userId)
    {
        $original = $this->get($originalId);
        if ($original->getOwner()->getId() !== $userId) {
            throw new AccessDeniedException();
        }

        $entity = $this->cloneEntity($original);
        $entity->setForkFrom($original);
        $entity->setOwner($original->getOwner());

        $this->duplicateDetail($entity, $original);
        $entity->setMetadata(new ArrayCollection());

        $this->em->persist($entity);
        $this->em->flush();

        // metadata
        $metadatas = array();
        /** @var Metadata $md */
        foreach ($original->getMetadata() as $md) {
            $newMd = $this->cloneMetadata($md);
            $newMd->setEntity($entity);
            $this->em->persist($newMd);
            $metadatas[] = $newMd;
        }
        $entity->setMetadata(new ArrayCollection($metadatas));

        $this->em->flush();

        return $entity;
    }

    /**
     * Import an entity from the id. Base work.
     *
     * @param int $userId
     * @param int $originalId The id of the original entity that must be duplicated
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @return SharedEntity
     */
    public function import($userId, $originalId)
    {
        $original = $this->get($originalId);
        if ($original->getOwner()->getId() === $userId) {
            throw new BadRequestHttpException('Object is already owned.');
        } elseif (!$original->getPublic()) {
            throw new BadRequestHttpException('The imported object must be public');
        }

        return $this->importByEntity($userId, $original);
    }

    /**
     * Import an entity from the entity. Base work.
     *
     * @param int $ownerId
     * @param SharedEntity $original The original entity that must be duplicated
     *
     * @return SharedEntity
     */
    public function importByEntity($ownerId, $original)
    {
        $entity = $this->cloneEntity($original);

        $entity->setOwner($this->userService->get($ownerId));
        $entity->setForkFrom($original);

        $this->em->persist($entity);
        $this->em->flush();

        // metadata
        $metadatas = array();
        /** @var Metadata $md */
        foreach ($original->getMetadata() as $md) {
            $newMd = $this->cloneMetadata($md);
            $newMd->setEntity($entity);
            $this->em->persist($newMd);

            if (substr($newMd->getValue(), 0, 2) === '__') {
                $rest = substr($newMd->getValue(), 2);
                if (is_numeric($rest)) {
                    $newMd->setValue('__' . $this->importOrLink($ownerId, $rest));
                }
            }

            $metadatas[] = $newMd;
        }
        $entity->setMetadata(new ArrayCollection($metadatas));

        return $this->importDetail($ownerId, $entity, $original->getOwner());
    }

    /**
     * Import an entity. Additional work, specific to entity type
     *
     * @param int $ownerId
     * @param SharedEntity $entity The duplicata
     * @param AskerUser $originalOwner
     *
     * @return SharedEntity
     */
    abstract protected function importDetail($ownerId, $entity, $originalOwner = null);

    /**
     * Clear an entity before import
     *
     * @param SharedEntity $entity
     */
    protected function clearEntity($entity)
    {
        $entity->setId(null);
        $entity->setMetadata(new ArrayCollection());
        $entity->setChildren(new ArrayCollection());
        $entity->setForkedBy(new ArrayCollection());
        $this->clearEntityDetail($entity);
    }

    /**
     * @param SharedEntity $original
     *
     * @return SharedEntity
     */
    protected function cloneEntity($original)
    {
        $entity = $this->newEntity();
        $entity->setArchived(false);
        $entity->setType($original->getType());
        $entity->setTitle($original->getTitle());
        $entity->setContent($original->getContent());
        $entity->setAuthor($original->getAuthor());
        $entity->setComplete($original->getComplete());
        $entity->setCompleteError($original->getCompleteError());
        $entity->setDraft($entity->getDraft());
        $entity->setPublic(false);
        $entity->setDraft($original->getDraft());

        return $entity;
    }

    /**
     * @return SharedEntity
     */
    abstract protected function newEntity();

    /**
     * @param Metadata $original
     *
     * @return Metadata
     */
    protected function cloneMetadata($original)
    {
        $metadata = $this->newMetadata();
        $metadata->setKey($original->getKey());
        $metadata->setValue($original->getValue());

        return $metadata;
    }

    /**
     * @return Metadata
     */
    abstract protected function newMetadata();

    /**
     * Clear an entity before import
     *
     * @param SharedEntity $entity
     */
    abstract protected function clearEntityDetail($entity);

    /**
     * Duplicate an entity. Additionnal work, specific to entity type
     *
     * @param SharedEntity $entity The duplicata
     * @param SharedEntity $original
     *
     * @return SharedEntity
     */
    abstract protected function duplicateDetail($entity, $original);

    /**
     * Import an entity if no direct children is owned by the user. (no flush if existing)
     *
     * @param int $ownerId
     * @param int $originalId The id of the original entity that must be duplicated
     *
     * @return int The id of the imported or already existing entity
     */
    public function importOrLink($ownerId, $originalId)
    {
        try {
            $fork = $this->entityRepository->findByForkFromAndOwner($originalId, $ownerId);

            return $fork->getId();
        } catch (NonExistingObjectException $neoe) {
            $entity = $this->import($ownerId, $originalId);

            return $entity->getId();
        }
    }

    /**
     * Make public an entity and all its requirements
     *
     * @param $entity
     */
    abstract public function makePublic($entity);
}
