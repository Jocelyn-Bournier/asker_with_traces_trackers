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
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\ResourceBlock;

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
     * @var string $metaKey The key of the metadata field used to determine the order of the
     * sequence
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $metaKey;

    /**
     * Constructor
     *
     * @param int    $numberOfOccurrences
     * @param string $metaKey
     */
    function __construct($numberOfOccurrences, $metaKey)
    {
        $this->numberOfOccurrences = $numberOfOccurrences;
        $this->metaKey = $metaKey;
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

    /**
     * Get metaKey
     *
     * @return string
     */
    public function getMetaKey()
    {
        return $this->metaKey;
    }

    /**
     * Set metaKey
     *
     * @param string $metaKey
     */
    public function setMetaKey($metaKey)
    {
        $this->metaKey = $metaKey;
    }
}
