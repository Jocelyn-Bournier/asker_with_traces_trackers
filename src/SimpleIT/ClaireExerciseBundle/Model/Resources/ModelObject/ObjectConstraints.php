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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject;

use JMS\Serializer\Annotation as Serializer;

/**
 * Containts a set of constraints about resources.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ObjectConstraints
{
    /**
     * @var array $metadataConstraints An array of MatadataConstraint that must all be satisfied
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\MetadataConstraint>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $metadataConstraints = array();

    /**
     * @var array $excluded An array of ObjectId that should not be retrieved
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectId>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $excluded = array();

    /**
     * @var string $type The type of resource
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $type;

    /**
     * Checks if the metadata key if $key
     *
     * @param string $key
     *
     * @return boolean
     */
    public function hasMetaKey($key)
    {
        foreach ($this->metadataConstraints as $mc) {
            /** @var MetadataConstraint $mc */
            if ($mc->getKey() == $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add the existence constraint over the key $metaKey if it is not required
     * yet.
     *
     * @param string $metaKey
     */
    public function addExists($metaKey)
    {
        if (!$this->hasMetaKey($metaKey)) {
            $mdc = new MetadataConstraint();
            $mdc->build($metaKey, null, 'exists');
            $this->metadataConstraints[] = $mdc;
        }
    }

    /**
     * Get metadata constraints
     *
     * @return array An array of MatadataConstraint that must all be satisfied
     */
    public function getMetadataConstraints()
    {
        return $this->metadataConstraints;
    }

    /**
     * Set metadata constraints
     *
     * @param array $metadataConstraints An array of MatadataConstraint that must all be satisfied
     */
    public function setMetadataConstraints($metadataConstraints)
    {
        $this->metadataConstraints = $metadataConstraints;
    }

    /**
     * Add metadata constraints
     *
     * @param MetadataConstraint $metadataConstraint
     */
    public function addMetadataConstraint(MetadataConstraint $metadataConstraint)
    {
        $this->metadataConstraints[] = $metadataConstraint;
    }

    /**
     * Get excluded object ids
     *
     * @return array An array of ObjectId that should not be retrieved
     */
    public function getExcluded()
    {
        return $this->excluded;
    }

    /**
     * Set excluded
     *
     * @param array $excluded An array of ObjectId that should not be retrieved
     */
    public function setExcluded($excluded)
    {
        $this->excluded = $excluded;
    }

    /**
     * Add excluded
     *
     * @param ObjectId $excluded
     */
    public function addExcluded(ObjectId $excluded)
    {
        $this->excluded[] = $excluded;
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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
