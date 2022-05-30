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
 * Abstract class for exercise objects (pictures, texts, ...)
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 * @Serializer\Discriminator(field = "object_type", map = {
 *    "picture":  "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExercisePictureObject",
 *    "text":     "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseTextObject",
 *    "sequence": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseSequenceObject"
 * })
 */
abstract class ExerciseObject
{
    const OBJECT_TYPE = "object type";

    /**
     * @var array $keywords An array of keyword
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected"})
     */
    protected $keywords;

    /**
     * @var array $metadata An array of metadata: key => value
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected"})
     */
    protected $metadata;

    /**
     * A value than can be used to describe the object (classify, order, ...)
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "corrected", "not_corrected"})
     */
    protected $metavalue = null;

    /**
     * @var array LocalFormulas
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\LocalFormula>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    protected $formulas;

    /**
     * @var int $originResource The resource from which the question is taken
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $originResource;

    /**
     * Get metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get keyword
     *
     * @return array
     */
    public function getKeyword()
    {
        return $this->keywords;
    }

    /**
     * Get metadata by key
     *
     * @param $key
     *
     * @return string The value
     */
    public function getMetadataByKey($key)
    {
        return $this->metadata[$key];
    }

    /**
     * Set metadata
     *
     * @param array $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Set keyword
     *
     * @param array $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * Add a $key - $value metadata
     *
     * @param string $key
     * @param string $value
     */
    public function addMetadata($key, $value)
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Add a $value keyword
     *
     * @param string $value
     */
    public function addKeyword($value)
    {
        array_push($this->keywords,$value);
    }

    /**
     * Set metavalue
     *
     * @param string $metavalue
     */
    public function setMetavalue($metavalue)
    {
        $this->metavalue = $metavalue;
    }

    /**
     * Get metavalue
     *
     * @return string
     */
    public function getMetavalue()
    {
        return $this->metavalue;
    }

    /**
     * Set formulas
     *
     * @param array $formulas
     */
    public function setFormulas($formulas)
    {
        $this->formulas = $formulas;
    }

    /**
     * Get formulas
     *
     * @return array
     */
    public function getFormulas()
    {
        return $this->formulas;
    }

    /**
     * Get the type of object
     *
     * @return string
     */
    public function getType()
    {
        return static::OBJECT_TYPE;
    }

    /**
     * Set originResource
     *
     * @param int $originResource
     */
    public function setOriginResource($originResource)
    {
        $this->originResource = $originResource;
    }

    /**
     * Get originResource
     *
     * @return int
     */
    public function getOriginResource()
    {
        return $this->originResource;
    }
}
