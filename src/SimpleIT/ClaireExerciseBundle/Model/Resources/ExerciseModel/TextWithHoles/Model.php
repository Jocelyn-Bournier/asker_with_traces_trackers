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
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextWithHolesResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectConstraints;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseResource\ExerciseResourceServiceInterface;

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
     * @var bool $isList
     * @Serializer\Type("bool")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $isList = false;

    /**
     * @var array $sharedTags
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $sharedTags = array();

    /**
     * The object constraints. If it is null, the Block contains a
     * list of ObjectId
     *
     * @var ObjectConstraints $resourceConstraint
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectConstraints")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    protected $resourceConstraint = null;

    /**
     * @var array $sharedConstraints
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $sharedConstraints = array();

    /**
     * @var array $ressources
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $ressources = array();

    /**
     * @var array $holes
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $holes = array();

    /**
     * @var array $answers
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $answers = array();

    /**
     * @var array $indications
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $indications = array();

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
     * @var string $answerModality
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $answerModality = "holes";

    /**
     * @var bool $generateIndication
     * @Serializer\Type("bool")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $generateIndication = false;

    /**
     * @var bool $initAsIndication
     * @Serializer\Type("bool")
     * @Serializer\Groups({"details", "exercise_model_storage"})
    */
    private $initAsIndication = false;

    /**
     * @var string $indicationKey
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $indicationKey = null;

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
     * Set ressources
     *
     * @param array $ressources the array of ressources
     */
    public function setRessources($ressources)
    {
        $this->ressources = $ressources;
    }


    /**
     * Set answer modality
     *
     * @param string $answerModality the string of tag for generating responses
     */
    public function setAnswerModality(string $answerModality)
    {
        $this->answerModality = $answerModality;
    }

    /**
     * Set generation of indications
     *
     * @param bool $genearateIndication
     */
    public function setGenerateIndication(bool $genearateIndication)
    {
        $this->generateIndication = $genearateIndication;
    }

    /**
     * Set init as indication
     *
     * @param bool $initAsIndication
     */
    public function setInitAsIndication(bool $initAsIndication)
    {
        $this->initAsIndication = $initAsIndication;
    }

    /**
     * Set key of indication
     *
     * @param bool $indicationKey
     */
    public function setIndicationKey(string $indicationKey)
    {
        $this->indicationKey = $indicationKey;
    }

    /**
     * Set generation of indications
     *
     * @return bool generation of indication
     */
    public function getGenerateIndication()
    {
        return $this->generateIndication;
    }

    /**
     * Set init as indication
     *
     * @return bool init as indication
     */
    public function getInitAsIndication()
    {
        return $this->initAsIndication;
    }

    /**
     * get key of indication
     *
     * @return bool key of indication
     */
    public function getIndicationKey()
    {
        return $this->indicationKey;
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
     * @return array An array of coverages
     */
    public function getCoverages()
    {
        return $this->coverages;
    }

    /**
     * get responses tag
     *
     * @return string the string of tag for generating responses
     */
    public function getResponsesTag()
    {
        return $this->responsesTag;
    }

    /**
     * get answer modality
     *
     * @return string  the string of answer modality
     */
    public function getAnswerModality()
    {
        return $this->answerModality;
    }

    /**
     * get ressources
     *
     * @param array the array of ressources
     */
    public function getRessources()
    {
        return $this->ressources;
    }

    /**
     * @return array
     */
    public function getHoles(): array
    {
        return $this->holes;
    }

    /**
     * @param array $holes
     */
    public function setHoles(array $holes): void
    {
        $this->holes = $holes;
    }

    /**
     * @return array
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * @param array $answers
     */
    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }

    /**
     * @return array
     */
    public function getIndications(): array
    {
        return $this->indications;
    }

    /**
     * @param array $indications
     */
    public function setIndications(array $indications): void
    {
        $this->indications = $indications;
    }

    /**
     * @return ObjectConstraints
     */
    public function getResourceConstraint(): ?ObjectConstraints
    {
        return $this->resourceConstraint;
    }

    /**
     * @param ObjectConstraints $resourceConstraint
     */
    public function setResourceConstraint(?ObjectConstraints $resourceConstraint): void
    {
        $this->resourceConstraint = $resourceConstraint;
    }

    /**
     * @return bool
     */
    public function isList(): bool
    {
        return $this->isList;
    }

    /**
     * @param bool $isList
     */
    public function setIsList(bool $isList): void
    {
        $this->isList = $isList;
    }




}
