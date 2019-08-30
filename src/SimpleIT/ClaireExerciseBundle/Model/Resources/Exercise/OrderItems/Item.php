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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OrderItems;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonItem;

/**
 * Class Exercise
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Item extends CommonItem
{
    /**
     * @var array $objects An array of ExerciseObject
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject>")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $objects = array();

    /**
     * @var boolean $giveFirst Id of the first or -1 if no help
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $giveFirst;

    /**
     * @var boolean $giveLast Id of the last or -1 if no help
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $giveLast;

    /**
     * The possible variants of the sequence. It is a recursive array of the
     * following form, for instance:
     * ['name'] => 'or',
     * [0] => 1,
     * [1] => array (
     *      ['name'] => 'di',
     *      [0] => 3,
     *      [1] => array (
     *              ['name'] => 'or',
     *              [0] => 2,
     *              [1] => 0,
     *              [2] => 5
     *          ),
     *      [2] => 6
     *  ),
     * [2] => 4
     * )
     * 'or' indicates a block in which the order of the elements cannot be
     * moved. 'di' indicates a block in which the order can be moved.
     * Every index can contain another an array (order or disorder block) or
     * a value that is the index of the ExerciseObject in the $objects array.
     *
     * @var array $solution
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "item_storage"})
     */
    private $solutions = array();

    /**
     * The learner's answers.
     *
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected"})
     */
    private $answers = array();

    /**
     * The values used to sort the objects
     *
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "item_storage"})
     */
    private $values = array();

    /**
     * Get solution
     *
     * @return array
     */
    public function getSolutions()
    {
        return $this->solutions;
    }

    /**
     * Get objects
     *
     * @return array An array of ExerciseObject
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Shuffle objects and update the solution
     */
    public function shuffleObjects()
    {
        // create an array with the keys
        $objKeys = array_keys($this->objects);

        // shuffle the array of keys
        if ($this->giveFirst && $this->giveLast) {
            $first = $objKeys[0];
            $last = $objKeys[count($objKeys) - 1];
            unset ($objKeys[count($objKeys) - 1]);
            unset ($objKeys[0]);
            shuffle($objKeys);
            $objKeys = array_merge(array($first), $objKeys, array($last));
        } elseif (!$this->giveFirst && $this->giveLast) {
            $last = $objKeys[count($objKeys) - 1];
            unset ($objKeys[count($objKeys) - 1]);
            shuffle($objKeys);
            $objKeys = array_merge($objKeys, array($last));
        } elseif ($this->giveFirst && !$this->giveLast) {
            $first = $objKeys[0];
            unset ($objKeys[0]);
            shuffle($objKeys);
            $objKeys = array_merge(array($first), $objKeys);
        } else {
            shuffle($objKeys);
        }

        // create new arrays
        $newObjects = array();
        $newValues = array();

        // fill the new object array with the shuffled keys
        foreach ($objKeys as $index => $key) {
            $newObjects[$index] = $this->objects[$key];
            if (key_exists($key,$this->values)){
                $newValues[$index] = $this->values[$key];
            }
        }

        // modify the solution
        $this->modifySolution($this->solutions, $objKeys);

        // Copy in the exercise object arrays
        $this->objects = $newObjects;
        $this->values = $newValues;
    }

    /**
     * Modify the solution according to a new array of keys
     *
     * @param array $solutions
     * @param array $objKeys
     */
    private function modifySolution(array &$solutions, array $objKeys)
    {
        foreach ($solutions as $key => &$sub) {
            if (is_array($sub)) {
                $this->modifySolution($sub, $objKeys);
            } elseif ($key !== "name") {
                $sub = array_search($sub, $objKeys);
            }
        }
    }

    /**
     * Set answers
     *
     * @param array $answers
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    /**
     * Get answers
     *
     * @return array
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set giveFirst
     *
     * @param boolean $giveFirst
     */
    public function setGiveFirst($giveFirst)
    {
        $this->giveFirst = $giveFirst;
    }

    /**
     * Get giveFirst
     *
     * @return boolean
     */
    public function getGiveFirst()
    {
        return $this->giveFirst;
    }

    /**
     * Set giveLast
     *
     * @param boolean $giveLast
     */
    public function setGiveLast($giveLast)
    {
        $this->giveLast = $giveLast;
    }

    /**
     * Get giveLast
     *
     * @return boolean
     */
    public function getGiveLast()
    {
        return $this->giveLast;
    }

    /**
     * Set objects
     *
     * @param array $objects
     */
    public function setObjects($objects)
    {
        $this->objects = $objects;
    }

    /**
     * Set solutions
     *
     * @param array $solutions
     */
    public function setSolutions($solutions)
    {
        $this->solutions = $solutions;
    }

    /**
     * Set values
     *
     * @param array $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * Get values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}
