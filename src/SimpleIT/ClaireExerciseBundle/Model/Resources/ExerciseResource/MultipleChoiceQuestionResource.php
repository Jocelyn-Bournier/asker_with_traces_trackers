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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\MultipleChoice\MultipleChoicePropositionResource;

/**
 * Class MultipleChoiceQuestionResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class MultipleChoiceQuestionResource extends CommonResource
{
    /**
     * @var string $question The wording of this question
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "resource_storage", "resource_list", "owner_resource_list"})
     */
    private $question;

    /**
     * @var array $propositions An array of Proposition
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\MultipleChoice\MultipleChoicePropositionResource>")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private $propositions = array();

    /**
     * @var string $comment A comment linked with the question which will be displayed after the
     * correction
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private $comment;

    /**
     * @var int The recommended max number of propositions when using this question
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private $maxNumberOfPropositions;

    /**
     * @var int The recommended max number of right propositions when using this question
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private $maxNumberOfRightPropositions;

    /**
     * @var boolean $doNotShuffle
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private $doNotShuffle;

    /**
     * Set comment
     *
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set propositions
     *
     * @param array $propositions
     */
    public function setPropositions($propositions)
    {
        $this->propositions = $propositions;
    }

    /**
     * Get propositions
     *
     * @return array
     */
    public function getPropositions()
    {
        return $this->propositions;
    }

    /**
     * Set question
     *
     * @param string $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * Get question
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set maxNumberOfPropositions
     *
     * @param int $maxNumberOfPropositions
     */
    public function setMaxNumberOfPropositions($maxNumberOfPropositions)
    {
        $this->maxNumberOfPropositions = $maxNumberOfPropositions;
    }

    /**
     * Get maxNumberOfPropositions
     *
     * @return int
     */
    public function getMaxNumberOfPropositions()
    {
        return $this->maxNumberOfPropositions;
    }

    /**
     * Set maxNumberOfRightPropositions
     *
     * @param int $maxNumberOfRightPropositions
     */
    public function setMaxNumberOfRightPropositions($maxNumberOfRightPropositions)
    {
        $this->maxNumberOfRightPropositions = $maxNumberOfRightPropositions;
    }

    /**
     * Get maxNumberOfRightPropositions
     *
     * @return int
     */
    public function getMaxNumberOfRightPropositions()
    {
        return $this->maxNumberOfRightPropositions;
    }

    /**
     * Set doNotShuffle
     *
     * @param boolean $doNotShuffle
     */
    public function setDoNotShuffle($doNotShuffle)
    {
        $this->doNotShuffle = $doNotShuffle;
    }

    /**
     * Get doNotShuffle
     *
     * @return boolean
     */
    public function getDoNotShuffle()
    {
        return $this->doNotShuffle;
    }

    /**
     * Validate the question resource.
     *
     * @throws \LogicException
     */
    public function  validate($param = null)
    {
        if ($this->question === null || $this->question == '') {
            throw new \LogicException('Invalid question');
        }

        $rightProp = 0;

        foreach ($this->propositions as $prop) {
            /** @var MultipleChoicePropositionResource $prop */
            if ($prop->getText() === null || $prop->getText() === "") {
                throw new \LogicException('Invalid proposition');
            }

            if ($prop->getRight() != true && $prop->getRight() != false) {
                throw new \LogicException('Proposition must be right or wrong');
            }

            if ($prop->getRight() == true) {
                $rightProp++;
            }
        }

        if ($this->maxNumberOfPropositions > count($this->propositions)) {
            throw new \LogicException('Invalid max number of propositions');
        }

        if ($this->maxNumberOfRightPropositions > $rightProp) {
            throw new \LogicException('Invalid max number of right propositions');
        }
    }
}
