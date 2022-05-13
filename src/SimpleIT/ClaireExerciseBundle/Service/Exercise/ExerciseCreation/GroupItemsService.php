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
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Exercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Item as ResItem;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\ClassificationConstraints;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Group;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Model;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\ObjectBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\MetadataConstraint;

/**
 * Service which manages Group Items Exercises.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */

class GroupItemsService extends ExerciseCreationService
{
    /**
     * A non-string value to identify the objects that should not be added to
     * the exercise.
     */
    const REJECT = 1;

    /**
     * @const MISC_NAME = "Autre";
     */
    const MISC_NAME = "Autre";

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
        $exercise = $this->generateGIExercise($commonModel, $owner);

        // Transformation of the exercise into entities (StoredExercise and Items)
        $exerciseEntity = $this->toStoredExercise(
            $exercise,
            $exerciseModel,
            "group-items",
            array($exercise->getItem())
        );

        return $exerciseEntity;
    }

    /**
     * Inserts the correction in the item and adapt the solution to make it
     * unique and match as best the user's answer
     *
     * @param Item   $entityItem
     * @param Answer $answer
     *
     * @return ItemResource
     */
    public function correct(Item $entityItem, Answer $answer)
    {
        /** @var ResItem $item */
        $itemResource = ItemResourceFactory::create($entityItem);
        $item = $itemResource->getContent();

        $la = AnswerResourceFactory::create($answer);
        $learnerAnswers = $la->getContent();

        $groups = $item->getGroups();   

        // If 'ask' or 'hide', determine the expected name of each group according to the objects
        $viewName = $item->getDisplayGroupNames();
        if ($viewName == 'ask' || $viewName == 'hide') {
            // If 'ask', correct the case
            if ($viewName == 'ask') {
                foreach ($learnerAnswers['gr'] as &$name) {
                    foreach ($groups as $group) {
                        if ($this->areStringsEquivalent($group, $name)) {
                            $name = $group;
                        }
                    }
                }
            }

            // In each group made by the user, count the number of elements of
            // each class according to the solution to determine the most
            // probable group name
            $this->determineGroupsAndModifySolution($item, $viewName, $groups, $learnerAnswers);    //debug mark: $item objet instance de classe, passé par référence, groups array, passé par valeur ?

        // else, the names of the groups are given and there is nothing to do

        // Mark
        $this->mark($item, $learnerAnswers, $groups);   

        // copy the learnerAnswers
        $item->setAnswers($learnerAnswers);

        $itemResource->setContent($item);

        return $itemResource;
    }

    /**
     * Generate a group items exercise from a model
     *
     * @param Model $model the model
     * @param AskerUser  $owner
     *
     * @return Exercise The exercise
     */
    private function generateGIExercise(Model $model, AskerUser $owner)
    {
        // Wording and documents
        $exercise = new Exercise($model->getWording());

        // Documents
        $this->addDocuments($model, $exercise, $owner);

        // Item
        $item = new ResItem();
        $exercise->setItem($item);

        // attributes
        $item->setDisplayGroupNames(
            $this->getDisplayGroupNames(
                $model->getDisplayGroupNames
                    ()
            )
        );

        // for each block of objects
        foreach ($model->getObjectBlocks() as $block) {
            /** @var ObjectBlock $block */
            // get the objects to add from the blocks
            $objectsToAdd = $this->getObjectsFromBlock($model, $block, $owner);

            // find the classification constraint of this block
            if (is_null($model->getClassifConstr())) {
                $classifConstr = $block->getClassifConstr();
            } else {
                $classifConstr = $model->getClassifConstr();
            }

            // add these objects to the exercise
            $this->addObjectsToExercise($objectsToAdd, $classifConstr, $item);

        }

        // shuffle the order of the objects
        $item->shuffleObjects();

        return $exercise;
    }

    /**
     * Add a list of objects to the exercise according to classification
     * constraints
     *
     * @param array                     $objects       An array of ExerciseObject
     * @param ClassificationConstraints $classifConstr The classification constraints
     * @param ResItem                   $item          The exercise to be modified
     */
    private function addObjectsToExercise(
        $objects,
        ClassificationConstraints $classifConstr,
        ResItem &$item
    )
    {
        foreach ($objects as $obj) {
            $count = 0;
            // find its group
            $groupName = "";
            foreach ($classifConstr->getGroups() as $group) {
                /** @var Group $group */
                if ($this->objectInGroup($obj, $group)) {
                    $groupName = $group->getName();
                    $count += 1;
                }
            }

            // if no group
            if ($count == 0) {
                $groupName = $this->chooseGroup($classifConstr);
            } elseif ($count >= 2) {
                $groupName = self::REJECT;
            }

            // add the object to the exercise
            if ($groupName !== self::REJECT) {
                $item->addObjectInGroup($obj, $groupName);
            }
        }
    }

    /**
     * Choose the group of a no-group-object
     *
     * @param ClassificationConstraints $classifConstr
     *
     * @return int|string
     */
    private function chooseGroup(ClassificationConstraints $classifConstr)
    {
        $groupName = "";
        switch ($classifConstr->getOther()) {
            case ClassificationConstraints::REJECT:
                $groupName = self::REJECT;
                break;

            case ClassificationConstraints::MISC:
                $groupName = self::MISC_NAME;
                break;
        }

        return $groupName;
    }

    /**
     * Determine if the object is in the group according to group constraints
     *
     * @param ExerciseObject $object The object
     * @param Group          $group  The group
     *
     * @return boolean true if it is in the group, false else
     * @throws \LogicException
     */
    private function objectInGroup(ExerciseObject $object, Group $group)
    {
        $belongs = true;

        $metadata = $object->getMetadata();

        // To be in the group, the object metadata must match all the
        // constraints.
        foreach ($group->getMDConstraints() as $constraint) {
            /** @var MetadataConstraint $constraint */
            $key = $constraint->getKey();
            $values = $constraint->getValues();
            $comparator = $constraint->getComparator();

            // if the metadata does not exist, it does not belong to the group
            if (!isset($metadata[$key])) {
                return false;
            }

            // in the case of a 'in'
            if ($comparator == 'in') {
                $in = false;

                foreach ($values as $val) {
                    if ($metadata[$key] == $val) {
                        $in = true;
                    }
                }

                // if the value was not in the list, the object is not in
                // the group
                if (!$in) {
                    $belongs = false;
                }
            } // in the case of a '>', '>=', '<' or '<='
            elseif (
                $comparator == 'gte' ||
                $comparator == 'gt' ||
                $comparator == 'lte' ||
                $comparator == 'lt'
            ) {
                if (!$this->$comparator($metadata[$key], $values[0])) {
                    $belongs = false;
                }
            } // in the case of a 'between'
            elseif ($comparator == 'between') {
                if ($metadata[$key] < $values[0] || $metadata[$key] > $values[1]) {
                    $belongs = false;
                }
            } // Comparator error
            elseif ($comparator !== 'exists') {
                throw new \LogicException("Invalid comparator type:" . $comparator);
            }
        }

        return $belongs;
    }

    /**
     * Get the objects from a block
     *
     * @param Model       $model The Model
     * @param ObjectBlock $ob    The ObjectBlock
     * @param AskerUser        $owner
     *
     * @return array An array of ExerciseObject
     */
    private function getObjectsFromBlock(Model $model, ObjectBlock $ob, AskerUser $owner)
    {
        $blockObjects = array();
        $numOfObjects = $ob->getNumberOfOccurrences();

        /*
         * if the block is a list
         */
        if ($ob->isList()) {
            $this->getObjectsFromList($ob, $numOfObjects, $blockObjects, $owner);
        } /*
         * if the block is object constraints
         */
        else {
            // get the resource constraint
            $oc = $ob->getResourceConstraint();

            // add the existence of the meta to display, if there is one
            $mtd = $ob->getMetaToDisplay();
            if (!is_null($mtd)) {
                $oc->addExists($mtd);
            }

            // get the objects
            $blockObjects = $this->exerciseResourceService
                ->getExerciseObjectsFromConstraints(
                    $oc,
                    $numOfObjects,
                    $owner
                );
        }

        // If the value of a metadata field must be displayed instead of the object
        if (!is_null($ob->getMetaToDisplay())) {
            $blockObjects = $this->objectsToMetaStrings(
                $blockObjects,
                $ob->getMetaToDisplay()
            );
        }

        return $blockObjects;
    }

    /**
     * Greater than or equal
     *
     * @param mixed $val1
     * @param mixed $val2
     *
     * @return boolean
     */
    private function gte($val1, $val2)
    {
        return ($val1 >= $val2);
    }

    /**
     * Greater than
     *
     * @param mixed $val1
     * @param mixed $val2
     *
     * @return boolean
     */
    private function gt($val1, $val2)
    {
        return ($val1 > $val2);
    }

    /**
     * Less than or equal
     *
     * @param mixed $val1
     * @param mixed $val2
     *
     * @return boolean
     */
    private function lte($val1, $val2)
    {
        return ($val1 <= $val2);
    }

    /**
     * Less than
     *
     * @param mixed $val1
     * @param mixed $val2
     *
     * @return boolean
     */
    private function lt($val1, $val2)
    {
        return ($val1 < $val2);
    }

    /**
     * Update the two arrays
     *
     * @param array $count
     * @param array $movesTo
     * @param int   $maxKey
     * @param int   $lineKey
     */
    private function updateMovesToAndCount(&$count, &$movesTo, $maxKey, $lineKey)
    {
        $movesTo[$maxKey] = $lineKey;
        unset($count[$lineKey]);

        // Delete maxKey column
        foreach ($count as &$countLine) {
            unset($countLine[$maxKey]);
        }
    }

    /**
     * In an array, find the max and its id and if it is unique
     *
     * @param array   $array
     * @param integer $max
     * @param integer $maxKey
     * @param null    $super
     */
    private function majority($array, &$max, &$maxKey, &$super = null)
    {
        $max = -1;
        $maxKey = null;
        $super = false;

        foreach ($array as $cellKey => $cell) {
            if ($cell > $max) {
                $max = $cell;
                $maxKey = $cellKey;
                $super = true;
            } elseif ($cell == $max) {
                $super = false;
            }
        }
    }

    /**
     * Compares two strings
     *
     * @param string $str1
     * @param string $str2
     *
     * @return bool
     */
    private function areStringsEquivalent($str1, $str2)
    {
        if (strcasecmp($str1, $str2) == 0) {
            return true;
        }

        return false;

    }

    /**
     * Determine what solution group corresponds with learner's group.
     *
     * @param ResItem $item
     * @param string  $viewName
     * @param array   $groups
     * @param array   $learnerAnswers
     */
    private function determineGroupsAndModifySolution(
        ResItem &$item,
        $viewName,
        array $groups,
        array $learnerAnswers
    )
    {
        $solutions = $item->getSolutions();

        $count = $this->countGroupsBySolGroups($groups, $learnerAnswers, $solutions);

        // find what group made by the user corresponds to which solution
        // group
        $movesTo = $this->groupCorrespondence($count, $viewName, $learnerAnswers, $groups);

        // modify the 'groups' array with movesTo
        $newGroups = array();
        foreach ($groups as $key => $group) {
            $newGroups[$movesTo[$key]] = $group;
        }
        $groups = $newGroups;

        // modify the 'solutions' array with movesTo
        foreach ($solutions as &$sol) {
            $sol = $movesTo[$sol];
        }

        // set the modified solution and names to the exercise
        ksort($groups);
        $item->setSolutions($solutions);
        $item->setGroups($groups);
    }

    /**
     * Find the group correspondence between solution and learner's answer.
     *
     * @param array  $count
     * @param string $viewName
     * @param array  $learnerAnswers
     * @param array  $groups
     *
     * @return array
     */
    private function groupCorrespondence(
        array &$count,
        $viewName,
        array $learnerAnswers,
        array $groups
    )
    {
        $movesTo = array();
        $restart = true;
        while ($restart && count($count) > 0) {
            $restart = false;

            // look for good name and supermajority
            if ($viewName == 'ask') {
                foreach ($count as $lineKey => $line) {
                    // find the supermajority
                    $this->majority($line, $max, $maxKey, $super);

                    // if the line has supermajority and good name, fill
                    // the movesTo array and delete the line.
                    if (
                        $super && $max >= 0 &&
                        $learnerAnswers['gr'][$lineKey] == $groups[$maxKey]
                    ) {
                        $this->updateMovesToAndCount($count, $movesTo, $maxKey, $lineKey);
                        // research can be restarted from the beginning
                        $restart = true;
                        break;
                    }
                }
            }

            // look for supermajority
            if (!$restart) {
                foreach ($count as $lineKey => $line) {
                    // find the supermajority
                    $this->majority($line, $max, $maxKey, $super);

                    // if the line has supermajority, fill
                    // the movesTo array and delete the line.
                    if ($super && $max >= 0) {
                        $this->updateMovesToAndCount($count, $movesTo, $maxKey, $lineKey);
                        // research can be restarted from the beginning
                        $restart = true;
                        break;
                    }
                }
            }

            // look for majority (not super) and right name
            if (!$restart && $viewName == 'ask') {
                foreach ($count as $lineKey => $line) {
                    // find the supermajority
                    $this->majority($line, $max, $maxKey);

                    // if the line has majority and good name, fill
                    // the movesTo array and delete the line.
                    if (
                        $max >= 0 &&
                        $learnerAnswers['gr'][$lineKey] == $groups[$maxKey]
                    ) {
                        $this->updateMovesToAndCount($count, $movesTo, $maxKey, $lineKey);
                        // research can be restarted from the beginning
                        $restart = true;
                        break;
                    }
                }
            }

            // look for majority
            if (!$restart) {
                foreach ($count as $lineKey => $line) {
                    // find the supermajority
                    $this->majority($line, $max, $maxKey);

                    // if the line has majority, fill
                    // the movesTo array and delete the line.
                    if ($max >= 0) {
                        $this->updateMovesToAndCount($count, $movesTo, $maxKey, $lineKey);
                        // research can be restarted from the beginning
                        $restart = true;
                        break;
                    }
                }
            }
        }

        return $movesTo;
    }

    /**
     * Count, in each group, the number of occurrences from each solution group.
     *
     * @param array $groups
     * @param array $learnerAnswers
     * @param array $solutions
     *
     * @return array
     */
    private function countGroupsBySolGroups(array $groups, array $learnerAnswers, array $solutions)
    {
        // Init the count array
        $count = array();
        $size = count($groups);
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                $count[$i][$j] = 0;
            }
        }

        // Fill the count array
        $laObj = $learnerAnswers['obj'];
        foreach ($solutions as $key => $sol) {
            $count[$laObj[$key]][$sol]++;
        }

        return $count;
    }

    /**
     * Compute and add the mark to the exercise
     *
     * @param ResItem $item
     * @param array   $learnerAnswers
     * @param array   $groups
     */
    private function mark(ResItem &$item, array $learnerAnswers, array $groups)
    {
        $pointsForName = 0.0;
        $pointsForGrouping = 0.0;

        if ($item->getDisplayGroupNames() == 'ask') {
            $maxPointsForNames = 20.0;
            $maxPointsForGrouping = 80.0;

            foreach ($groups as $key => $group) {
                if ($group === $learnerAnswers['gr'][$key]) {
                    $pointsForName += $maxPointsForNames / count($groups);
                }
            }
        } else {
            $maxPointsForGrouping = 100.0;
        }

        foreach ($item->getSolutions() as $key => $sol) {
            if ($sol === $learnerAnswers['obj'][$key]) {
                $pointsForGrouping += $maxPointsForGrouping / count($item->getObjects());
            }
        }

        $mark = $pointsForName + $pointsForGrouping;
        if ($mark < 0) {
            $mark = 0;
        }

        $item->setMark($mark);
    }

    /**
     * Get the displayGroupNames
     *
     * @param string $getDisplayGroupNames
     *
     * @throws \LogicException
     * @return string
     */
    private function getDisplayGroupNames($getDisplayGroupNames)
    {
        switch ($getDisplayGroupNames) {
            case Model::ASK:
                return ResItem::ASK;
            case Model::SHOW:
                return ResItem::SHOW;
            case Model::HIDE:
                return ResItem::HIDE;
            default:
                throw new \LogicException('Invalid displayGroupNames');
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
        $nbGroups = count($item->getGroups());

        if ($item->getDisplayGroupNames() == "ask") {
            if (!isset ($answer['gr']) || count($answer['gr']) !== $nbGroups) {
                throw new InvalidAnswerException('Invalid number of group names in the answer');
            }
        } else {
            if (isset($answer['gr'])) {
                throw new InvalidAnswerException('Name of groups must not be specified an answer to this exercise');
            }
        }

        if (!isset($answer['obj'])) {
            throw new InvalidAnswerException('Missing "obj" array in the answer');
        }

        if (count($answer['obj']) !== count($item->getObjects())) {
            throw new InvalidAnswerException('Invalid number of objects in the answer');
        }

        foreach ($answer['obj'] as $ans) {
            if (!array_key_exists($ans, $item->getGroups())) {
                throw new InvalidAnswerException('Invalid rank of group');
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
        /** @var \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Item $content */
        $content = $itemResource->getContent();
        $content->setSolutions(null);

        return $itemResource;
    }
}
