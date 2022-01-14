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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;

/**
 * A Group Items exercises model. It containts one or more blocks of objects.
 * It also describes the group names and how to group the objects. An option
 * allows to choose if the names of the groups must be shown, hidden or asked.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Model extends CommonModel
{
    /**
     * @const SHOW = "show"
     */
    const SHOW = "show";

    /**
     * @const HIDE = "hide"
     */
    const HIDE = "hide";

    /**
     * @const ASK = "ask"
     */
    const ASK = "ask";

    /**
     * @var array $objectBlocks An array of ObjectBlock
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\ObjectBlock>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $objectBlocks = array();

    /**
     * @var string $displayGroupNames show, hide, ask
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $displayGroupNames;

    /**
     * @var ClassificationConstraints $classifConstr
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\ClassificationConstraints")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $classifConstr = null;

    /**
     * Get the object blocks
     *
     * @return array An aray of QuestionBlock
     */
    public function getObjectBlocks()
    {
        return $this->objectBlocks;
    }

    /**
     * Set object blocks
     *
     * @param array $objectBlocks An aray of QuestionBlock
     */
    public function setObjectBlocks($objectBlocks)
    {
        $this->objectBlocks = $objectBlocks;
    }

    /**
     * Add an object block
     *
     * @param ObjectBlock $objectBlock
     */
    public function addObjectBlock(ObjectBlock $objectBlock)
    {
        $this->objectBlocks[] = $objectBlock;
    }

    /**
     * Get DisplayGroupNames
     *
     * @return int displayGroupNames
     */
    public function getDisplayGroupNames()
    {
        return $this->displayGroupNames;
    }

    /**
     * Set displayGroupNames
     *
     * @param int $displayGroupNames
     */
    public function setDisplayGroupNames($displayGroupNames)
    {
        $this->displayGroupNames = $displayGroupNames;
    }

    /**
     * Get global classification constraints
     *
     * @return ClassificationConstraints
     */
    public function getClassifConstr()
    {
        return $this->classifConstr;
    }

    /**
     * Set global classification constraints
     *
     * @param ClassificationConstraints $classifConstr
     */
    public function setClassifConstr($classifConstr)
    {
        $this->classifConstr = $classifConstr;
    }
}
