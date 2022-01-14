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
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\MetadataConstraint;

/**

/**
 * A Group is a category explicitely specified in which the learner will be
 * asked to classify objects. It has a name and constraints that determine what
 * objects come in.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Group
{
    /**
     * @var string $name
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $name;

    /**
     * @var array $mDConstraints An array of MetadataConstraint
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\MetadataConstraint>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     * @Serializer\SerializedName("metadata_constraints")
     */
    private $mDConstraints;

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get metadata constraints
     *
     * @return array An array of MetadataConstraint
     */
    public function getMDConstraints()
    {
        return $this->mDConstraints;
    }

    /**
     * Set Metadata Constraints list
     *
     * @param array $mDConstraints
     */
    public function setMDConstraints($mDConstraints)
    {
        $this->mDConstraints = $mDConstraints;
    }

    /**
     * Add Metadata Constraint
     *
     * @param MetadataConstraint $mDConstraint
     */
    public function addMDConstraint($mDConstraint)
    {
        $this->mDConstraints[] = $mDConstraint;
    }
}
