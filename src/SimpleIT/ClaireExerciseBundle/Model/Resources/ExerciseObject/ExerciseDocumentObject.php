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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject;

use JMS\Serializer\Annotation as Serializer;

/**
 * A picture object in an exercise.
 * 
 */
class ExerciseDocumentObject extends ExerciseObject
{
    const OBJECT_TYPE = "document";

    /**
     * @var string $source The source of the picture
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage", "exercise_storage", "exercise"})
     */
    private $source;

    /**
     * Get source
     *
     * @return string The source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set source
     *
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }
}
