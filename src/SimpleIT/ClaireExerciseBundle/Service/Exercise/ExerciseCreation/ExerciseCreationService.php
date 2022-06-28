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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseCreation;

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use JMS\Serializer\SerializationContext;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\StoredExercise;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Entity\ItemFactory;
use SimpleIT\ClaireExerciseBundle\Entity\StoredExerciseFactory;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use SimpleIT\ClaireExerciseBundle\Model\ExerciseObject\ExerciseTextFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\LocalFormula;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\ResourceBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\KnowledgeResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\KnowledgeResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ModelDocument;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\DomainKnowledge\FormulaServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\DomainKnowledge\KnowledgeServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseResource\ExerciseResourceServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\Serializer\SerializerInterface;

/**
 * Abstract class for the services which manages the specific exercise
 * generation
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class ExerciseCreationService implements ExerciseCreationServiceInterface
{
    /**
     * @var ExerciseResourceServiceInterface
     */
    protected $exerciseResourceService;

    /**
     * @var FormulaServiceInterface
     */
    protected $formulaService;

    /**
     * @var KnowledgeServiceInterface
     */
    protected $knowledgeService;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

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
     * Set exerciseResourceService
     *
     * @param ExerciseResourceServiceInterface $exerciseResourceService
     */
    public function setExerciseResourceService($exerciseResourceService)
    {
        $this->exerciseResourceService = $exerciseResourceService;
    }

    /**
     * Set formulaService
     *
     * @param FormulaServiceInterface $formulaService
     */
    public function setFormulaService($formulaService)
    {
        $this->formulaService = $formulaService;
    }

    /**
     * Set ownerKnowledgeService
     *
     * @param KnowledgeServiceInterface $ownerKnowledgeService
     */
    public function setKnowledgeService($ownerKnowledgeService)
    {
        $this->knowledgeService = $ownerKnowledgeService;
    }

    /**
     * Add the documents from model to the exercise
     *
     * @param CommonModel    $model
     * @param CommonExercise $exercise
     * @param AskerUser           $owner
     *
     * @throws \LogicException
     */
    protected function addDocuments(CommonModel $model, CommonExercise &$exercise, AskerUser $owner)
    {
        $modDocs = $model->getDocuments();
        foreach ($modDocs as $modDoc) {
            /** @var ModelDocument $modDoc */
            if (is_null($modDoc->getId())) {
                throw new \LogicException("Invalid document resource id.");
            }

            $exercise->addDocument(
                $this->exerciseResourceService
                    ->getExerciseObject($modDoc, $owner)
            );
        }
    }

    /**
     * Compute the values of the formula: variable instantiation and equation resolution
     *
     * @param array $localFormulas
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException
     * @return array
     */
    protected function computeFormulaVariableValues($localFormulas)
    {
        if (empty($localFormulas)) {
            return array();
        }

        $returnVariables = array();

        /** @var LocalFormula $localFormula */
        foreach ($localFormulas as $localFormula) {
            if (!is_null($localFormula->getFormulaId())) {
                $formula = KnowledgeResourceFactory::create(
                    $this->knowledgeService->get($localFormula->getFormulaId())
                )->getContent();

                if (get_class($formula) != KnowledgeResource::FORMULA_CLASS) {
                    throw new ApiBadRequestException(
                        'The specified formula resource is not a formula'
                    );
                }

                /** @var Formula $formula */
                $textFormula = $formula->getEquation();
                $variables = $formula->getVariables();
                $unknown = $formula->getUnknown();
            } elseif (!is_null($localFormula->getEquation())) {
                $textFormula = $localFormula->getEquation();
                $unknown = $localFormula->getUnknown();
                $variables = array();
            } else {
                throw new ApiBadRequestException(
                    'The equation of the formula cannot be found'
                );
            }

            $newVariables = array();
            /** @var Formula\Variable $newVar */
            foreach ($localFormula->getVariables() as $newVar) {
                $newVariables[] = $newVar->getName();
            }

            /** @var Formula\Variable $variable */
            foreach ($variables as $key => $variable) {
                if (array_search($variable->getName(), $newVariables) !== false) {
                    unset($variables[$key]);
                }
            }

            $variables = array_merge($variables, $localFormula->getVariables());

            if ($localFormula->getUnknown() != null) {
                $unknown = $localFormula->getUnknown();
            }

            $returnVariables = array_merge(
                $returnVariables,
                $this->formulaService->prefixVariableNames(
                    $this->formulaService->resolveFormulaResource(
                        $textFormula,
                        $variables,
                        $unknown
                    ),
                    $localFormula->getName()
                )
            );
        }

        return $returnVariables;
    }

    /**
     * Inject variable values in a string
     *
     * @param string $string
     * @param array  $variables
     *
     * @return string
     */
    protected function parseStringWithVariables($string, $variables)
    {
        if (!empty($variables)) {
            preg_match_all('#\$[a-z|A-Z|0-9]+:[a-z|A-Z|0-9]+#', $string, $foundVarNames);
            foreach ($foundVarNames[0] as $varName) {
                $shortName = substr($varName, 1);
                if (isset($variables[$shortName])) {
                    $string = str_replace($varName, $variables[$shortName], $string);
                }
            }
        }

        return $string;
    }

    /**
     * Inject variable values in an array of strings
     *
     * @param $array
     * @param $variables
     *
     * @return mixed
     */
    protected function parseArrayWithVariables($array, $variables)
    {
        $result = array();
        foreach ($array as $string) {
            $result[] = $this->parseStringWithVariables($string, $variables);
        }

        return $result;
    }

    /**
     * Convert an array of ExerciseObjects into an array of ExerciseText
     * which contains the value of the field of metadata pointed by a key.
     * The metadata are copied in the new objects. The number of objects in the
     * output list can be reduced because some objects may not have the
     * metadata
     *
     * @param array  $objects An array of ExerciseObject
     * @param string $key     The key of the metadata to use to replace the object
     *
     * @return array An array of ExerciseText
     */
    protected function objectsToMetaStrings($objects, $key)
    {
        $stringObjects = array();

        foreach ($objects as $obj) {
            $textObject = $this->objectToMetaString($obj, $key);
            if (!is_null($textObject)) {
                $stringObjects[] = $textObject;
            }
        }

        return $stringObjects;
    }

    /**
     * Convert an ExerciseObject into an ExerciseText which contains the value
     * of the field of metadata pointed by a key. The metadata are copied in the
     * new object.
     *
     * @param ExerciseObject $obj The input ExerciseObject
     * @param string         $key The key of the metadata
     *
     * @return ExerciseObject The text object or null if the metadata was not found
     */
    protected function objectToMetaString(ExerciseObject $obj, $key)
    {
        $md = $obj->getMetadata();
        if (array_key_exists($key, $md)) {
            $textObj = ExerciseTextFactory::createFromText($md[$key]);
            $textObj->setMetadata($md);

            return $textObj;
        } else {
            return null;
        }
    }

    /**
     * Convert an exercise and its items into one StoredExercise entity and
     * Item entities. Schemas of the exercise and of the items are required.
     *
     * @param CommonExercise $exercise
     * @param ExerciseModel  $exerciseModel
     * @param string         $typeOfItem
     * @param null           $items
     *
     * @return StoredExercise
     */
    protected function toStoredExercise(
        CommonExercise $exercise,
        ExerciseModel $exerciseModel,
        $typeOfItem,
        $items = null
    )
    {
        $context = SerializationContext::create();
        $context->setGroups(array("exercise_storage", 'Default'));
        $exContent = $this->serializer->jmsSerialize(
            $exercise,
            'json',
            $context
        );
        $exerciseEntity = StoredExerciseFactory::create($exContent, $exerciseModel);

        // transform and add the items
        foreach ($items as $item) {
            $itemEnt = $this->toItemEntity($item, $typeOfItem);
            $itemEnt->setStoredExercise($exerciseEntity);
            $exerciseEntity->addItem($itemEnt);
        }

        return $exerciseEntity;
    }

    /**
     * Convert an item into an entity object
     *
     * @param $item
     * @param $typeOfItem
     *
     * @return Item
     */
    protected function toItemEntity($item, $typeOfItem)
    {
        $context = SerializationContext::create();
        $context->setGroups(array('item_storage', 'Default'));

        $itemContent = $this->serializer->jmsSerialize(
            $item,
            'json',
            $context
        );

        return ItemFactory::create($itemContent, $typeOfItem);
    }

    /**
     * Convert an item into an entity object
     *
     * @param $item
     * @param $typeOfItem
     *
     * @return Item
     */
    protected function toCorrectedItemEntity($item, $typeOfItem)
    {
        $context = SerializationContext::create();
        $context->setGroups(array('corrected', 'Default'));

        $itemContent = $this->serializer->jmsSerialize(
            $item,
            'json',
            $context
        );

        return ItemFactory::create($itemContent, $typeOfItem);
    }

    /**
     * Select random resources from a block
     *
     * @param ResourceBlock $resourceBlock
     * @param int           $numberOfOccurrences
     * @param array         $blockResources
     * @param AskerUser          $owner
     * @param array         $resourceToExclude
     */
    public function getObjectsFromList(
        ResourceBlock $resourceBlock,
        &$numberOfOccurrences,
        array &$blockResources,
        AskerUser $owner,
        $resourceToExclude = null
    )
    {
        $existingResourceIds = $resourceBlock->getResources();
        $existingResource = [];
        
        //get the resources in form of ExerciseObject
        foreach ($existingResourceIds as $id) {
            $existingResource[] = $this->exerciseResourceService->getExerciseObject($id, $owner);
        }

        //exclude existing resources
        foreach ($resourceToExclude as $obj){
            if (($key = array_search($obj, $existingResource)) !== false) {
                unset($existingResource[$key]);
            }
        }

        while ($numberOfOccurrences > 0 && count($existingResource) > 0) {
            // select a random object
            $resIndex = array_rand($existingResource);
            $exObj = $existingResource[$resIndex];

            // remove the object from the list (cannot be added anymore)
            unset($existingResource[$resIndex]);

            $blockResources[] = $exObj;
            $numberOfOccurrences--;
        }
    }
}
