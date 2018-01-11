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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\CreatedExercise;

use JMS\Serializer\SerializationContext;
use SimpleIT\ClaireExerciseBundle\Entity\AnswerFactory;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Exception\AnswerAlreadyExistsException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\CreatedExercise\AnswerRepository;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseCreation\ExerciseService;
use SimpleIT\ClaireExerciseBundle\Service\Serializer\SerializerInterface;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;

/**
 * Service which manages the stored exercises
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AnswerService extends TransactionalService implements AnswerServiceInterface
{
    /**
     * @var  ExerciseService
     */
    private $exerciseService;

    /**
     * @var ItemService
     */
    private $itemService;

    /**
     * @var AttemptServiceInterface
     */
    private $attemptService;

    /**
     * @var AnswerRepository
     */
    private $answerRepository;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Set serializer
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Set exerciseService
     *
     * @param ExerciseService $exerciseService
     */
    public function setExerciseService($exerciseService)
    {
        $this->exerciseService = $exerciseService;
    }

    /**
     * Set attemptService
     *
     * @param AttemptServiceInterface $attemptService
     */
    public function setAttemptService($attemptService)
    {
        $this->attemptService = $attemptService;
    }

    /**
     * Set answerRepository
     *
     * @param AnswerRepository $answerRepository
     */
    public function setAnswerRepository($answerRepository)
    {
        $this->answerRepository = $answerRepository;
    }

    /**
     * Set itemService
     *
     * @param ItemService $itemService
     */
    public function setItemService($itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * Create an answer to an item
     *
     * @param int            $itemId
     * @param AnswerResource $answerResource
     * @param int            $attemptId
     * @param int            $userId
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\AnswerAlreadyExistsException
     * @return ItemResource
     */
    public function add($itemId, AnswerResource $answerResource, $attemptId, $userId)
    {
        // Get the item and the attempt
        if (count($this->getAll($itemId, $attemptId)) > 0) {
            throw new AnswerAlreadyExistsException();
        }
        $attempt = $this->attemptService->get($attemptId, $userId);
        /** @var Item $item */
        $item = $this->itemService->getByAttempt($itemId, $attemptId);

        $this->exerciseService->validateAnswer($item, $answerResource);

        $context = SerializationContext::create();
        $context->setGroups(array("answer_storage", 'Default'));
        $content = $this->serializer->jmsSerialize(
            $answerResource,
            'json',
            $context
        );

        $answer = AnswerFactory::create($content, $item, $attempt);
        // Add the answer to the database

        $this->em->persist($answer);
        $this->em->flush();

        $itemResource = $this->itemService->findItemAndCorrectionByAttempt(
            $itemId,
            $attemptId,
            $userId
        );

        $answer->setMark($itemResource->getContent()->getMark());
        $this->em->flush();

        return $itemResource;

    }

    /**
     * Get all answers for an item
     *
     * @param int  $itemId Item id
     * @param int  $attemptId
     * @param null $userId
     *
     * @return array
     */
    public function getAll($itemId = null, $attemptId = null, $userId = null)
    {
        $item = null;
        $attempt = null;

        if (!is_null($itemId)) {
            if (!is_null($attemptId)) {
                $attempt = $this->attemptService->get($attemptId, $userId);
                $item = $this->itemService->getByAttempt($itemId, $attemptId);
            } else {
                $item = $this->itemService->get($itemId);
            }
        }

        return $this->answerRepository->findAllBy($item, $attempt);
    }
}
