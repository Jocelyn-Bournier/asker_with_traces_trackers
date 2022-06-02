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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JMS\Serializer\SerializationContext;
use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Knowledge;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\Metadata;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModelFactory;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseResource\ExerciseResource;
use SimpleIT\ClaireExerciseBundle\Exception\InconsistentEntityException;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException;
use SimpleIT\ClaireExerciseBundle\Exception\NoAuthorException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\LocalFormula;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\ResourceBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\ClassificationConstraints;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Group;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Model as GroupItems;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\ObjectBlock as GIObjectBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice\Model as MultipleChoice;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice\QuestionBlock as MCQuestionBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OpenEndedQuestion\Model as OpenEnded;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OpenEndedQuestion\QuestionBlock as OEQuestionBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\Model as OrderItems;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\ObjectBlock as OIObjectBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\Model as PairItems;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\PairBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\KnowledgeResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\MetadataConstraint;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ModelDocument;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectConstraints;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectId;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ResourceResource;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\ExerciseModel\ExerciseModelRepository;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\DomainKnowledge\KnowledgeServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseResource\ExerciseResourceServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\SharedEntity\SharedEntityService;

/**
 * Service which manages the exercise generation
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseModelService extends SharedEntityService implements ExerciseModelServiceInterface
{
    const ENTITY_TYPE = 'exerciseModel';

    /**
     * @var ExerciseModelRepository $exerciseModelRepository
     */
    protected $entityRepository;

    /**
     * @var ExerciseResourceServiceInterface
     */
    private $exerciseResourceService;

    /**
     * @var KnowledgeServiceInterface
     */
    private $knowledgeService;

    /**
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * @var ResourceNodeRepository
     */
    private $resourceNodeRepository;

    /**
     * Set exerciseResourceService
     *
     * @param ExerciseResourceServiceInterface $exerciseResourceService
     */
    public function setExerciseResourceService($exerciseResourceService)
    {
        $this->exerciseResourceService = $exerciseResourceService;
    }

    /**
     * Set knowledgeService
     *
     * @param \SimpleIT\ClaireExerciseBundle\Service\Exercise\DomainKnowledge\KnowledgeServiceInterface $knowledgeService
     */
    public function setKnowledgeService($knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Set resourceManager
     *
     * @param \Claroline\CoreBundle\Manager\ResourceManager $resourceManager
     */
    public function setResourceManager($resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    /**
     * Set resourceNodeRepository
     *
     * @param \Claroline\CoreBundle\Repository\ResourceNodeRepository $resourceNodeRepository
     */
    public function setResourceNodeRepository($resourceNodeRepository)
    {
        $this->resourceNodeRepository = $resourceNodeRepository;
    }

    /**
     * Get an exercise Model (business object, no entity)
     *
     * @param int $exerciseModelId
     *
     * @return CommonModel
     */
    public function getModel($exerciseModelId)
    {
        /** @var ExerciseModel $entity */
        $entity = $this->get($exerciseModelId);

        return $this->getModelFromEntity($entity);

    }

    /**
     * Get an exercise model from an entity  (business object, no entity)
     *
     * @param ExerciseModel $entity
     *
     * @return CommonModel
     * @throws \LogicException
     */
    public function getModelFromEntity(ExerciseModel $entity)
    {
        // deserialize to get an object
        switch ($entity->getType()) {
            case CommonExercise::MULTIPLE_CHOICE:
                $class = ExerciseModelResource::MULTIPLE_CHOICE_MODEL_CLASS;
                break;
            case CommonExercise::GROUP_ITEMS:
                $class = ExerciseModelResource::GROUP_ITEMS_MODEL_CLASS;
                break;
            case CommonExercise::ORDER_ITEMS:
                $class = ExerciseModelResource::ORDER_ITEMS_MODEL_CLASS;
                break;
            case CommonExercise::PAIR_ITEMS:
                $class = ExerciseModelResource::PAIR_ITEMS_MODEL_CLASS;
                break;
            case CommonExercise::OPEN_ENDED_QUESTION:
                $class = ExerciseModelResource::OPEN_ENDED_QUESTION_CLASS;
                break;
            default:
                throw new \LogicException('Unknown type of model');
        }

        return $this->serializer->jmsDeserialize($entity->getContent(), $class, 'json');
    }

    /**
     * Create an entity from a resource (no saving).
     * Required fields: type, title, [content or parent], draft, owner, author, archived, metadata
     * Must be null: id
     * Not used (computed) : required resources, required knowledge
     *
     * @param ExerciseModelResource $modelResource
     *
     * @throws NoAuthorException
     * @return ExerciseModel
     */
    public function createFromResource($modelResource)
    {
        $model = ExerciseModelFactory::createFromResource($modelResource);

        parent::fillFromResource($model, $modelResource);
        $model = $this->computeRequirements($modelResource, $model);

        return $model;
    }

    /**
     * Update an entity object from a Resource.
     * Only the fields that are not null in the resource are taken in account to edit the entity.
     * The id of an entity can never be modified (ignored if not null)
     *
     * @param ExerciseModelResource $modelResource
     * @param ExerciseModel $model
     *
     * @throws NoAuthorException
     * @return ExerciseModel
     */
    public function updateFromResource(
        $modelResource,
        $model
    )
    {
        parent::updateFromSharedResource($modelResource, $model, 'exercise_model_storage');
        $model = $this->computeRequirements($modelResource, $model);
	# debug romain
	#die(var_dump($model));

        if ($modelResource->getArchived() === true && $model->getArchived() === false) {
            $model->setArchived(true);
            $rn = $model->getResourceNode();
            $model->deleteResourceNode();
            $this->resourceManager->delete($rn);
        }

        if ($modelResource->getArchived() === false && $model->getArchived() === true) {
            $model->setArchived(false);
            //$this->createClarolineResourceNode($model->getOwner(), $model);
        }

        if (!is_null($modelResource->getTitle())) {
            /** @var ResourceNode $resourceNode */
            //$resourceNode = $model->getResourceNode();
            //if ($resourceNode !== null) {
            //    $resourceNode->setName($modelResource->getTitle());
            //}
        }
        //foreach($model->getDirectories() as $dir){
        //    echo $dir."<br>";
        //}
        $repo = $this->getEntityManager()
            ->getRepository('SimpleITClaireExerciseBundle:Directory')
        ;
        $dirs = array();
        $alls = array();
        $currents = array();
        foreach($model->getDirectories() as $dir){
            $currents[$dir->getName()] = "0";
        }
        //get all directories longName to match "Parentsname: childname"
        foreach($repo->findAll() as $d){
            $alls[$d->getLongName()] = $d->getId();
        }
        //from client
        foreach($modelResource->getDirectories() as $dir){
            if (!isset($alls[$dir])){
                die("Something wrong happened #96345");
            }
            $dirs[$dir] = $repo->find($alls[$dir]);
            if (!isset($currents[$dir])){
                $dirs[$dir]->addModel($model);
            }
        }
        //in server
        foreach($model->getDirectories() as $dir){
            if (!isset($dirs[$dir->getName()])){
                $dir->removeModel($model);
            }
        }
        $model->setDirectories($dirs);

        $this->em->flush();

        return $model;
    }

    /**
     * Get models that the user attempted
     *
     * @param int $userId
     *
     * @return array
     */
    public function getAllByUserWhoAttempted($userId)
    {
        try {
            ##return $this->entityRepository->findAllByUser($userId);
            return $this->entityRepository->findAllByUserWhoAttempted($userId);
        } catch (NoResultException $e) {
            return $this->getAll(null, $userId);
        }
    }

    /**
     * Get model filled with attempts
     *
     * @param int $userId
     * @param int $modelId
     *
     * @return ExerciseModel
     */
    public function getAllByUserWhoAttemptedByModel($userId, $modelId)
    {
        try {
            return $this->entityRepository->findAllByUserWhoAttemptedByModel($userId, $modelId);
        } catch (NonUniqueResultException $e) {
            /** @var ExerciseModel $model */
            $model = $this->get($modelId);
            $model->setExercises(new ArrayCollection());
            return $model;
        } catch (NoResultException $e) {
            /** @var ExerciseModel $model */
            $model = $this->get($modelId);
            $model->setExercises(new ArrayCollection());
            return $model;
        }
    }

    /**
     * Compute the requirements of the entity from the resource content (if empty,
     * requirements are not changed).
     * The model can be imported if owned by another user. Content of the resource is updated in
     * this case.
     *
     * @param ExerciseModelResource $modelResource
     * @param ExerciseModel $model
     * @param bool $import
     * @param int $ownerId
     * @param User $originalOwner
     *
     * @return ExerciseModel
     */
    private function computeRequirements(
        $modelResource,
        $model,
        $import = false,
        $ownerId = null,
        $originalOwner = null
    )
    {
        if ($modelResource->getContent() != null) {
            // required resources
            $modelResource = $this->computeRequiredResourcesFromResource(
                $modelResource,
                $import,
                $ownerId,
                $originalOwner
            );
            $reqResources = array();
            foreach ($modelResource->getRequiredExerciseResources() as $reqRes) {
                try {
                    $reqResources[] = $this->exerciseResourceService->get($reqRes);
                } catch (\Exception $e) {
                }
            }
            $model->setRequiredExerciseResources(new ArrayCollection($reqResources));

            // required knowledges
            $modelResource = $this->computeRequiredKnowledgesFromResource(
                $modelResource,
                $import,
                $ownerId
            );
            $reqKnowledges = array();
            foreach ($modelResource->getRequiredKnowledges() as $reqKnowledge) {
                try {
                    $reqKnowledges[] = $this->knowledgeService->get($reqKnowledge);
                } catch (\Exception $e) {
                }
            }
            $model->setRequiredKnowledges(new ArrayCollection($reqKnowledges));
        }

        // if public model, set public all the requirements
        if ($model->getPublic()) {
            /** @var ExerciseResource $reqRes */
            foreach ($model->getRequiredExerciseResources() as $reqRes) {
                if (!$reqRes->getPublic()) {
                    $pubResRes = new ResourceResource();
                    $pubResRes->setPublic(true);
                    $this->exerciseResourceService->updateFromResource($pubResRes, $reqRes);
                }
            }
            /** @var Knowledge $reqKno */
            foreach ($model->getRequiredKnowledges() as $reqKno) {
                if (!$reqKno->getPublic()) {
                    $pubKnoRes = new KnowledgeResource();
                    $pubKnoRes->setPublic(true);
                    $this->knowledgeService->updateFromResource($pubKnoRes, $reqKno);
                }
            }
        }

        return $model;
    }

    /**
     * Check if the content of an exercise model is sufficient to generate exercises.
     *
     * @param ExerciseModel $entity
     * @param string $type
     * @param int $parentId
     * @param $content
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InconsistentEntityException
     * @return boolean True if the model is complete
     */
    protected function checkEntityComplete($entity, $type, $parentId, $content)
    {
        $errorCode = null;

        if ($parentId === null) {
            switch ($type) {
                case CommonModel::MULTIPLE_CHOICE:
                    /** @var MultipleChoice $content */
                    $complete = $this->checkMCComplete($content, $errorCode);
                    break;
                case CommonModel::PAIR_ITEMS:
                    /** @var PairItems $content */
                    $complete = $this->checkPIComplete($content, $errorCode);
                    break;
                case CommonModel::GROUP_ITEMS:
                    /** @var GroupItems $content */
                    $complete = $this->checkGIComplete($content, $errorCode);
                    break;
                case CommonModel::ORDER_ITEMS:
                    /** @var OrderItems $content */
                    $complete = $this->checkOIComplete($content, $errorCode);
                    break;
                case CommonModel::OPEN_ENDED_QUESTION:
                    /** @var OpenEnded $content */
                    $complete = $this->checkOEQComplete($content, $errorCode);
                    break;
                default:
                    throw new InconsistentEntityException('Invalid type');
            }
        } else {
            if ($content !== null) {
                throw new InconsistentEntityException('A model must be a pointer OR have a content');
            }
            try {

                $parentModel = $this->get($parentId);
            } catch (NonExistingObjectException $neoe) {
                throw new InconsistentEntityException('The parent model cannot be found.');
            }

            $complete = $parentModel->getPublic();
            if (!$parentModel->getPublic()) {
                $errorCode = 101;
            }
        }

        $entity->setComplete($complete);
        $entity->setCompleteError($errorCode);
    }

    /**
     * Check if a multiple choice content is complete
     *
     * @param MultipleChoice $content
     * @param string $errorCode
     *
     * @return boolean
     */
    private function checkMCComplete(
        MultipleChoice $content,
        &$errorCode
    )
    {
        if (is_null($content->isShuffleQuestionsOrder())) {
            $errorCode = '201';

            return false;
        }
        $questionBlocks = $content->getQuestionBlocks();
        if (!count($questionBlocks) > 0) {
            $errorCode = '202';

            return false;
        }
        /** @var MCQuestionBlock $questionBlock */
        foreach ($questionBlocks as $questionBlock) {
            if (!($questionBlock->getMaxNumberOfPropositions() >= 0
                && $questionBlock->getMaxNumberOfRightPropositions() >= 0)
            ) {
                $errorCode = '301';

                return false;
            }

            if (!$this->checkBlockComplete(
                $questionBlock,
                array(CommonResource::MULTIPLE_CHOICE_QUESTION),
                $errorCode
            )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a pair items content is complete
     *
     * @param PairItems $content
     * @param string $errorCode
     *
     * @return bool
     */
    private function checkPIComplete(
        PairItems $content,
        &$errorCode
    )
    {
        $pairBlocks = $content->getPairBlocks();
        if (!count($pairBlocks) > 0) {
            $errorCode = '202';

            return false;
        }

        /** @var PairBlock $pairBlock */
        foreach ($pairBlocks as $pairBlock) {
            if ($pairBlock->getPairMetaKey() == null) {
                $errorCode = '302';

                return false;
            }

            if (!$this->checkBlockComplete(
                $pairBlock,
                array(
                    CommonResource::PICTURE,
                    CommonResource::TEXT
                ),
                $errorCode,
                $pairBlock->getPairMetaKey()
            )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a group items model is complete
     *
     * @param GroupItems $content
     * @param string $errorCode
     *
     * @return bool
     */
    private function checkGIComplete(
        GroupItems $content,
        &$errorCode
    )
    {
        if ($content->getDisplayGroupNames() != GroupItems::ASK
            && $content->getDisplayGroupNames() != GroupItems::HIDE
            && $content->getDisplayGroupNames() != GroupItems::SHOW
        ) {
            $errorCode = '203';

            return false;
        }

        $globalClassification = false;
        if ($content->getClassifConstr() != null) {
            if (!$this->checkClassifConstr($content->getClassifConstr(), $errorCode)) {
                return false;
            }
            $globalClassification = true;
        }

        $objectBlocks = $content->getObjectBlocks();
        if (!count($objectBlocks) > 0) {
            $errorCode = '202';

            return false;
        }

        /** @var GIObjectBlock $objectBlock */
        foreach ($objectBlocks as $objectBlock) {
            if (!$globalClassification &&
                (
                    $objectBlock->getClassifConstr() == null
                    || !$this->checkClassifConstr($objectBlock->getClassifConstr(), $errorCode)
                )
            ) {
                return false;
            }

            if (!$this->checkBlockComplete(
                $objectBlock,
                array(
                    CommonResource::TEXT,
                    CommonResource::PICTURE
                ),
                $errorCode
            )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if an order items model is complete
     *
     * @param OrderItems $content
     * @param string $errorCode
     *
     * @return bool
     */
    private function checkOIComplete(
        OrderItems $content,
        &$errorCode
    )
    {
        if ($content->isGiveFirst() === null || $content->isGiveLast() === null) {
            $errorCode = '204';

            return false;
        }

        $sequenceBlock = $content->getSequenceBlock();
        $objectBlocks = $content->getObjectBlocks();
        // both cannot be empty or filled
        if (empty($sequenceBlock) == empty($objectBlocks)) {
            $errorCode = '205';

            return false;
        }

        if ($sequenceBlock !== null) {
            if ($sequenceBlock->isKeepAll() === null) {
                $errorCode = '303';

                return false;
            }

            if (!$sequenceBlock->isKeepAll() &&
                ($sequenceBlock->isUseFirst() === null || $sequenceBlock->isUseLast() === null)
            ) {
                $errorCode = '304';

                return false;
            }

            if (!$this->checkBlockComplete(
                $sequenceBlock,
                array(CommonResource::SEQUENCE),
                $errorCode
            )
            ) {
                return false;
            }
        } else {
            if ($content->getOrder() != OrderItems::ASCENDENT
                && $content->getOrder() != OrderItems::DESCENDENT
            ) {
                $errorCode = '206';

                return false;
            }

            if (is_null($content->getShowValues())) {
                $errorCode = '207';

                return false;
            }

            /** @var OIObjectBlock $objectBlock */
            foreach ($objectBlocks as $objectBlock) {
                if ($objectBlock->getMetaKey() === null) {
                    $errorCode = '305';

                    return false;
                }

                if (
                !$this->checkBlockComplete(
                    $objectBlock,
                    array(
                        CommonResource::PICTURE,
                        CommonResource::TEXT
                    ),
                    $errorCode,
                    $objectBlock->getMetaKey()
                )
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if an open ended question model is complete
     *
     * @param OpenEnded $content
     * @param string $errorCode
     *
     * @return bool
     */
    private function checkOEQComplete(
        OpenEnded $content,
        &$errorCode
    )
    {
        if (is_null($content->isShuffleQuestionsOrder())) {
            $errorCode = '201';

            return false;
        }
        $questionBlocks = $content->getQuestionBlocks();
        if (!count($questionBlocks) > 0) {
            $errorCode = '202';

            return false;
        }

        /** @var OEQuestionBlock $questionBlock */
        foreach ($questionBlocks as $questionBlock) {
            if (!$this->checkBlockComplete(
                $questionBlock,
                array(CommonResource::OPEN_ENDED_QUESTION),
                $errorCode
            )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a resource block is complete
     *
     * @param ResourceBlock $block
     * @param array $resourceTypes
     * @param string $metaKey
     * @param string $errorCode
     *
     * @return boolean
     */
    private function checkBlockComplete(
        ResourceBlock $block,
        array $resourceTypes,
        &$errorCode,
        $metaKey = null
    )
    {
        if (!($block->getNumberOfOccurrences() > 0)) {
            $errorCode = '306';

            return false;
        }

        if (count($block->getResources()) == 0 && $block->getResourceConstraint() === null) {
            $errorCode = '307';

            return false;
        } elseif ($block->getIsList() && count($block->getResources()) == 0) {
            $errorCode = '308';

            return false;
        }

        /** @var ObjectId $resource */
        foreach ($block->getResources() as $resource) {
            if (!$this->checkObjectId($resource, $errorCode, $resourceTypes, $metaKey)) {
                return false;
            }
        }

        if (!$block->getIsList() &&
            (
                $block->getResourceConstraint() == null ||
                !$this->checkConstraintsComplete(
                    $block->getResourceConstraint(),
                    $errorCode,
                    $resourceTypes
                )
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if and object constraints object is complete
     *
     * @param ObjectConstraints $resourceConstraints
     * @param array $resourceTypes
     * @param string $errorCode
     *
     * @return boolean
     */
    private function checkConstraintsComplete(
        ObjectConstraints $resourceConstraints,
        &$errorCode,
        array $resourceTypes = array()
    )
    {
        if (!empty($resourceTypes) && !is_null($resourceConstraints->getType()) &&
            !in_array($resourceConstraints->getType(), $resourceTypes)
        ) {
            $errorCode = '401';

            return false;
        }
        if (count($resourceConstraints->getMetadataConstraints()) == 0) {
            $errorCode = '402';

            return false;
        }

        /** @var MetadataConstraint $mdc */
        foreach ($resourceConstraints->getMetadataConstraints() as $mdc) {
            if (!$this->checkMetadataConstraintComplete($mdc, $errorCode)) {
                return false;
            }
        }

        /** @var ObjectId $excluded */
        foreach ($resourceConstraints->getExcluded() as $excluded) {
            if (!$this->checkObjectId($excluded, $errorCode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if an obect Id is valid (and exists)
     *
     * @param ObjectId $objectId
     * @param array $resourceTypes
     * @param string $metaKey
     * @param string $errorCode
     *
     * @return bool
     */
    private function checkObjectId(
        ObjectId $objectId,
        &$errorCode,
        array $resourceTypes = array(),
        $metaKey = null
    )
    {
        if (is_null($objectId->getId())) {
            $errorCode = '501';

            return false;
        }
        try {
            $resource = $this->exerciseResourceService->get($objectId->getId());
        } catch (NonExistingObjectException $neoe) {
            $errorCode = '502';

            return false;
        }

        if (!empty($resourceTypes) && !in_array($resource->getType(), $resourceTypes)) {
            $errorCode = '503';

            return false;
        }

        if ($metaKey !== null) {
            $found = false;
            /** @var Metadata $md */
            foreach ($resource->getMetadata() as $md) {
                if ($md->getKey() === $metaKey) {
                    $found = true;
                }
            }
            if (!$found) {
                $errorCode = '504';

                return false;
            }
        }

        return true;
    }

    /**
     * Check if a metadata constraint is complete
     *
     * @param MetadataConstraint $mdc
     * @param string $errorCode
     *
     * @return bool
     */
    private function checkMetadataConstraintComplete(
        MetadataConstraint $mdc,
        &$errorCode
    )
    {
        if ($mdc->getComparator() == null) {
            $errorCode = '601';

            return false;
        }
        if ($mdc->getKey() == null) {
            $errorCode = '602';

            return false;
        }

        return true;
    }

    /**
     * Check if a classification constraint is complete
     *
     * @param ClassificationConstraints $classifConstr
     * @param string $errorCode
     *
     * @return bool
     */
    private function checkClassifConstr(
        ClassificationConstraints $classifConstr,
        &$errorCode
    )
    {
        if ($classifConstr->getOther() != ClassificationConstraints::MISC
            && $classifConstr->getOther() != ClassificationConstraints::REJECT
        ) {
            $errorCode = '701';

            return false;
        }

        /** @var Group $group */
        foreach ($classifConstr->getGroups() as $group) {
            $name = $group->getName();
            if (empty($name)) {
                $errorCode = '702';

                return false;
            }

            if (count($group->getMDConstraints()) == 0 && !$group->getBuildGroups()) {
                $errorCode = '703';

                return false;
            }

            /** @var MetadataConstraint $mdc */
            foreach ($group->getMDConstraints() as $mdc) {
                if (!$this->checkMetadataConstraintComplete($mdc, $errorCode)) {
                    return false;
                }
            }
        }

        return true;
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
        if (($type === CommonModel::MULTIPLE_CHOICE &&
                get_class($content) !== ExerciseModelResource::MULTIPLE_CHOICE_MODEL_CLASS)
            || ($type === CommonModel::GROUP_ITEMS
                && get_class($content) !== ExerciseModelResource::GROUP_ITEMS_MODEL_CLASS)
            || ($type === CommonModel::ORDER_ITEMS &&
                get_class($content) !== ExerciseModelResource::ORDER_ITEMS_MODEL_CLASS)
            || ($type === CommonModel::PAIR_ITEMS &&
                get_class($content) !== ExerciseModelResource::PAIR_ITEMS_MODEL_CLASS)
            || ($type === CommonModel::OPEN_ENDED_QUESTION &&
                get_class($content) !== ExerciseModelResource::OPEN_ENDED_QUESTION_CLASS)
        ) {
            throw new InvalidTypeException('Content does not match exercise model type');
        }
    }

    /**
     * Computes the required resources according to the content of the resource resource and
     * write it in the corresponding field of the output resource (content must not be empty).
     * The resource can be imported if owned by another user.
     *
     * @param ExerciseModelResource $modelResource
     * @param bool $import
     * @param int $ownerId
     * @param User $originalOwner
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException
     * @return ExerciseModelResource
     */
    private function computeRequiredResourcesFromResource(
        $modelResource,
        $import = false,
        $ownerId = null,
        $originalOwner = null
    )
    {
        $reqRes = array();

        /** @var ModelDocument $document */
        foreach ($modelResource->getContent()->getDocuments() as $document) {
            if ($import) {
                $requiredId = $this->exerciseResourceService->importOrLink(
                    $ownerId,
                    $document->getId()
                );
                $document->setId($requiredId);
            } else {
                $requiredId = $document->getId();
            }
            $reqRes[] = $requiredId;
        }

        $content = $modelResource->getContent();
        switch (get_class($modelResource->getContent())) {
            case ExerciseModelResource::ORDER_ITEMS_MODEL_CLASS:
                /** @var OrderItems $content */
                $reqRes = array_merge(
                    $reqRes,
                    $this->computeRequiredResourcesFromModelBlocks
                        (
                            $content->getObjectBlocks(),
                            $import,
                            $ownerId,
                            $originalOwner
                        )
                );
                $reqRes = array_merge(
                    $reqRes,
                    $this->computeRequiredResourcesFromModelBlock
                        (
                            $content->getSequenceBlock(),
                            $import,
                            $ownerId,
                            $originalOwner
                        )
                );
                break;
            case ExerciseModelResource::PAIR_ITEMS_MODEL_CLASS:
                /** @var PairItems $content */
                $reqRes = array_merge(
                    $reqRes,
                    $this->computeRequiredResourcesFromModelBlocks
                        (
                            $content->getPairBlocks(),
                            $import,
                            $ownerId,
                            $originalOwner
                        )
                );
                break;
            case ExerciseModelResource::GROUP_ITEMS_MODEL_CLASS:
                /** @var GroupItems $content */
                $reqRes = array_merge(
                    $reqRes,
                    $this->computeRequiredResourcesFromModelBlocks
                        (
                            $content->getObjectBlocks(),
                            $import,
                            $ownerId,
                            $originalOwner
                        )
                );
                break;
            case ExerciseModelResource::MULTIPLE_CHOICE_MODEL_CLASS:
            case ExerciseModelResource::OPEN_ENDED_QUESTION_CLASS:
                /** @var MultipleChoice|OpenEnded $content */
                $reqRes = array_merge(
                    $reqRes,
                    $this->computeRequiredResourcesFromModelBlocks
                        (
                            $content->getQuestionBlocks(),
                            $import,
                            $ownerId,
                            $originalOwner
                        )
                );
                break;
            default:
                throw new InvalidTypeException('Unknown type:' . $modelResource->getType());
        }

        $modelResource->setRequiredExerciseResources(array_unique($reqRes));

        return $modelResource;
    }

    /**
     * List all the required resources found in a list of blocks
     *
     * @param array $blocks
     * @param bool $import
     * @param int $ownerId
     * @param User $originalOwner
     *
     * @return array
     */
    private function computeRequiredResourcesFromModelBlocks(
        $blocks,
        $import = false,
        $ownerId = null,
        $originalOwner = null
    )
    {
        $reqRes = array();

        /** @var ResourceBlock $block */
        foreach ($blocks as &$block) {
            $reqRes = array_merge(
                $reqRes,
                $this->computeRequiredResourcesFromModelBlock(
                    $block,
                    $import,
                    $ownerId,
                    $originalOwner
                )
            );
        }

        return $reqRes;
    }

    /**
     * List all the required resource found in a block
     *
     * @param ResourceBlock $block
     * @param bool $import
     * @param int $ownerId
     * @param User $originalOwner
     *
     * @return array
     */
    private function computeRequiredResourcesFromModelBlock(
        $block,
        $import = false,
        $ownerId = null,
        $originalOwner = null
    )
    {
        $reqRes = array();

        /** @var ObjectId $resource */
        if (!empty($block)) {
            foreach ($block->getResources() as $resource) {
                if ($import) {
                    $requiredId = $this->exerciseResourceService->importOrLink(
                        $ownerId,
                        $resource->getId()
                    );
                    $resource->setId($requiredId);
                } else {
                    $requiredId = $resource->getId();
                }
                $reqRes[] = $requiredId;
            }

            // if import, if constraints, import all the public resources matching the constraints
            // but they must not be considered as required resources
            if ($import && $block->isList() === false && $originalOwner !== null) {
                // get all the public matching resources
                $resList = $this->exerciseResourceService->getResourcesFromConstraintsByOwner($block->getResourceConstraint(), $originalOwner, true);

                // for each of them, import it
                foreach ($resList as $res)
                {
                    $this->exerciseResourceService->importOrLink(
                        $ownerId,
                        $res->getId()
                    );
                }

            }
        }

        return $reqRes;
    }

    /**
     * Computes the required knowledges according to the content of the resource resource and
     * write it in the corresponding field of the output resource (content must not be empty)
     *The knowledge can be imported if owned by another user.
     *
     * @param ExerciseModelResource $modelResource
     * @param bool $import
     * @param int $ownerId
     *
     * @throws InvalidTypeException
     * @return ExerciseModelResource
     */
    private function computeRequiredKnowledgesFromResource(
        $modelResource,
        $import = false,
        $ownerId = null
    )
    {
        $reqKno = array();

        if ($modelResource->getContent()->getFormulas() !== null) {
            /** @var LocalFormula $formula */
            foreach ($modelResource->getContent()->getFormulas() as $formula) {
                if ($import) {
                    $requiredId = $this->knowledgeService->importOrLink(
                        $ownerId,
                        $formula->getFormulaId()
                    );
                    $formula->setFormulaId($requiredId);
                } else {
                    $requiredId = $formula->getFormulaId();
                }

                $reqKno[] = $requiredId;
            }
        }

        $modelResource->setRequiredKnowledges(array_unique($reqKno));

        return $modelResource;
    }

    /**
     * Subscribe to an entity: the new entity is a pointer to the parent entity. It has no
     * content and no metadata because these elements rely on the parent.
     *
     * @param int $ownerId The id of the owner who wants to get the new pointer
     * @param int $parentEntityId The id of the parent entity
     *
     * @return ExerciseModel
     */
    public function subscribe($ownerId, $parentEntityId)
    {
        /** @var ExerciseModel $model */
        $model = parent::subscribe($ownerId, $parentEntityId);
        $model->setRequiredExerciseResources(new ArrayCollection());
        $model->setRequiredKnowledges(new ArrayCollection());

        return $model;
    }

    /**
     * Import an entity. Additionnal work, specific to entity type
     *
     * @param int $ownerId
     * @param ExerciseModel $entity The duplicata
     * @param User $originalOwner
     *
     * @return ExerciseModel
     */
    protected function importDetail($ownerId, $entity, $originalOwner = null)
    {
        $resource = ExerciseModelResourceFactory::create($entity);

        // requirement
        $entity = $this->computeRequirements($resource, $entity, true, $ownerId, $originalOwner);

        // updated content
        $context = SerializationContext::create();
        $context->setGroups(array('exercise_model_storage', 'Default'));
        $entity->setContent(
            $this->serializer->jmsSerialize($resource->getContent(), 'json', $context)
        );

        $this->em->flush();

        return $entity;
    }

    /**
     * @return ExerciseModel
     */
    protected function newEntity()
    {
        return new ExerciseModel();
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
     * @param ExerciseModel $entity
     */
    protected function clearEntityDetail($entity)
    {
        $entity->setRequiredKnowledges(new ArrayCollection());
        $entity->setRequiredExerciseResources(new ArrayCollection());
    }

    /**
     * Import the public resources that can be used by constraints in a public exercise model.
     *
     * @param $ownerId
     * @param $originalId
     *
     * @return array Array of SharedEntity that are new imported entities
     */
    public function importUsedResources($ownerId, $originalId)
    {
        /** @var ExerciseModel $entity */
        $entity = $this->get($originalId);
        $resource = ExerciseModelResourceFactory::create($entity);
        $owner = $this->userService->get($ownerId);

        $usedResources = $this->getUsedResourcesFromResource($resource, $owner);

        $newRes = array();
        /** @var ExerciseResource $usedResource */
        foreach ($usedResources as $usedResource) {
            $newRes = $this->exerciseResourceService->importByEntity($ownerId, $usedResource);
        }

        return $newRes;
    }

    /**
     * Computes the used resources according to the content of the resource resource.
     *
     * @param ExerciseModelResource $modelResource
     * @param User $owner
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException
     * @return array
     */
    private function getUsedResourcesFromResource(
        $modelResource,
        $owner
    )
    {
        $usedRes = array();

        $content = $modelResource->getContent();
        switch (get_class($modelResource->getContent())) {
            case ExerciseModelResource::ORDER_ITEMS_MODEL_CLASS:
                /** @var OrderItems $content */
                $usedRes = array_merge(
                    $usedRes,
                    $this->getUsedResourcesFromModelBlocks
                        (
                            $content->getObjectBlocks(),
                            $owner
                        )
                );
                $usedRes = array_merge(
                    $usedRes,
                    $this->getUsedResourcesFromModelBlock
                        (
                            $content->getSequenceBlock(),
                            $owner
                        )
                );
                break;
            case ExerciseModelResource::PAIR_ITEMS_MODEL_CLASS:
                /** @var PairItems $content */
                $usedRes = array_merge(
                    $usedRes,
                    $this->getUsedResourcesFromModelBlocks
                        (
                            $content->getPairBlocks(),
                            $owner
                        )
                );
                break;
            case ExerciseModelResource::GROUP_ITEMS_MODEL_CLASS:
                /** @var GroupItems $content */
                $usedRes = array_merge(
                    $usedRes,
                    $this->getUsedResourcesFromModelBlocks
                        (
                            $content->getObjectBlocks(),
                            $owner
                        )
                );
                break;
            case ExerciseModelResource::MULTIPLE_CHOICE_MODEL_CLASS:
            case ExerciseModelResource::OPEN_ENDED_QUESTION_CLASS:
                /** @var MultipleChoice|OpenEnded $content */
                $usedRes = array_merge(
                    $usedRes,
                    $this->getUsedResourcesFromModelBlocks
                        (
                            $content->getQuestionBlocks(),
                            $owner
                        )
                );
                break;
            default:
                throw new InvalidTypeException('Unknown type:' . $modelResource->getType());
        }

        return array_unique($usedRes);
    }

    /**
     * List all the required resources found in a list of blocks
     *
     * @param array $blocks
     * @param User $owner
     *
     * @return array
     */
    private function getUsedResourcesFromModelBlocks($blocks, $owner)
    {
        $usedRes = array();

        /** @var ResourceBlock $block */
        foreach ($blocks as &$block) {
            $usedRes = array_merge(
                $usedRes,
                $this->getUsedResourcesFromModelBlock($block, $owner)
            );
        }

        return $usedRes;
    }

    /**
     * Import all the used resource found in a block
     *
     * @param ResourceBlock $block
     * @param User $owner
     *
     * @return array
     */
    private function getUsedResourcesFromModelBlock($block, $owner)
    {
        return $this->exerciseResourceService->getResourcesFromConstraintsByOwner
            (
                $block->getResourceConstraint(),
                $owner
            );
    }

    /**
     * Set public a model and all its requirements
     *
     * @param ExerciseModel $entity
     */
    public function makePublic($entity)
    {
        $entity->setPublic(true);

        foreach ($entity->getRequiredExerciseResources() as $resource) {
            $this->exerciseResourceService->makePublic($resource);
        }

        foreach ($entity->getRequiredKnowledges() as $knowledge) {
            $this->knowledgeService->makePublic($knowledge);
        }
    }

    /**
     * Create the claroline resource node associated to the model
     *
     * @param User $user
     * @param ExerciseModel $model
     */
    public function createClarolineResourceNode($user, $model)
    {
        $workspace = $user->getPersonalWorkspace();
    }

    /**
     * Checks if an entity can be removed (is required)
     *
     * @param ExerciseModel $entity
     *
     * @return boolean
     */
    public function canBeRemoved($entity)
    {
        return true;
    }

    /**
     * Duplicate an entity. Additionnal work, specific to entity type
     *
     * @param ExerciseModel $entity The duplicata
     * @param ExerciseModel $original
     */
    protected function duplicateDetail($entity, $original)
    {
        $entity->setRequiredKnowledges($original->getRequiredKnowledges());
        $entity->setRequiredExerciseResources($original->getRequiredKnowledges());
    }
}
