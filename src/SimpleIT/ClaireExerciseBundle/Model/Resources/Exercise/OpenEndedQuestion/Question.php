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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OpenEndedQuestion;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonItem;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\Markable;

/**
 * An ExerciseQuestion is a question of openEndedQuestion exercise in its final version,
 * ready to be used in an exercise.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Question extends CommonItem implements Markable
{
    /**
     * @var string $question The wording of this question
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $question;

    /**
     * @var array $answerFormat
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $answerFormat;

    /**
     * @var array $solutions An array of string
     * @Serializer\Type("array<string>")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $solutions = array();

    /**
     * @var string $answer The answer of the learner
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $answer;

    /**
     * @var int $originResource The resource from which the question is taken
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $originResource;

    /**
     * Set answer
     *
     * @param string $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    /**
     * Get answer
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
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
     * Set solutions
     *
     * @param array $solutions
     */
    public function setSolutions($solutions)
    {
        $this->solutions = $solutions;
    }

    /**
     * Get solutions
     *
     * @return array
     */
    public function getSolutions()
    {
        return $this->solutions;
    }

    /**
     * Set answerFormat
     *
     * @param array $answerFormat
     */
    public function setAnswerFormat($answerFormat)
    {
        $this->answerFormat = $answerFormat;
    }

    /**
     * Get answerFormat
     *
     * @return array
     */
    public function getAnswerFormat()
    {
        return $this->answerFormat;
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
