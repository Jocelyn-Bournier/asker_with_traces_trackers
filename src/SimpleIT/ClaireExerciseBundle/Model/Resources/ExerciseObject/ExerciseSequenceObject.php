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
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseSequenceObject extends ExerciseObject
{
    const OBJECT_TYPE = "sequence";

    /**
     * @var array $objects An array of ExerciseObject of the same type
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject>")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $objects;

    /**
     * The structure indicate the possible orders of the sequence. In some
     * blocks (sub-arrays), the elements can be switched (di) and in others,
     * the order must be kept (or). The objects from $objects are designated by
     * their index in the array. Example of structure :
     * array (
     *      'name' => 'or',
     *      0 => 0,
     *      1 => array (
     *          'name' => 'di',
     *          0 => array (
     *              'name' => 'or',
     *              0      => 1,
     *              1      => 2
     *            ),
     *          1 => 3,
     *          2 => 4
     *        ),
     *      2 => array (
     *          'name' => 'di',
     *          0 => 5,
     *          1 => 6
     *        ),
     *      3 => 7,
     *      4 => 8
     *  )
     *
     * @var array $structure The structure
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $structure;

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
     * Get objects
     *
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Set structure
     *
     * @param array $structure
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;
    }

    /**
     * Get structure
     *
     * @return array
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
