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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonItem;

/**
 * Class ExerciseModelResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ItemResource
{
    /**
     * @const RESOURCE_NAME = 'Item'
     */
    const RESOURCE_NAME = 'Item';

    /**
     * @const MULTIPLE_CHOICE_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\MultipleChoice\Question'
     */
    const MULTIPLE_CHOICE_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\MultipleChoice\Question';

    /**
     * @const OPEN_ENDED_QUESTION_CLASS ='SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OpenEndedQuestion\Question'
     */
    const OPEN_ENDED_QUESTION_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OpenEndedQuestion\Question';

    /**
     * @const GROUP_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Item'
     */
    const GROUP_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Item';

    /**
     * @const GROUP_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Item'
     */
    const ORDER_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OrderItems\Item';

    /**
     * @const PAIR_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems\Item'
     */
    const PAIR_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems\Item';

    /**
     * @const TEXT_WITH_HOLES_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Item'
     */
    const TEXT_WITH_HOLES_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Item';

    /**
     * @var int $itemId Id of item
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "exercise", "list", "corrected", "not_corrected"})
     */
    private $itemId;

    /**
     * @var string $type
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "corrected", "not_corrected"})
     */
    private $type;

    /**
     * @var bool $corrected
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "list", "corrected", "not_corrected"})
     */
    private $corrected;

    /**
     * @var CommonItem $content
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonItem")
     * @Serializer\Groups({"details", "corrected", "not_corrected"})
     */
    private $content;

    /**
     * Set content
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonItem $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonItem
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get corrected
     *
     * @return boolean
     */
    public function getCorrected()
    {
        return $this->corrected;
    }

    /**
     * Set corrected
     *
     * @param boolean $corrected
     */
    public function setCorrected($corrected)
    {
        $this->corrected = $corrected;
    }

    /**
     * Get itemId
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set itemId
     *
     * @param int $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Return the item serialization class corresponding to the type
     *
     * @param string $type
     *
     * @return string
     * @throws \LogicException
     */
    static public function getClass($type)
    {
        switch ($type) {
            case CommonExercise::MULTIPLE_CHOICE:
                $class = self::MULTIPLE_CHOICE_CLASS;
                break;
            case CommonExercise::OPEN_ENDED_QUESTION:
                $class = self::OPEN_ENDED_QUESTION_CLASS;
                break;
            case CommonExercise::GROUP_ITEMS:
                $class = self::GROUP_ITEMS_CLASS;
                break;
            case CommonExercise::ORDER_ITEMS:
                $class = self::ORDER_ITEMS_CLASS;
                break;
            case CommonExercise::PAIR_ITEMS:
                $class = self::PAIR_ITEMS_CLASS;
                break;
            case CommonExercise::TEXT_WITH_HOLES:
                $class = self::TEXT_WITH_HOLES_CLASS;
                break;
            default:
                throw new \LogicException('Unknown type');
        }

        return $class;
    }
}
