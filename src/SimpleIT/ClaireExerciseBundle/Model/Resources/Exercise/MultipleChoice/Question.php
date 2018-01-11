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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\MultipleChoice;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonItem;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\Markable;

/**
 * An ExerciseQuestion is a question of multiple choice in its final version,
 * ready to be used in an exercise. All the propositions will be used and the order of the
 * propositions is the one to use for the formatting of the exercise. This order can be
 * shuffled with the function shufflePropositionsOrder().
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Question extends CommonItem implements Markable
{
    /**
     * @var string $question The wording of this question
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $question;

    /**
     * @var array $propositions An array of Proposition
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\MultipleChoice\Proposition>")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $propositions = array();

    /**
     * @var boolean
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "corrected", "item"})
     */
    private $doNotShuffle;

    /**
     * @var int $originResource The resource from which the question is taken
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $originResource;

    /**
     * Shuffle the order of the propositions
     */
    public function shufflePropositionsOrder()
    {
        if ($this->doNotShuffle !== true) {
            shuffle($this->propositions);
        }
    }

    /**
     * Get question
     *
     * @return string Th wording of the question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set question
     *
     * @param string $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * Add an proposition
     *
     * @param boolean $right True if this is a right proposition, false else.
     * @param string  $text  The proposition text
     * @param null    $ticked
     */
    public function addProposition($right, $text, $ticked = null)
    {
        $this->propositions[] = new Proposition($text, $right, $ticked);
    }

    /**
     * Get propositions
     *
     * @return array An array of Proposition.
     */
    public function getPropositions()
    {
        return $this->propositions;
    }

    /**
     * Tick or detick one proposition
     *
     * @param int     $key
     * @param boolean $tick
     */
    public function setTicked($key, $tick)
    {
        $this->propositions[$key]->setTicked($tick);
    }

    /**
     * Set doNotShuffle
     *
     * @param boolean $doNotShuffle
     */
    public function setDoNotShuffle($doNotShuffle)
    {
        $this->doNotShuffle = $doNotShuffle;
    }

    /**
     * Get doNotShuffle
     *
     * @return boolean
     */
    public function getDoNotShuffle()
    {
        return $this->doNotShuffle;
    }

    /**
     * Set originResource
     *
     * @param int $originResource
     */
    public function setOriginResource($originResource)
    {
        $this->originResource = $originResource;
    }

    /**
     * Get originResource
     *
     * @return int
     */
    public function getOriginResource()
    {
        return $this->originResource;
    }
}
