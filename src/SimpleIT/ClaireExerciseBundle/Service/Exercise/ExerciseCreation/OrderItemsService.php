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
use SimpleIT\ClaireExerciseBundle\Model\ExerciseObject\ExerciseTextFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OrderItems\Exercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OrderItems\Item as ResItem;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\Model;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\ObjectBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\SequenceBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseSequenceObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseTextObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResourceFactory;

/**
 * Service which manages Order Items Exercises.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */

class OrderItemsService extends ExerciseCreationService
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
        $exercise = $this->generateOIExercise($commonModel, $owner);

        // Transformation of the exercise into entities (StoredExercise and Items)
        return $this->toStoredExercise(
            $exercise,
            $exerciseModel,
            "order-items",
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
        #die(var_dump($learnerAnswers));

        $item->setAnswers($learnerAnswers);

        // check the answers
        $sol = $item->getSolutions();
        #die(var_dump($learnerAnswers) . var_dump($sol));

        // if it matches the answer, set it as solution
        if ($this->matchSolution($learnerAnswers, $sol)) {
            $sol = $learnerAnswers;
        } // else, take the basic solution
        else {
            $sol = $this->getBasicSolution($sol);
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
    private function mark(ResItem &$item)
    {
        $learnerAnswers = $item->getAnswers();
        $solutions = $item->getSolutions();

        $mark = 100;

        if ($item->getGiveFirst()) {
            $start = 0;
        } else {
            $start = 1;
        }

        if ($item->getGiveLast()) {
            $end = count($item->getObjects());
        } else {
            $end = count($item->getObjects()) - 1;
        }

        for ($i = $start; $i < $end; $i++) {
            if ($solutions[$i] != $learnerAnswers[$i]) {
                $mark -= 100 / ($end - $start);
            }
        }

        if ($mark < 0) {
            $mark = 0;
        }
        $item->setMark($mark);
    }

    /**
     * Generate an order items exercise from a model
     *
     * @param Model $model the model
     * @param AskerUser  $owner
     *
     * @return Exercise The exercise
     */
    private function generateOIExercise(Model $model, AskerUser $owner)
    {
        // Wording and documents
        $exercise = new Exercise($model->getWording());

        // Documents
        $this->addDocuments($model, $exercise, $owner);

        // Item
        $item = new ResItem();
        $exercise->setItem($item);

        // array of object
        $objects = array();

        // array of values
        $values = array();

        // array of solution
        $solutions = array();

        // If object list
        if ($model->getIsSequence()) {
            $this->exerciseFromSequence($model, $solutions, $objects, $owner);
        } else {
            $this->exerciseFromObjList($model, $solutions, $objects, $values, $owner);
        }

        // add the objects and the solution
        $item->setSolutions($solutions);
        $item->setObjects($objects);

        // values
        if ($model->getShowValues())
        {
            $item->setValues($values);
        }

        // shuffle the order of the objects
        $item->shuffleObjects();

        // set give first and last
        if ($model->isGiveFirst()) {
            if($model->getOrder() == 'desc'){
                $item->setGiveFirst($this->getLast($item->getSolutions()));
            }else{
                $item->setGiveFirst($this->getFirst($item->getSolutions()));
            }
        } else {
            $item->setGiveFirst(-1);
        }
        if ($model->isGiveLast()) {
            if($model->getOrder() == 'desc'){
                $item->setGiveLast($this->getFirst($item->getSolutions()));
            }else{
                $item->setGiveLast($this->getLast($item->getSolutions()));
            }
        } else {
            $item->setGiveLast(-1);
        }

        return $exercise;
    }

    /**
     * Get the first object id of the solution
     *
     * @param $solutions
     *
     * @return mixed
     */
    private function getFirst($solutions)
    {
        if (is_array($solutions[0])) {
            return $this->getFirst($solutions[0]);
        } else {
            return $solutions[0];
        }
    }

    /**
     * Get the last object id of the solution
     *
     * @param $solutions
     *
     * @return mixed
     */
    private function getLast($solutions)
    {
        $maxKey = 0;
        foreach (array_keys($solutions) as $key) {
            if (is_numeric($key) && $key > $maxKey) {
                $maxKey = $key;
            }
        }

        if (is_array($solutions[$maxKey])) {
            return $this->getLast($solutions[$maxKey]);
        } else {
            return $solutions[$maxKey];
        }
    }

    /**
     * Create the exercise from a sequence
     *
     * @param Model $model
     * @param array $solutions
     * @param array $objects
     * @param AskerUser  $owner
     */
    private function exerciseFromSequence(
        Model $model,
        array &$solutions,
        array &$objects,
        AskerUser $owner
    )
    {
        $sequenceBlock = $model->getSequenceBlock();
        // get the sequence with the object inside (if object)
        $sequenceObject = $this->getSequenceFromBlock($sequenceBlock, $owner);

        // choose a cutting or an extraction and build the solution
        // if only cutting
        if ($sequenceBlock->isKeepAll()) {
            $this->exFromKeepAll($sequenceBlock, $sequenceObject, $solutions, $objects);
        } // else, extraction of parts of the sequence
        else {
            $this->exFromExtract($sequenceBlock, $sequenceObject, $solutions, $objects);
        }

        // update the object list index and adapt the solution
        $this->updateSolution($solutions);
        $this->reorganiseObjectsAndSolution($objects, $solutions);
    }

    /**
     * Create an exercise in the case of the extraction of parts from a sequence
     *
     * @param SequenceBlock          $sequenceBlock
     * @param ExerciseSequenceObject $sequenceObject
     * @param array                  $solutions
     * @param array                  $objects
     */
    private function exFromExtract(
        SequenceBlock $sequenceBlock,
        ExerciseSequenceObject $sequenceObject,
        array &$solutions,
        array &$objects
    )
    {
        //get solution
        $solutions = $sequenceObject->getStructure();
        // select random parts
        $nbParts = $sequenceBlock->getNumberOfParts();
        $objects = $sequenceObject->getObjects();
        $deletes = array_rand($objects, count($objects) - $nbParts);

        // for each part to delete
        foreach ($deletes as $del) {
            // remove it from the objects
            unset($objects[$del]);

            // remove it from the solution
            $this->deleteFromArray($solutions, $del);
        }
    }

    /**
     * Create an exercise in the case of keeping all the parts of the sequence and shuffling them
     *
     * @param SequenceBlock          $sequenceBlock
     * @param ExerciseSequenceObject $sequenceObject
     * @param array                  $solutions
     * @param array                  $objects
     */
    private function exFromKeepAll(
        SequenceBlock $sequenceBlock,
        ExerciseSequenceObject $sequenceObject,
        array &$solutions,
        array &$objects
    )
    {
        // select random cutting points
        $nbParts = $sequenceBlock->getNumberOfParts();
        $cuttingArray = $sequenceObject->getObjects();
        unset($cuttingArray[count($cuttingArray) - 1]);
        $extract = array_rand($cuttingArray, $nbParts - 1);
        sort($extract);

        // make parts with keys of objects
        $parts = array();
        $objects = $sequenceObject->getObjects();
        $i = 0;
        foreach ($objects as $key => $obj) {
            if ($i != count($extract) && $key > $extract[$i]) {
                $i++;
            }
            $parts[$i][] = $key;
        }

        // build new solution
        $this->buildSequenceSolution($sequenceObject, $parts, $solutions);

        // group the objects (remove the index in solution)
        $this->groupObjects($parts, $objects, $solutions);
    }

    /**
     * Merge the consecutive objects that stay together in a part
     *
     * @param array $parts
     * @param array $objects
     * @param array $solutions
     */
    private function groupObjects(array $parts, array &$objects, array &$solutions)
    {
        foreach ($parts as $part) {
            $newObj = $objects[$part[0]];

            // for each object to merge
            for ($idObjPart = 1; $idObjPart < count($part); $idObjPart++) {
                // merge it
                $newObj = $this->mergeObj($newObj, $objects[$part[$idObjPart]]);

                // remove it from the objects
                unset($objects[$part[$idObjPart]]);

                // remove it from the solution
                $this->deleteFromArray($solutions, $part[$idObjPart]);
            }

            $objects[$part[0]] = $newObj;
        }
    }

    /**
     * Modify the solution according to the sequence object, the parts and the solution. Objects
     * that are in two consecutive blocks make these block merge (for example).
     *
     * @param ExerciseSequenceObject $sequenceObject
     * @param array                  $parts
     * @param array                  $solutions
     */
    private function buildSequenceSolution(
        ExerciseSequenceObject $sequenceObject,
        array $parts,
        array &$solutions
    )
    {
        $solutions = $sequenceObject->getStructure();
        foreach ($parts as $part) {
            // for each object merging
            for ($idObjPart = 0; $idObjPart < count($part) - 1; $idObjPart++) {
                // update solution
                $this->updateSolution($solutions);
                // Find the address of both elements
                $el1 = $part[$idObjPart];
                $el2 = $part[$idObjPart + 1];
                $address1 = $this->findBlockOrElAddress($solutions, $el1);
                $address2 = $this->findBlockOrElAddress($solutions, $el2);

                // bring the elements (or blocks) at the same level
                $this->bringToSameLevel($el1, $el2, $address1, $address2, $solutions);

                // if el1 or/and el2 are in different 'or' blocks,
                // merge them
                $this->mergeOrBlock($address1, $address2, $solutions);
            }
        }
    }

    /**
     * Bring two elements to the same level in the tree.
     *
     * @param mixed $el1
     * @param mixed $el2
     * @param array $address1
     * @param array $address2
     * @param array $solutions
     */
    private function bringToSameLevel(
        $el1,
        $el2,
        array &$address1,
        array &$address2,
        array &$solutions
    )
    {
        // While the elements are not in the same node
        while (!$this->sameNode($address1, $address2)) {
            // if el1 deeper (or the same) in the tree than el2
            if (count($address1) >= count($address2)) {
                $this->move($solutions, $address1, true);
            }

            // if el2 strictly deeper than el1
            if (count($address1) < count($address2)) {
                $this->move($solutions, $address2, false);

            }
            // update tree
            $this->updateSolution($solutions);

            // Addresses
            $address1 = $this->findBlockOrElAddress($solutions, $el1);
            $address2 = $this->findBlockOrElAddress($solutions, $el2);
        }
    }

    /**
     * Merge two consecutive 'or' (ordered) blocks together
     *
     * @param array $address1
     * @param array $address2
     * @param array $solutions
     */
    private function mergeOrBlock(array $address1, array $address2, array &$solutions)
    {
        // find the mother node (if they are not in the root)
        if (count($address1) > 0) {
            $mother = & $solutions;
            for ($j = 0; $j < count($address1) - 1; $j++) {
                $mother = & $mother[$address1[$j]];
            }

            // if they are from different blocks
            if ($address1[$j] != $address2[$j]) {
                $item1 = & $mother[$address1[$j]];
                $item2 = & $mother[$address2[$j]];

                if (is_array($item1) || is_array($item2)) {
                    if (is_array($item1) && !is_array($item2)) {
                        $item1 = array_merge($item1, array($item2));
                    } elseif (is_array($item1) && is_array($item2)) {
                        $item1 = array_merge($item1, $item2);
                    } elseif (!is_array($item1) && is_array($item2)) {
                        $item1 = array_merge(array('name' => 'or', $item1), $item2);
                    }

                    unset($mother[$address2[$j]]);
                    $mother = array_merge($mother);
                }
            }
        }
    }

    /**
     * Create an exercise from an object list
     *
     * @param Model $model
     * @param array $solutions
     * @param array $objects
     * @param array $values
     * @param AskerUser  $owner
     */
    private function exerciseFromObjList(
        Model $model,
        array &$solutions,
        array &$objects,
        array &$values,
        AskerUser $owner
    )
    {
        // get the objects and the metadata
        foreach ($model->getObjectBlocks() as $ob) {
            $objects = array_merge($objects, $this->getObjectsFromBlock($ob, $owner));
        }
        // find their order
        $metaValues = array();
        foreach ($objects as $key => $obj) {
            /** @var ExerciseObject $obj */
            $metaValues[$key] = $obj->getMetavalue();
        }
        asort($metaValues);

        $objectsToAdd = array();
        foreach ($metaValues as $key => $mv) {
            $objectsToAdd[] = $objects[$key];
            $values[] = $mv;
        }
        $objects = $objectsToAdd;

        #die c est ici quon genere la solution
        // create the  the solution
        $solutions['name'] = 'or';
        $i = 0;

        foreach ($objects as $key => $obj) {
            /** @var ExerciseObject $obj */
            // look at the previous to see if identical
            if (isset(
                $objects[$key - 1]) &&
                $objects[$key - 1]->getMetavalue() == $obj->getMetavalue()
            ) {
                // write in the 'di' block
                $solutions[$i][] = $key;

                // if the next is different, increment $i
                if (isset(
                    $objects[$key + 1]) &&
                    $objects[$key + 1]->getMetavalue() == $obj->getMetavalue()
                ) {
                    $i++;
                }
            } // else, look at the next one to see if identical
            elseif (isset(
                $objects[$key + 1]) &&
                $objects[$key + 1]->getMetavalue() == $obj->getMetavalue()
            ) {
                // start a 'di' block
                $solutions[$i]['name'] = 'di';
                $solutions[$i][] = $key;
            } // else, normal add
            else {
                $solutions[$i] = $key;
                $i++;
            }
        }
        if ($model->getOrder() == 'desc'){
            krsort($solutions);
        }
    }

    /**
     * Reorganize object and solution arrays. Gaps in index are removed.
     *
     * @param array $objects
     * @param array $solutions
     */
    private function reorganiseObjectsAndSolution(array &$objects, array &$solutions)
    {
        $i = 0;
        $this->reorganiseObjAndSolRecursive($objects, $solutions, $i);
    }

    /**
     * Recursive function to reorganize objects
     *
     * @param array $objects
     * @param array $solutions
     * @param int   $i
     */
    private function reorganiseObjAndSolRecursive(array &$objects, array &$solutions, &$i)
    {
        foreach ($solutions as $key => &$sub) {
            if (is_array($sub)) {
                $this->reorganiseObjAndSolRecursive($objects, $sub, $i);
            } elseif ($key !== 'name') {
                if ($i != $sub) {
                    $objects[$i] = $objects[$sub];
                    unset ($objects[$sub]);
                    $sub = $i;
                }
                $i++;
            }
        }
    }

    /**
     * Merge two objects in one. The objects must be texts.
     *
     * @param ExerciseTextObject $obj1
     * @param ExerciseTextObject $obj2
     *
     * @throws \Exception
     * @return ExerciseTextObject
     */
    private function mergeObj(ExerciseTextObject $obj1, ExerciseTextObject $obj2)
    {
        if (
            get_class(
                $obj1
            ) === 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseTextObject' &&
            get_class(
                $obj2
            ) === 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseTextObject'
        ) {
            return ExerciseTextFactory::createFromTwoObjects($obj1, $obj2);
        } else {
            throw new \LogicException('Impossible to merge these objects');
        }
    }

    /**
     * Delete a value from a recursive array
     *
     * @param array $array
     * @param mixed $val
     */
    private function deleteFromArray(array &$array, $val)
    {
        foreach ($array as $key => &$sub) {
            if (is_array($sub)) {
                $this->deleteFromArray($sub, $val);
            } elseif ($sub == $val) {
                unset($array[$key]);
            }
        }
    }

    /**
     * Move an element out of his block to put it one position up or down in the array
     *
     * @param array   $solutions
     * @param array   $address
     * @param boolean $up
     */
    private function move(array &$solutions, array $address, $up)
    {
        // find the mother and grandMother nodes
        $gMother = & $solutions;
        for ($j = 0; $j < count($address) - 2; $j++) {
            $gMother = & $gMother[$address[$j]];
        }

        $mother = & $gMother[$address[$j]];
        $elId = $address[$j + 1];

        // move the element up (it can be a 'or' branch)
        // save the element
        $temp = $mother[$elId];

        // remove it
        unset($mother[$elId]);

        // modify grandMother
        if ($up) {
            $indexInsert = $address[$j] + 1;
        } else {
            $indexInsert = $address[$j];
        }
        $count = count($gMother);
        for ($i = $count - 1; $i > $indexInsert; $i--) {
            $gMother[$i] = $gMother[$i - 1];
        }
        $gMother[$i] = $temp;
    }

    /**
     * Determine if two addresses are in the same node
     *
     * @param array $address1
     * @param array $address2
     *
     * @return boolean
     */
    private function sameNode(array $address1, array $address2)
    {
        $ak1 = array_keys(array_diff_assoc($address1, $address2));
        $ak2 = array_keys(array_diff_assoc($address2, $address1));

        return (
            (
                count($ak1) == 1 &&
                count($ak2) == 1 &&
                $ak1[0] == count($address1) - 1 &&
                $ak2[0] == count($address2) - 1
            ) ||
            (
                count($ak1) == 0 &&
                count($ak2) == 0
            )
        );
    }

    /**
     * Get the address of an element or the address of the containing block if
     * it is an 'or' block
     *
     * @param array $array
     * @param int   $val
     *
     * @return array
     * @throws \Exception
     */
    private function findBlockOrElAddress($array, $val)
    {
        $address = $this->findAddress($array, $val);
        // find the mother node
        $mother = $array;
        for ($j = 0; $j < count($address) - 1; $j++) {
            $mother = $mother[$address[$j]];
        }

        if ($mother['name'] == 'or') {
            unset($address[count($address) - 1]);
        }

        if (is_null($address)) {
            throw new \LogicException('Impossible to find the value' . $val);
        }

        return $address;
    }

    /**
     * Get the address of an element.
     *
     * @param array $array
     * @param int   $val
     *
     * @return array
     */
    private function findAddress($array, $val)
    {
        foreach ($array as $key => $sub) {
            if ($sub === $val) {
                return array($key);
            } elseif (is_array($sub)) {
                $address = $this->findAddress($sub, $val);
                if (!is_null($address)) {
                    return array_merge(array($key), $address);
                }
            }
        }

        return null;
    }

    /**
     * Update the solution array (gaps in index, blocks of only one elements)
     *
     * @param array $solutions
     */
    private function updateSolution(array &$solutions)
    {
        $newSol = array();
        $i = 0;
        $isOr = ($solutions['name'] === 'or');

        foreach ($solutions as $ks => $sub) {
            if (is_array($sub)) {
                $this->updateSolution($sub);
            }
            if ($ks === 'name') {
                $newSol['name'] = $sub;
            } elseif ($isOr && is_array($sub) && $sub['name'] === 'or') {
                foreach ($sub as $key => $subOr) {
                    if ($key !== 'name') {
                        $newSol[$i] = $subOr;
                        $i++;
                    }
                }
            } elseif (!is_null($sub)) {
                $newSol[$i] = $sub;
                $i++;
            }
        }

        // if the block contains only one element, cancel the block and return
        // the element
        if ($i > 1) {
            $solutions = $newSol;
        } elseif ($i === 1) {
            $solutions = $newSol[0];
        } else {
            $solutions = null;
        }
    }

    /**
     * Get an ExerciseSequenceObject from a block
     *
     * @param SequenceBlock $sb
     * @param AskerUser          $owner
     *
     * @return ExerciseSequenceObject
     */
    private function getSequenceFromBlock(SequenceBlock $sb, AskerUser $owner)
    {
        /*
         * if the block is a list
         */
        if ($sb->isList()) {
            $existingResourceIds = $sb->getResources();

            // select a random object
            $objIndex = array_rand($existingResourceIds);
            $objId = $existingResourceIds[$objIndex];

            // get this object in form of an ExerciseSequenceObject
            $sequence = $this->exerciseResourceService
                ->getExerciseObject($objId, $owner);
        } /*
         * if the block is object constraints
         */
        else {
            // get the resource constraint
            $oc = $sb->getResourceConstraint();

            // get the objects
            $blockObjects = $this->exerciseResourceService
                ->getExerciseObjectsFromConstraints(
                    $oc,
                    1,
                    $owner
                );
            $sequence = $blockObjects[0];
        }

        return $sequence;
    }

    /**
     * Get the objects from a block
     *
     * @param ObjectBlock $ob    The ObjectBlock
     * @param AskerUser        $owner
     *
     * @return array An array of ExerciseObject
     */
    private function getObjectsFromBlock(ObjectBlock $ob, AskerUser $owner)
    {
        $blockObjects = array();
        $numOfObjects = $ob->getNumberOfOccurrences();
        /*
         * if the block is a list
         */
        if ($ob->isList()) {
            $this->getObjectsFromList($ob, $numOfObjects, $blockObjects, $owner);
        } // if the block is object constraints
        else {
            // get the resource constraint
            $oc = $ob->getResourceConstraint();

            // add the existence of the link meta key
            $oc->addExists($ob->getMetaKey());

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

        // add the ordering metavalue
        foreach ($blockObjects as &$bo) {
            /** @var ExerciseObject $bo */
            $md = $bo->getMetadata();
            $bo->setMetavalue($md[$ob->getMetaKey()]);
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
     * Create a linear solution from the solution
     *
     * @param array $sol
     *
     * @return array
     */
    private function getBasicSolution(array $sol)
    {
        $newSolution = array();
        $this->copySolution($sol, $newSolution);

        return $newSolution;
    }

    /**
     * Recursive function to flatten an array
     *
     * @param mixed $array
     * @param mixed $newSol
     */
    private function copySolution($array, &$newSol)
    {
        if (is_array($array)) {
            foreach ($array as $key => $sub) {
                if ($key !== 'name') {
                    $this->copySolution($sub, $newSol);
                }
            }
        } else {
            $newSol[] = $array;
        }
    }

    /**
     * Check if the learner's answer matches the solution
     *
     * @param array $answer
     * @param array $solutions
     *
     * @return bool
     */
    private function matchSolution(array $answer, array $solutions)
    {
        $i = 0;

        return $this->findNext($solutions, $answer, $i);
    }

    /**
     * Test if an element of the solution equals or contains at the right place the answer
     * (recursive with findNext)
     *
     * @param mixed $el
     * @param array $answer
     * @param int   $i
     *
     * @return bool
     */
    private function testElement(&$el, array $answer, &$i)
    {
        // if it is a block
        if (is_array($el)) {
            return $this->findNext($el, $answer, $i);

        } // if it is a value
        else {
            if ($el === $answer[$i]) {
                $i++;

                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Move on in the array to continue the exploration (recursive with testElement)
     *
     * @param array $array
     * @param array $answer
     * @param int   $i
     *
     * @return bool
     */
    private function findNext(array &$array, array $answer, &$i)
    {
        $keys = array_keys($array);

        // if or block
        // if or block
        if ($array['name'] === "or") {
            if ($keys[0] === "name") {
                $id = $keys[1];
            } else {
                $id = $keys[0];
            }

            $found = $this->testElement($array[$id], $answer, $i);
        } // if di block
        else {
            // memorize current $i
            $currentI = $i;
            $keyIndex = 0;
            $found = $this->testElement($array[$keys[$keyIndex]], $answer, $i);

            // test all the elements until the right one is found or the end
            // is reached
            while (
                $keyIndex < count($keys) - 1 &&
                ($keys[$keyIndex] === "name" || !$found) &&
                $currentI == $i
            ) {
                $keyIndex++;
                $found = $this->testElement($array[$keys[$keyIndex]], $answer, $i);
            }

            // if the end of the block is reached, the answer is false
            // if it is not found, the answer is false
            if ($keyIndex === count($keys) || !$found) {
                return false;
            } else {
                $id = $keys[$keyIndex];
            }
        }

        // if found
        if ($found) {
            // delete the found element
            unset($array[$id]);

            if (count($array) === 1) {
                return true;
            } else {
                return $this->findNext($array, $answer, $i);
            }
        } // if not found
        else {
            return false;
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

        $nbObj = count($item->getObjects());

        if (count($answer) !== $nbObj) {
            throw new InvalidAnswerException('Invalid number of objects in the answer');
        }

        foreach ($answer as $ans) {
            if (!array_key_exists($ans, $item->getObjects())) {
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
        /** @var \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OrderItems\Item $content */
        $content = $itemResource->getContent();
        $content->setSolutions(null);

        return $itemResource;
    }
}
