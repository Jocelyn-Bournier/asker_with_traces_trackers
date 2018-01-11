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

/**

/**
 * Describes how the groups must be chosen and how to determine the group of
 * every object.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ClassificationConstraints
{
    /**
     * The no-group objects are not used (can reduce the number of objects in
     * the exercise)
     *
     * @const REJECT = "reject"
     */
    const REJECT = "reject";

    /**
     * The no-group objects are classified in a group called "other"
     *
     * @const MISC = "misc"
     */
    const MISC = "misc";

    /**
     * @var string $other Indicate what to do with the values of the metadata
     *                 designated by the metaKey but not linked to a group.
     *                 Values in OWN, REJECT and MISC.
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $other;

    /**
     * @var array $groups An array of Group
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Group>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $groups = array();

    /**
     * Get Other
     *
     * @return int
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * Set other
     *
     * @param int $other
     */
    public function setOther($other)
    {
        $this->other = $other;
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set groups
     *
     * @param array $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * Add group
     *
     * @param Group $group
     */
    public function addGroup($group)
    {
        $this->groups[] = $group;
    }
}
