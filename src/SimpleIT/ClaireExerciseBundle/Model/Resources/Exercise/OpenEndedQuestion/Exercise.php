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
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;

/**
 * The exercise (final) version of the exercise with the final questions in the final order.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Exercise extends CommonExercise
{
    /**
     * @var array $questions An array of Question
     * @Serializer\Exclude
     */
    private $questions = array();

    /**
     * Shuffle the order of the questions
     */
    public function shuffleQuestionOrder()
    {
        shuffle($this->questions);
    }

    /**
     * Get question at index $key
     *
     * @param int $key The index
     *
     * @return Question The question
     */
    public function getQuestion($key)
    {
        return $this->questions[$key];
    }

    /**
     * Get questions
     *
     * @return array An array of ExerciseQuestion
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Add a question
     *
     * @param Question $question
     */
    public function addQuestion(Question $question)
    {
        $this->questions[] = $question;
    }

    /**
     * Get the number of questions
     *
     * @return int The number of questions
     */
    public function getNumberOfQuestions()
    {
        return count($this->questions);
    }

    /**
     * Compute the itemCount
     */
    public function finalize()
    {
        $this->itemCount = count($this->questions);
    }
}
