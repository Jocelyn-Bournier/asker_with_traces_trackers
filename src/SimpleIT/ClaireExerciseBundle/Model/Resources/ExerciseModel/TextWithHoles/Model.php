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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\TextWithHoles;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;

/**
 * A short answer question model. It contains blocks of questions and a parameter to
 * indicate if the question have to be shuffled before formatting the final
 * version of the multiple choice.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Model extends CommonModel
{
    /**
     * @var array $sharedTags
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $sharedTags = array();

    /**
     * @var array $sharedConstraints
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $sharedConstraints = array();

    /**
     * @var array $annotationsLists
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $annotationsLists = array();

    /**
     * @var array $coverages
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $coverages = array();

    /**
     * @var string $responsesTag
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $responsesTag = "";


    /**
     * Set shared constraint
     *
     * @param array $sharedConstraints An array of shared constraints
     */
    public function setSharedConstraints($sharedConstraints)
    {
        $this->sharedConstraints = $sharedConstraints;
    }

    /**
     * Set shared tags
     *
     * @param array $sharedTags An array of shared tags
     */
    public function setSharedTags($sharedTags)
    {
        $this->sharedTags = $sharedTags;
    }

    /**
     * Set annotations lists
     *
     * @param array $annotationsLists An array of annotations lists
     */
    public function setAnnotationsLists($annotationsLists)
    {
        $this->annotationsLists = $annotationsLists;
    }

    /**
     * Set coverages
     *
     * @param array $coverages An array of coverages
     */
    public function setCoverages($coverages)
    {
        $this->coverages = $coverages;
    }

    /**
     * Set responses tag
     *
     * @param string $responsesTag the string of tag for generating responses
     */
    public function setResponsesTag($responsesTag)
    {
        $this->responsesTag = $responsesTag;
    }

    /**
     * Get the shared constraints
     *
     * @return array An array of shared constraints
     */
    public function getSharedConstraints()
    {
        return $this->sharedConstraints;
    }

    /**
     * Get the shared tags
     *
     * @return array An array of shared tags
     */
    public function getSharedTags()
    {
        return $this->sharedTags;
    }

    /**
     * Set annotations lists
     *
     * @return array  An aray of annotations lists
     */
    public function getAnnotationsLists()
    {
        return $this->annotationsLists;
    }

    /**
     * get coverages
     *
     * @param array An array of coverages
     */
    public function getCoverages()
    {
        return $this->coverages;
    }

    /**
     * get responses tag
     *
     * @param string the string of tag for generating responses
     */
    public function getResponsesTag()
    {
        return $this->responsesTag;
    }

}
