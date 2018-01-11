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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems;

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
     * @var array $fixParts An array of ExerciseObject (text, picture, ...)
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject>")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $fixParts = array();

    /**
     * @var array $mobileParts An array of ExerciseObject (text, picture, ...)
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject>")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $mobileParts = array();

    /**
     * Solutions: array which keys are the keys from fixParts and mobileParts
     * Array of array :
     * array(
     *      0 => array(0, 1),
     *      1 => array(0, 1),
     *      2 => array(2),
     *      3 => array()
     *      )
     * In this example, fixPart 0 can be linked with mobilePart 0 or 1
     *                  fixPart 1 can be linked with mobilePart 0 or 1
     *                  fixPart 2 can be linked only with mobilePart 2
     *                  fixPart 3 is a fake
     *
     * @var array $solutions
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
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
     * Add the ExercisePairs to the Exercise. It creates the solution array
     *
     * @param array $mobileParts An array of ExercisePairs
     * @param array $fixParts    An array of ExercisePairs
     */
    function addPairs(array $mobileParts, array $fixParts)
    {
        for ($i = 0; $i < count($fixParts); $i++) {
            $this->fixParts[$i] = $mobileParts[$i];
            $this->mobileParts[$i] = $fixParts[$i];
            $this->solutions[$i] = array($i);
        }
    }

    /**
     * Shuffle the fix parts and mobile parts order and update the solution
     */
    public function shufflePairs()
    {
        // create an array with the keys of the fix parts
        $fixArray = array();
        foreach ($this->fixParts as $key => $part) {
            $fixArray[] = $key;
        }

        // create an array with the keys of the mobile parts
        $mobileArray = array();
        foreach ($this->mobileParts as $key => $part) {
            $mobileArray[] = $key;
        }

        // shuffle the arrays
        shuffle($fixArray);
        shuffle($mobileArray);

        // create the reverse array for the mobile parts (value => key)
        $mobileArrayRev = array();
        foreach ($mobileArray as $key => $value) {
            $mobileArrayRev[$value] = $key;
        }

        // build new arrays with the suffle order and build the solution
        $fixParts = array();
        $mobileParts = array();
        $solutions = array();
        foreach ($fixArray as $key => $part) {
            $fixParts[$key] = $this->fixParts[$part];
            $sol = array();
            foreach ($this->solutions[$part] as $solution) {
                $sol[] = $mobileArrayRev[$solution];
            }
            $solutions[$key] = $sol;
        }
        foreach ($mobileArray as $key => $part) {
            $mobileParts[$key] = $this->mobileParts[$part];
        }

        // copy the new arrays into the object ones
        $this->fixParts = $fixParts;
        $this->mobileParts = $mobileParts;
        $this->solutions = $solutions;
    }

    /**
     * Get fix parts
     *
     * @return array
     */
    public function getFixParts()
    {
        return $this->fixParts;
    }

    /**
     * Get mobile parts
     *
     * @return array
     */
    public function getMobileParts()
    {
        return $this->mobileParts;
    }

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
     * Set fixParts
     *
     * @param array $fixParts
     */
    public function setFixParts($fixParts)
    {
        $this->fixParts = $fixParts;
    }

    /**
     * Set mobileParts
     *
     * @param array $mobileParts
     */
    public function setMobileParts($mobileParts)
    {
        $this->mobileParts = $mobileParts;
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
}
