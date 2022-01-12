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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;

/**
 * A multiple choice model. It contains blocks of questions and a parameter to
 * indicate if the question have to be shuffled before formatting the final
 * version of the multiple choice.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Model extends CommonModel
{
    /**
     * @var array $questionBlocks An array of QuestionBlock
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice\QuestionBlock>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $questionBlocks = array();

    /**
     * @var boolean $shuffleQuestionsOrder True if the questions have to be shuffled
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $shuffleQuestionsOrder;

    /**
     * Get the question blocks
     *
     * @return array An aray of QuestionBlock
     */
    public function getQuestionBlocks()
    {
        return $this->questionBlocks;
    }

    /**
     * Set question blocks
     *
     * @param array $questionBlocks An aray of QuestionBlock
     */
    public function setQuestionBlocks($questionBlocks)
    {
        $this->questionBlocks = $questionBlocks;
    }

    /**
     * Add a question block
     *
     * @param QuestionBlock $questionBlock
     */
    public function addQuestionBlock(QuestionBlock $questionBlock)
    {
        $this->questionBlocks[] = $questionBlock;
    }

    /**
     * Get shuffleQuestionsOrder
     *
     * @return boolean
     */
    public function isShuffleQuestionsOrder()
    {
        return $this->shuffleQuestionsOrder;
    }

    /**
     * Set shuffleQuestionsOrder
     *
     * @param boolean $shuffleQuestionsOrder
     */
    public function setShuffleQuestionsOrder($shuffleQuestionsOrder)
    {
        $this->shuffleQuestionsOrder = $shuffleQuestionsOrder;
    }
}
