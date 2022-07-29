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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common;

use JMS\Serializer\Annotation as Serializer;

/**
 * Abstract class for the item in their final form.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 * @Serializer\Discriminator(field = "item_type", map = {
 *    "group-items": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Item",
 *    "pair-items": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems\Item",
 *    "order-items": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OrderItems\Item",
 *    "text-with-holes": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Item",
 *    "multiple-choice-question": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\MultipleChoice\Question",
 *    "open-ended-question": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OpenEndedQuestion\Question"
 * })
 */
abstract class CommonItem implements Markable
{
    /**
     * @var string $comment A comment linked with the question which will be displayed after the
     * correction
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    protected $comment;

    /**
     * @var boolean
     * @Serializer\Exclude
     */
    protected $allRight;

    /**
     * @var string
     * @Serializer\Exclude
     */
    protected $explanation;

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\Groups({"details", "corrected"})
     */
    protected $mark = null;

    /**
     * Set allRight
     *
     * @param boolean $allRight
     */
    public function setAllRight($allRight)
    {
        $this->allRight = $allRight;
    }

    /**
     * Get allRight
     *
     * @return boolean
     */
    public function getAllRight()
    {
        return $this->allRight;
    }

    /**
     * Set explanation
     *
     * @param string $explanation
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;
    }

    /**
     * Get explanation
     *
     * @return string
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Set comment
     *
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set mark
     *
     * @param float $mark
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    }

    /**
     * Get mark
     *
     * @return float
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * Check if the Markable has a mark
     *
     * @return boolean
     */
    public function isMarked()
    {
        return !is_null($this->mark);
    }
}
