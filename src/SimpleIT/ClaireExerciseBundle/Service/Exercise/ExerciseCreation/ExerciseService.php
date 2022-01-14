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

use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Answer;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseModel\ExerciseModelServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;

/**
 * Service which manages the exercise generation
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseService extends TransactionalService implements ExerciseServiceInterface
{

    /**
     * @var  ExerciseModelServiceInterface
     */
    private $exerciseModelService;

    /**
     * @var ExerciseCreationServiceInterface
     */
    private $multipleChoiceService;

    /**
     * @var ExerciseCreationServiceInterface
     */
    private $pairItemsService;

    /**
     * @var ExerciseCreationServiceInterface
     */
    private $groupItemsService;

    /**
     * @var ExerciseCreationServiceInterface
     */
    private $orderItemsService;

    /**
     * @var ExerciseCreationServiceInterface
     */
    private $openEndedQuestionService;

    /**
     * Set ExerciseModelService
     *
     * @param ExerciseModelServiceInterface $exerciseModelService
     */
    public function setExerciseModelService(ExerciseModelServiceInterface $exerciseModelService)
    {
        $this->exerciseModelService = $exerciseModelService;
    }

    /**
     * Set MultipleChoiceService
     *
     * @param MultipleChoiceService $mcService
     */
    public function setMultipleChoiceService(MultipleChoiceService $mcService)
    {
        $this->multipleChoiceService = $mcService;
    }

    /**
     * Set PairItemsService
     *
     * @param PairItemsService $pairItemsService
     */
    public function setPairItemsService(PairItemsService $pairItemsService)
    {
        $this->pairItemsService = $pairItemsService;
    }

    /**
     * Set GroupItemsService
     *
     * @param GroupItemsService $groupItemsService
     */
    public function setGroupItemsService(GroupItemsService $groupItemsService)
    {
        $this->groupItemsService = $groupItemsService;
    }

    /**
     * Set OrderItemsService
     *
     * @param OrderItemsService $orderItemsService
     */
    public function setOrderItemsService(OrderItemsService $orderItemsService)
    {
        $this->orderItemsService = $orderItemsService;
    }

    /**
     * Set openEndedQuestionService
     *
     * @param OpenEndedQuestionService $openEndedQuestionService
     */
    public function setOpenEndedQuestionService($openEndedQuestionService)
    {
        $this->openEndedQuestionService = $openEndedQuestionService;
    }

    /**
     * Correct an item from an exercise
     *
     * @param Answer $answer Answer
     *
     * @return ItemResource The corrected item
     * @throws \Exception
     */
    public function correctItem(Answer $answer)
    {
        $item = $answer->getItem();
        $type = $item->getType();

        // depending on the type of the item, call the service
        $service = $this->getServiceFromType($type);

        $correctedItem = $service->correct($item, $answer);
        $correctedItem->setCorrected(true);

        return $correctedItem;
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
        // depending on the type of the item, call the service
        $service = $this->getServiceFromType($itemResource->getType());

        return $service->noSolutionItem($itemResource);
    }

    /**
     * Generate an exercise from the id of the model
     *
     * @param ExerciseModel $exerciseModel
     *
     * @return CommonExercise The generated exercise
     */
    public function generateExercise($exerciseModel)
    {
        $commonModel = $this->exerciseModelService->getModelFromEntity($exerciseModel);

        $service = $this->getServiceFromType($exerciseModel->getType());

        return $service->generateExerciseFromExerciseModel(
            $exerciseModel,
            $commonModel,
            $exerciseModel->getOwner()
        );
    }

    /**
     * Validate learner's answer format
     *
     * @param Item           $item
     * @param AnswerResource $answerResource
     */
    public function validateAnswer(Item $item, AnswerResource $answerResource)
    {
        $service = $this->getServiceFromType($item->getType());
        $service->validateAnswer($item, $answerResource->getContent());
    }

    /**
     * Select the right exercise service according to the type
     *
     * @param string $type
     *
     * @return ExerciseCreationServiceInterface
     * @throws \LogicException
     */
    private function getServiceFromType($type)
    {
        $service = null;
        switch ($type) {
            case CommonExercise::MULTIPLE_CHOICE:
                $service = $this->multipleChoiceService;
                break;
            case CommonExercise::PAIR_ITEMS:
                $service = $this->pairItemsService;
                break;
            case CommonExercise::GROUP_ITEMS:
                $service = $this->groupItemsService;
                break;
            case CommonExercise::ORDER_ITEMS:
                $service = $this->orderItemsService;
                break;
            case CommonExercise::OPEN_ENDED_QUESTION:
                $service = $this->openEndedQuestionService;
                break;
            case null:
                throw new \LogicException('The type of exercise should be specified');
            default :
                throw new \DomainException('Unknown type of item');

        }

        return $service;
    }
}
