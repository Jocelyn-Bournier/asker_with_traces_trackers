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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;

/**
 * Model of a OrderItems exercise.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Model extends CommonModel
{
    /**
     * @const ASCENDENT = "asc"
     */
    const ASCENDENT = "asc";

    /**
     * @const DESCENDENT = "desc"
     */
    const DESCENDENT = "desc";

    /**
     * @var SequenceBlock $sequenceBlock
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\SequenceBlock")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $sequenceBlock = null;

    /**
     * @var array $objectBlocks An array of ObjectBlock
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\ObjectBlock>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $objectBlocks = array();

    /**
     * @var bool $isSequence
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    protected $isSequence;

    /**
     * @var boolean $giveFirst Precise if the first element must be revealed in the exercise
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $giveFirst = null;

    /**
     * @var boolean $giveLast Precise if the last element must be revealed in the exercise
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $giveLast = null;

    /**
     * @var string $order The order (asc or desc) in the case of an object list
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $order = null;

    /**
     * @var boolean $showValues Show the values in the case of an object list
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $showValues = null;

    /**
     * Get sequenceBlock
     *
     * @return SequenceBlock
     */
    public function getSequenceBlock()
    {
        return $this->sequenceBlock;
    }

    /**
     * Set sequenceBlock
     *
     * @param SequenceBlock $sequenceBlock
     *
     * @throws \Exception
     */
    public function setSequenceBlock($sequenceBlock)
    {
        $this->sequenceBlock = $sequenceBlock;
        if (!empty($this->objectBlocks)) {
            throw new \LogicException("Both sequenceBlock and objectBlock cannot be filled");
        }
    }

    /**
     * Get objectBlocks
     *
     * @return array An array of ObjectBlock
     */
    public function getObjectBlocks()
    {
        return $this->objectBlocks;
    }

    /**
     * Set objectBlocks
     *
     * @param array $objectBlocks An array of ObjectBlock
     *
     * @throws \Exception
     */
    public function setObjectBlocks($objectBlocks)
    {
        $this->objectBlocks = $objectBlocks;
        if (!is_null($this->sequenceBlock)) {
            throw new \LogicException("Both sequenceBlock and objectBlock cannot be filled");
        }
    }

    /**
     * Add an objectBlock
     *
     * @param ObjectBlock $objectBlock
     *
     * @throws \Exception
     */
    public function addObjectBlock(ObjectBlock $objectBlock)
    {
        $this->objectBlocks[] = $objectBlock;
        if (!is_null($this->sequenceBlock)) {
            throw new \LogicException("Both sequenceBlock and objectBlock cannot be filled");
        }
    }

    /**
     * Get giveFirst
     *
     * @return boolean
     */
    public function isGiveFirst()
    {
        return $this->giveFirst;
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
     * Get giveLast
     *
     * @return boolean
     */
    public function isGiveLast()
    {
        return $this->giveLast;
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
     * Get order
     *
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order
     *
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Set showValues
     *
     * @param boolean $showValues
     */
    public function setShowValues($showValues)
    {
        $this->showValues = $showValues;
    }

    /**
     * Get showValues
     *
     * @return boolean
     */
    public function getShowValues()
    {
        return $this->showValues;
    }

    /**
     * Set isSequence
     *
     * @param boolean $isSequence
     */
    public function setIsSequence($isSequence)
    {
        $this->isSequence = $isSequence;
    }

    /**
     * Get isSequence
     *
     * @return boolean
     */
    public function getIsSequence()
    {
        return $this->isSequence;
    }
}
