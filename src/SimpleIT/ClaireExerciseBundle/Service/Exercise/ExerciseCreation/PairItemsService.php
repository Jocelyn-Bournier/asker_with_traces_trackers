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
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Answer;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidAnswerException;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException;
use SimpleIT\ClaireExerciseBundle\Model\ExerciseObject\ExerciseTextFactory;
use SimpleIT\ClaireExerciseBundle\Model\ModelObject\ObjectIdFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems\Exercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems\Item as ResItem;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\Model;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\PairBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExercisePictureObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseTextObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ResourceResource;

/**
 * Service which manages Pair Items Exercises.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class PairItemsService extends ExerciseCreationService
{
    /**
     * @inheritdoc
     */
    public function generateExerciseFromExerciseModel(
        ExerciseModel $exerciseModel,
        CommonModel $commonModel,
        AskerUser $owner
    )
    {
        /** @var Model $commonModel */
        // Generation of the exercise with the model
        $exercise = $this->generatePIExercise($commonModel, $owner);

        // Transformation of the exercise into entities (StoredExercise and Items)
        return $this->toStoredExercise(
            $exercise,
            $exerciseModel,
            "pair-items",
            array($exercise->getItem())
        );
    }

    /**
     * Correct the pair items. Modify the solution to keep only one of the
     * possible solutions, which must be the closest to the learner's answer.
     *
     * @param Item   $entityItem
     * @param Answer $answer
     *
     * @return ItemResource
     */
    public function correct(Item $entityItem, Answer $answer)
    {
        $itemResource = ItemResourceFactory::create($entityItem);
        /** @var ResItem $item */
        $item = $itemResource->getContent();

        $la = AnswerResourceFactory::create($answer);
        $learnerAnswers = $la->getContent();

        $item->setAnswers($learnerAnswers);

        // check the answers
        $sol = $item->getSolutions();
        foreach ($learnerAnswers as $key => $ans) {
            // case of the right answers
            if (array_search($ans, $sol[$key]) !== false) {
                // keep the solution
                $sol[$key] = $ans;

                // and remove it in the other solutions
                $this->removeFromArray($sol, $ans);
            }
        }

        // new loop for the wrong answers
        foreach ($sol as $key => $s) {
            // if the solution has not been chosen
            if (is_array($s)) {
                $keys = array_keys($s);
                $val = $s[$keys[0]];
                $sol[$key] = $val;

                // and remove it in the other solutions
                $this->removeFromArray($sol, $val);
            }

        }

        $item->setSolutions($sol);

        $this->mark($item);

        $itemResource->setContent($item);

        return $itemResource;
    }

    /**
     * Compute and add the mark to the item according to the answer and the solution
     *
     * @param ResItem $item
     */
    public function mark(ResItem &$item)
    {
        $solutions = $item->getSolutions();

        $mark = 100;
        $numberOfPairs = count($item->getFixParts());
        foreach ($item->getAnswers() as $key => $answer) {
            if ($solutions[$key] != $answer) {
                $mark -= 100 / $numberOfPairs;
            }
        }

        if ($mark < 0) {
            $mark = 0;
        }

        $item->setMark($mark);
    }

    /**
     * Generate a pair items exercise from a model
     *
     * @param Model $model the model
     * @param AskerUser  $owner
     *
     * @throws InvalidTypeException
     * @return Exercise The exercise
     */
    private function generatePIExercise(Model $model, AskerUser $owner)
    {
        // Wording and documents
        $exercise = new Exercise($model->getWording());

        // Documents
        $this->addDocuments($model, $exercise, $owner);

        // Item
        $item = new ResItem();
        $exercise->setItem($item);

        // array of ExercisePair to collect all the pairs to add in form of ModelPair
        $fixPartsToAdd = array();
        $mobilePartsToAdd = array();

        // for each block of pairs, add the pairs to the list of "pairs to add"
        foreach ($model->getPairBlocks() as $block) {
            $this->addPairsFromBlock($block, $fixPartsToAdd, $mobilePartsToAdd, $owner);
        }

        /*
         *  Add the pairs to the exercise and generate the solution
         */
        $item->addPairs($fixPartsToAdd, $mobilePartsToAdd);

        /*
         * Look for similar objects and allow all the possible answers
         */
        $revMP = array();
        /** @var ExerciseObject $mobPart */
        foreach ($item->getMobileParts() as $key => $mobPart) {
            if ($mobPart->getType() === CommonResource::TEXT) {
                /** @var ExerciseTextObject $mobPart */
                $value = $mobPart->getText();
            } elseif ($mobPart->getType() === CommonResource::PICTURE) {
                /** @var ExercisePictureObject $mobPart */
                $value = $mobPart->getSource();
            } else {
                throw new InvalidTypeException('Invalid exercise object type:' . $item->getMobileParts(
                ));
            }
            $revMP[$value][] = $key;
        }

        $solutions = array();
        foreach ($item->getSolutions() as $key => $sol) {
            /** @var ExerciseObject $mp */
            $mp = $item->getMobileParts()[$sol[0]];
            if ($mp->getType() === CommonResource::TEXT) {
                /** @var ExerciseTextObject $mp */
                $value = $mp->getText();
            } elseif ($mp->getType() === CommonResource::PICTURE) {
                /** @var ExercisePictureObject $mp */
                $value = $mp->getSource();
            } else {
                throw new InvalidTypeException('Invalid exercise object type:' . $item->getMobileParts(
                ));
            }

            $solutions[$key] = $revMP[$value];
        }
        $item->setSolutions($solutions);

        // shuffle the order of the pairs
        $item->shufflePairs();

        return $exercise;
    }

    /**
     * Complete a ExercisePair list with the pairs from a PairBlock
     *
     * @param PairBlock $pb
     * @param array     $fixPartsToAdd
     * @param array     $mobilePartsToAdd
     * @param AskerUser      $owner
     *
     * @throws \LogicException
     */
    private function addPairsFromBlock(
        PairBlock $pb,
        array &$fixPartsToAdd,
        array &$mobilePartsToAdd,
        AskerUser $owner
    )
    {
        // Get the objects from the block and the associated metadata
        // Array of ExerciseObject
        $blockFixObjects = $this->exerciseObjectsFromBlock($pb, $owner);

        foreach ($blockFixObjects as $fixObj) {
            /** @var ExerciseObject $fixObj */
            // get the value of the pair metadata
            $metaValue = $fixObj->getMetadataByKey($pb->getPairMetaKey());

            $substr = substr($metaValue, 0, 2);
            $objectId = substr($metaValue, 2);
            try {
                // Depending on the type of association (begins with '__' is resource, else text)
                if ($substr === ResourceResource::METADATA_IS_RESOURCE_PREFIX && is_numeric(
                        $objectId
                    )
                ) {
                    // create an ObjectId with the meta value
                    $objId = ObjectIdFactory::createFromResourceId($objectId);

                    // retrieve the resource in form of ExerciseObject
                    $mobileObj = $this->exerciseResourceService
                        ->getExerciseObject($objId, $owner);
                } else {
                    // create a text object with this value
                    $mobileObj = ExerciseTextFactory::createFromText($metaValue);

                    // change into text if required
                    $rmtd = $pb->getMobileMetaToDisplay();
                    if (!is_null($rmtd)) {
                        $mobileObj = $this->objectToMetaString($mobileObj, $rmtd);
                    }
                }

                // add to the pairs to add if not null
                if (!is_null($mobileObj)) {
                    $mobilePartsToAdd[] = $mobileObj;
                    $fixPartsToAdd[] = $fixObj;
                }
            } catch (\Exception $exc) {
                throw new \LogicException("Invalid meta-key to link the mobile part: "
                . $pb->getPairMetaKey());
            }
        }
    }

    /**
     * Retrieve objects from a PairBlock
     *
     * @param PairBlock $pb
     * @param AskerUser      $owner
     *
     * @return array An array of ExerciseObject
     */
    private function exerciseObjectsFromBlock(PairBlock $pb, AskerUser $owner)
    {
        $blockObjects = array();
        $numOfPairs = $pb->getNumberOfOccurrences();

        /*
         * if the block is a list
         */
        if ($pb->isList()) {
            $this->getObjectsFromList($pb, $numOfPairs, $blockObjects, $owner);
        } /*
         * if the block is object constraints
         */
        else {
            // get the resource constraint
            $oc = $pb->getResourceConstraint();

            // add the existence of the link meta key
            $oc->addExists($pb->getPairMetaKey());

            // add the existence of the fix meta to display, if there is one
            $lmtd = $pb->getFixMetaToDisplay();
            if (!is_null($lmtd)) {
                $oc->addExists($lmtd);
            }

            $blockObjects = $this->exerciseResourceService
                ->getExerciseObjectsFromConstraints(
                    $oc,
                    $numOfPairs,
                    $owner
                );
        }

        // If the value of a metadata field must be displayed instead of the object
        if (!is_null($pb->getFixMetaToDisplay())) {
            $blockObjects = $this->objectsToMetaStrings(
                $blockObjects,
                $pb->getFixMetaToDisplay()
            );
        }

        return $blockObjects;
    }

    /**
     * Remove a value from a recursive array
     *
     * @param array $array
     * @param mixed $val
     */
    private function removeFromArray(&$array, $val)
    {
        foreach ($array as $k1 => $sList) {
            if (is_array($sList)) {
                foreach ($sList as $k2 => $s) {
                    if ($s == $val) {
                        unset ($array[$k1][$k2]);
                    }
                }
            }
        }
    }

    /**
     * Validate the answer to an item
     *
     * @param Item  $itemEntity
     * @param array $answer
     *
     * @throws InvalidAnswerException
     */
    public function validateAnswer(Item $itemEntity, array $answer)
    {
        /** @var ResItem $item */
        $item = ItemResourceFactory::create($itemEntity)->getContent();

        $nbPairs = count($item->getFixParts());

        if (count($answer) !== $nbPairs) {
            throw new InvalidAnswerException('Invalid number of objects in the answer');
        }

        foreach ($answer as $ans) {
            if (!array_key_exists($ans, $item->getMobileParts())) {
                throw new InvalidAnswerException('Invalid rank for object');
            }
        }
    }

    /**
     * Return an item without solution
     *
     * @param ItemResource $itemResource
     *
     * @return ItemResource
     */
    public function noSolutionItem($itemResource)
    {
        /** @var \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems\Item $content */
        $content = $itemResource->getContent();
        $content->setSolutions(null);

        return $itemResource;
    }
}
