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
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\ResourceBlock;

/**

/**
 * Class ObjectBlock
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ObjectBlock extends ResourceBlock
{
    /**
     * @var string $metaToDisplay Indicates the metadata field which content must be displayed
     * instead of the objects
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $metaToDisplay = null;

    /**
     * @var ClassificationConstraints $classifConstr
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\ClassificationConstraints")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $classifConstr = null;

    /**
     * Constructor
     *
     * @param int $numberOfOccurrences
     */
    function __construct($numberOfOccurrences)
    {
        $this->numberOfOccurrences = $numberOfOccurrences;
    }

    /**
     * Get ClassificationConstraints
     *
     * @return ClassificationConstraints
     */
    public function getClassifConstr()
    {
        return $this->classifConstr;
    }

    /**
     * Set ClassificationConstraints
     *
     * @param ClassificationConstraints $classifConstr
     */
    public function setClassifConstr($classifConstr)
    {
        $this->classifConstr = $classifConstr;
    }

    /**
     * Get Metadata to display
     *
     * @return string
     */
    public function getMetaToDisplay()
    {
        return $this->metaToDisplay;
    }

    /**
     * Set Metadata to display
     *
     * @param string $metaToDisplay
     */
    public function setMetaToDisplay($metaToDisplay)
    {
        $this->metaToDisplay = $metaToDisplay;
    }
}
