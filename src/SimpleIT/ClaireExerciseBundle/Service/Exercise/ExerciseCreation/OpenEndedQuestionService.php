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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseCreation;

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Answer;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidAnswerException;
use SimpleIT\ClaireExerciseBundle\Model\ExerciseObject\OpenEndedQuestion;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OpenEndedQuestion\Exercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OpenEndedQuestion\Question;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OpenEndedQuestion\Model;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OpenEndedQuestion\QuestionBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResourceFactory;

/**
 * Service which manages OpenEndedQuestion
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class OpenEndedQuestionService extends ExerciseCreationService
{
    /**
     * @inheritdoc
     */
    public function generateExerciseFromExerciseModel(
        ExerciseModel $exerciseModel,
        CommonModel $commonModel,
        AskerUser $owner
    )
    {
        /** @var Model $commonModel */
        // Generation of the exercise with the model
        $exercise = $this->generateOEQExercise($commonModel, $owner);

        // Transformation of the exercise into entities (StoredExercise and Items)
        return $this->toStoredExercise(
            $exercise,
            $exerciseModel,
            "open-ended-question",
            $exercise->getQuestions()
        );
    }

    /**
     * Generate a open ended question exercise from a model
     *
     * @param Model $model
     * @param AskerUser  $owner
     *
     * @return Exercise
     */
    private function generateOEQExercise(Model $model, AskerUser $owner)
    {
        // Formulas
        $variables = $this->computeFormulaVariableValues($model->getFormulas(), $owner);
        $wording = $this->parseStringWithVariables($model->getWording(), $variables);

        $exercise = new Exercise($wording);

        // Documents
        $this->addDocuments($model, $exercise, $owner);

        // array to collect all the questions to add
        $modelQuestionToAdd = array();

        // get the blocks
        foreach ($model->getQuestionBlocks() as $block) {
            $this->addQuestionsFromBlock($block, $modelQuestionToAdd, $owner);
        }

        // Create and add the exercise questions
        $this->addQuestionsToTheExercise($modelQuestionToAdd, $exercise);

        // shuffle the order of the questions (if needed) and the propositions
        if ($model->isShuffleQuestionsOrder()) {
            $exercise->shuffleQuestionOrder();
        }

        $exercise->finalize();

        return $exercise;
    }

    /**
     * Add questions from an array of OpenEndedQuestion to the Exercise
     *
     * @param array    $modelQuestionToAdd The array of questions
     * @param Exercise $exercise           The Exercise
     */
    private function addQuestionsToTheExercise(
        array $modelQuestionToAdd,
        Exercise &$exercise
    )
    {
        /** @var OpenEndedQuestion $modelQuestion */
        foreach ($modelQuestionToAdd as $modelQuestion) {
            // initialize the variables
            $variables = $this->computeFormulaVariableValues(
                $modelQuestion->getFormulas()
            );

            $exerciseQuestion = new Question();

            $exerciseQuestion->setOriginResource($modelQuestion->getOriginResource());

            $exerciseQuestion->setQuestion(
                $this->parseStringWithVariables($modelQuestion->getQuestion(), $variables)
            );

            $exerciseQuestion->setComment(
                $this->parseStringWithVariables($modelQuestion->getComment(), $variables)
            );

            $exerciseQuestion->setSolutions(
                $this->parseArrayWithVariables($modelQuestion->getSolutions(), $variables)
            );

            $exerciseQuestion->setAnswerFormat(
                $this->formulaService->getValueArrayFormat($exerciseQuestion->getSolutions())
            );

            $exercise->addQuestion($exerciseQuestion);
        }
    }

    /**
     * Add questions to the question-to-add list from a questionBlock
     *
     * @param QuestionBlock $questionBlock
     * @param array         $modelQuestionToAdd
     * @param AskerUser          $owner
     */
    private function addQuestionsFromBlock(
        QuestionBlock $questionBlock,
        &$modelQuestionToAdd,
        AskerUser $owner
    )
    {
        // get the questions from the block
        $blockQuestions = $this->questionsFromBlock($questionBlock, $owner);

        foreach ($blockQuestions as $question) {
            /** @var OpenEndedQuestion $question */
            $modelQuestionToAdd[] = $question;
        }
    }

    /**
     * Retrieve OpenEndedQuestions from a question block
     *
     * @param QuestionBlock $questionBlock
     * @param AskerUser          $owner
     *
     * @return array An array of OpenEndedQuestion
     */
    private function questionsFromBlock(QuestionBlock $questionBlock, AskerUser $owner)
    {
        $blockQuestions = array();
        $numOfQuestions = $questionBlock->getNumberOfOccurrences();

        // if the block is a list
        if ($questionBlock->isList()) {
            $this->getObjectsFromList($questionBlock, $numOfQuestions, $blockQuestions, $owner);
        } // if the block is question constraints
        else {
            $oc = $questionBlock->getResourceConstraint();
            $oc->setType(CommonResource::OPEN_ENDED_QUESTION);
            $blockQuestions = $this->exerciseResourceService
                ->getExerciseObjectsFromConstraints(
                    $oc,
                    $numOfQuestions,
                    $owner
                );
        }

        return $blockQuestions;
    }

    /**
     * Correct the open ended question
     *
     * @param Item   $item
     * @param Answer $answer
     *
     * @return ItemResource
     */
    public function correct(Item $item, Answer $answer)
    {
        $itemResource = ItemResourceFactory::create($item);
        /** @var Question $question */
        $question = $itemResource->getContent();

        $la = AnswerResourceFactory::create($answer);
        $userAnswer = $la->getContent()['answer'];
        $question->setAnswer($userAnswer);
        $this->mark($question);

        $itemResource->setContent($question);

        return $itemResource;
    }

    /**
     * Compute and set mark to the question
     *
     * @param Question $question
     */
    private function mark(Question &$question)
    {
        $mark = 100.0;
        if (!in_array($question->getAnswer(), $question->getSolutions(), true)) {
            $mark = 0;
        }

        if ($mark < 0) {
            $mark = 0;
        }

        $question->setMark($mark);
    }

    /**
     * Validate the answer to an item
     *
     * @param Item  $itemEntity
     * @param array $answer
     *
     * @throws InvalidAnswerException
     */
    public function validateAnswer(Item $itemEntity, array $answer)
    {
        /** @var Question $question */
        if (!is_string($answer['answer'])) {
            throw new InvalidAnswerException('Invalid number of objects in the answer');
        }
    }

    /**
     * Return an item without solution
     *
     * @param ItemResource $itemResource
     *
     * @return ItemResource
     */
    public function noSolutionItem($itemResource)
    {
        /** @var Question $content */
        $content = $itemResource->getContent();
        $content->setComment(null);
        $content->setSolutions(null);

        return $itemResource;
    }
}
