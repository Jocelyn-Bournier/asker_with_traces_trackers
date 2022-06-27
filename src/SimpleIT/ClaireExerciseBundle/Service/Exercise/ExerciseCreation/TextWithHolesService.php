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

use Claroline\CoreBundle\Entity\User;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Answer;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidAnswerException;
use SimpleIT\ClaireExerciseBundle\Model\ExerciseObject\ExerciseTextWithHoles;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Exercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\TextWithHoles\Model;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice\QuestionBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextWithHolesResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResourceFactory;

/**
 * Service which manages Text With Holes Exercises.
 *
 * @author Valentin Lachand-Pascal <valentin.lachand@liris.cnrs.fr>
 */
class TextWithHolesService extends ExerciseCreationService
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
        $exercise = $this->generateTWHxercise($commonModel, $owner);

        // Transformation of the exercise into entities (StoredExercise and Items)
        return $this->toStoredExercise(
            $exercise,
            $exerciseModel,
            "text-with-holes",
            $exercise->getTextWithHoles()
        );
    }

    /**
     * Generate a text with holes exercise from a model
     *
     * @param Model $model
     * @param AskerUser $owner
     *
     * @return Exercise
     */
    private function generateTWHxercise(Model $model, AskerUser $owner)
    {
        $exercise = new Exercise($model->getWording());

        $this->setItems($exercise, $model);

        // Documents
        $this->addDocuments($model, $exercise, $owner);


        $exercise->finalize();

        return $exercise;
    }

    private function setItems(Exercise $exercise, Model $model){
        $ressource = $model->getRessources()[array_rand($model->getRessources())];
        //foreach($model->getRessources() as $ressource){
            $res = $this->exerciseResourceService->get($ressource);
            $exercise->addItem($res, $model);
        //}
    }

    /**
     * Correct the multiple choice question
     *
     * @param Item $item
     * @param Answer $answer
     *
     * @return ItemResource
     */
    public function correct(Item $item, Answer $answer)
    {
        $itemResource = ItemResourceFactory::create($item);

        /** @var \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Item $twh */
        $twh = $itemResource->getContent();

        $this->mark($twh, $answer);
        $twh->setAnswers(json_decode($answer->getContent())->content);

        $itemResource->setContent($twh);

        return $itemResource;
    }

    /**
     * Compute and set mark to the question
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Item $item
     */
    private function mark(\SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Item &$item, Answer $answer)
    {
        $mark = 0;
        $cpt = 0;
        foreach ($item->getHoles() as $hole){
            if($hole['answer'] == json_decode($answer->getContent())->content[$cpt]){
                $mark ++;
            }
            $cpt ++;
        }

        $item->setMark(($mark/($cpt))*100);
    }

    /**
     * Validate the answer to an item
     *
     * @param Item $itemEntity
     * @param array $answer
     *
     * @throws InvalidAnswerException
     */
    public function validateAnswer(Item $itemEntity, array $answer)
    {

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
        /** @var \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Item $content */
        $content = $itemResource->getContent();
        return $itemResource;
    }
}
