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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\DomainKnowledge;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use SimpleIT\ApiResourcesBundle\Exception\InvalidKnowledgeException;
use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Knowledge;
use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Metadata;
use SimpleIT\ClaireExerciseBundle\Entity\KnowledgeFactory;
use SimpleIT\ClaireExerciseBundle\Exception\InconsistentEntityException;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\CommonKnowledge;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula;
use SimpleIT\ClaireExerciseBundle\Model\Resources\KnowledgeResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\KnowledgeResourceFactory;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\DomainKnowledge\KnowledgeRepository;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\SharedEntity\SharedEntityService;

/**
 * Service which manages the domain knowledge
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class KnowledgeService extends SharedEntityService implements KnowledgeServiceInterface
{
    const ENTITY_TYPE = 'knowledge';

    /**
     * @var KnowledgeRepository
     */
    protected $entityRepository;

    /**
     * @var FormulaServiceInterface
     */
    private $formulaService;

    /**
     * Set formulaService
     *
     * @param \SimpleIT\ClaireExerciseBundle\Service\Exercise\DomainKnowledge\FormulaServiceInterface $formulaService
     */
    public function setFormulaService($formulaService)
    {
        $this->formulaService = $formulaService;
    }

    /**
     * Create and add an entity from a resource (saving).
     * Required fields: type, title, [content or parent], draft, owner, author, archived, metadata
     * Must be null: id
     *
     * @param KnowledgeResource $knowledgeResource
     *
     * @return Knowledge
     */
    public function createFromResource(
        $knowledgeResource
    )
    {
        $this->validateKnowledgeResource($knowledgeResource);

        $knowledge = KnowledgeFactory::createFromResource($knowledgeResource);

        parent::fillFromResource($knowledge, $knowledgeResource);
        $knowledge = $this->computeRequirements($knowledgeResource, $knowledge);

        return $knowledge;
    }

    /**
     * Update an entity object from a Resource (no saving).
     * Only the fields that are not null in the resource are taken in account to edit the entity.
     * The id of an entity can never be modified (ignored if not null)
     *
     * @param KnowledgeResource $knowledgeResource
     * @param Knowledge $knowledge
     *
     * @return Knowledge
     */
    public function updateFromResource(
        $knowledgeResource,
        $knowledge
    )
    {
        parent::updateFromSharedResource($knowledgeResource, $knowledge, 'knowledge_storage');
        $knowledge = $this->computeRequirements($knowledgeResource, $knowledge);

        if (!is_null($knowledgeResource->getArchived())) {
            $knowledge->setArchived($knowledgeResource->getArchived());
        }

        return $knowledge;
    }

    /**
     * Compute the requirements of the entity from the resource content (if content is empty,
     * no change)
     *
     * @param KnowledgeResource $knowledgeResource
     * @param Knowledge $knowledge
     * @param bool $import
     * @param int $ownerId
     *
     * @return Knowledge
     */
    private function computeRequirements(
        $knowledgeResource,
        $knowledge,
        $import = false,
        $ownerId = null
    )
    {
        if ($knowledgeResource->getContent() != null) {
            // required knowledges
            $knowledgeResource = $this->computeRequiredKnowledgesFromResource(
                $knowledgeResource,
                $import,
                $ownerId
            );
            $reqKnowledges = array();
            foreach ($knowledgeResource->getRequiredKnowledges() as $reqKnowledge) {
                $reqKnowledges[] = $this->get($reqKnowledge);
            }
            $knowledge->setRequiredKnowledges(new ArrayCollection($reqKnowledges));
        }

        // if public knowledge, set public all the requirements
        if ($knowledge->getPublic()) {
            /** @var Knowledge $reqKno */
            foreach ($knowledge->getRequiredKnowledges() as $reqKno) {
                if (!$reqKno->getPublic()) {
                    $pubKnoRes = new KnowledgeResource();
                    $pubKnoRes->setPublic(true);
                    $this->updateFromResource($pubKnoRes, $reqKno);
                }
            }
        }

        return $knowledge;
    }

    /**
     * Validate the knowledge resource
     *
     * @param KnowledgeResource $knowledgeResource
     *
     * @throws \SimpleIT\ApiResourcesBundle\Exception\InvalidKnowledgeException
     */
    public function validateKnowledgeResource(KnowledgeResource $knowledgeResource)
    {
        $knowledgeResource->getContent()->validate();

        if ($knowledgeResource->getType() === CommonKnowledge::FORMULA) {
            $content = $knowledgeResource->getContent();
            if (get_class($content) === KnowledgeResource::FORMULA_CLASS) {
                /** @var Formula $content */
                $this->formulaService->validateFormulaResource($content);
            } else {
                throw new InvalidKnowledgeException('Type are not consistent');
            }
        } else {
            throw new InvalidKnowledgeException('Unknown type of knowledge');
        }
    }

    /**
     * Check if the content of an exercise model is sufficient to generate exercises.
     *
     * @param Knowledge $entity
     * @param string $type
     * @param int $parentId
     * @param           $content
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InconsistentEntityException
     * @internal param \SimpleIT\ClaireExerciseBundle\Model\Resources\KnowledgeResource $resource
     * @return boolean True if the model is complete
     */
    protected function checkEntityComplete($entity, $type, $parentId, $content)
    {
        $errorCode = null;

        if ($parentId === null) {
            switch ($type) {
                case CommonKnowledge::FORMULA:
                    /** @var Formula $content */
                    try {

                        $this->formulaService->validateFormulaResource($content);
                        $errorCode = 'Formule invalide';
                        $complete = true;
                    } catch (InvalidKnowledgeException $ike) {
                        $complete = false;
                    }
                    break;
                default:
                    throw new InconsistentEntityException('Invalid type');
            }
        } else {
            if ($content !== null) {
                throw new InconsistentEntityException('A model must be a pointer OR have a content');
            }
            try {

                $parentEntity = $this->get($parentId);
            } catch (NonExistingObjectException $neoe) {
                throw new InconsistentEntityException('The parent knowledge cannot be found.');
            }

            $complete = $parentEntity->getPublic();
            if (!$parentEntity->getPublic()) {
                $errorCode = 101;
            }
        }

        $entity->setComplete($complete);
        $entity->setCompleteError($errorCode);
    }

    /**
     * Throws an exception if the content does not match the type
     *
     * @param $content
     * @param $type
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException
     */
    protected function validateType(
        $content,
        $type
    )
    {
        if (($type === CommonKnowledge::FORMULA &&
            get_class($content) !== KnowledgeResource::FORMULA_CLASS)
        ) {
            throw new InvalidTypeException('Content does not match exercise model type');
        }
    }

    /**
     * Computes the required knowledges according to the content of the resource resource and
     * write it in the corresponding field of the output resource
     *
     * @param KnowledgeResource $knowledgeResource
     * @param bool $import
     * @param int $ownerId
     *
     * @throws InvalidTypeException
     * @return KnowledgeResource
     */
    public function computeRequiredKnowledgesFromResource(
        $knowledgeResource,
        $import = false,
        $ownerId = null
    )
    {
        // no knowledge inter dependencies for the moment
        return $knowledgeResource;
    }

    /**
     * Subscribe to an entity: the new entity is a pointer to the parent entity. It has no
     * content and no metadata because these elements rely on the parent.
     *
     * @param int $ownerId The id of the owner who wants to get the new pointer
     * @param int $parentEntityId The id of the parent entity
     *
     * @return Knowledge
     */
    public function subscribe($ownerId, $parentEntityId)
    {
        /** @var Knowledge $knowledge */
        $knowledge = parent::subscribe($ownerId, $parentEntityId);
        $knowledge->setRequiredKnowledges(new ArrayCollection());

        $knowledge->setRequiredByModels(new ArrayCollection());
        $knowledge->setRequiredByResources(new ArrayCollection());
        $knowledge->setRequiredByKnowledges(new ArrayCollection());

        return $knowledge;
    }

    /**
     * Import an entity. Additionnal work, specific to entity type
     *
     * @param int $ownerId
     * @param Knowledge $entity The duplicata
     * @param User $originalOwner
     *
     * @return Knowledge
     */
    protected function importDetail($ownerId, $entity, $originalOwner = null)
    {
        $resource = KnowledgeResourceFactory::create($entity);

        // requirement
        $entity = $this->computeRequirements($resource, $entity, true, $ownerId);

        $this->em->flush();

        return $entity;
    }

    /**
     * @return Knowledge
     */
    protected function newEntity()
    {
        return new Knowledge();
    }

    /**
     * @return Metadata
     */
    protected function newMetadata()
    {
        return new Metadata();
    }

    /**
     * Clear an entity before import
     *
     * @param Knowledge $entity
     */
    protected function clearEntityDetail($entity)
    {
        $entity->setRequiredByModels(new ArrayCollection());
        $entity->setRequiredByResources(new ArrayCollection());
        $entity->setRequiredByKnowledges(new ArrayCollection());
        $entity->setRequiredKnowledges(new ArrayCollection());
    }

    /**
     * Set public a knowledge and all its requirements
     *
     * @param Knowledge $entity
     */
    public function makePublic($entity)
    {
        $entity->setPublic(true);

        foreach ($entity->getRequiredKnowledges() as $knowledge) {
            $this->makePublic($knowledge);
        }
    }

    /**
     * Checks if an entity can be removed (is required)
     *
     * @param Knowledge $entity
     *
     * @return boolean
     */
    public function canBeRemoved($entity)
    {
        if (count($entity->getRequiredByResources()) > 0) {
            return false;
        }

        if (count($entity->getRequiredByModels()) > 0) {
            return false;
        }

        if (count($entity->getRequiredByKnowledges()) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Duplicate an entity. Additionnal work, specific to entity type
     *
     * @param Knowledge $entity The duplicata
     * @param Knowledge $original
     *
     * @return Knowledge
     */
    protected function duplicateDetail($entity, $original)
    {
        $entity->setRequiredKnowledges($original->getRequiredKnowledges());
    }
}
