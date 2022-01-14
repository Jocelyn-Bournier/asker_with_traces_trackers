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

use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\StoredExercise;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\CreatedExercise\ItemRepository;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\CreatedExercise\StoredExerciseRepository;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseCreation\ExerciseServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseModel\ExerciseModelServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\Test\TestAttemptServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;

/**
 * Service which manages the stored exercises
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class StoredExerciseService extends TransactionalService implements StoredExerciseServiceInterface
{

    /**
     * @var StoredExerciseRepository $storedExerciseRepository
     */
    private $storedExerciseRepository;

    /**
     * @var TestAttemptServiceInterface
     */
    private $testAttemptService;

    /**
     * @var ItemRepository $itemRepository
     */
    private $itemRepository;

    /**
     * @var  ExerciseServiceInterface
     */
    private $exerciseService;

    /**
     * @var ExerciseModelServiceInterface
     */
    private $exerciseModelService;

    /**
     * Set itemRepository
     *
     * @param ItemRepository $itemRepository
     */
    public function setItemRepository($itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Set testRepository
     *
     * @param StoredExerciseRepository $storedExerciseRepository
     */
    public function setStoredExerciseRepository($storedExerciseRepository)
    {
        $this->storedExerciseRepository = $storedExerciseRepository;
    }

    /**
     * Set exerciseService
     *
     * @param ExerciseServiceInterface $exerciseService
     */
    public function setExerciseService($exerciseService)
    {
        $this->exerciseService = $exerciseService;
    }

    /**
     * Set exerciseModelService
     *
     * @param ExerciseModelServiceInterface $exerciseModelService
     */
    public function setExerciseModelService($exerciseModelService)
    {
        $this->exerciseModelService = $exerciseModelService;
    }

    /**
     * Set testAttemptService
     *
     * @param TestAttemptServiceInterface $testAttemptService
     */
    public function setTestAttemptService($testAttemptService)
    {
        $this->testAttemptService = $testAttemptService;
    }

    /**
     * Find a storedExercise by its id
     *
     * @param int $storedExerciseId Stored Exercise Id
     *
     * @throws NonExistingObjectException
     * @return StoredExercise
     */
    public function get($storedExerciseId)
    {
        $storedExercise = $this->storedExerciseRepository->find($storedExerciseId);
        if (is_null($storedExercise)) {
            throw new NonExistingObjectException();
        }

        return $storedExercise;
    }

    /**
     * Get all the stored exercises corresponding to an exercise model (if specified)
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $exerciseModelId
     *
     * @return array
     */
    public function getAll(
        $collectionInformation = null,
        $exerciseModelId = null
    )
    {
        $exerciseModel = null;

        if (!is_null($exerciseModelId)) {
            $exerciseModel = $this->exerciseModelService->get($exerciseModelId);
        }

        return $this->storedExerciseRepository->findAllBy(
            $collectionInformation,
            $exerciseModel
        );
    }

    /**
     * Get all by test attempt id
     *
     * @param int $testAttemptId
     * @param int $userId
     *
     * @return array
     */
    public function getAllByTestAttempt($testAttemptId, $userId = null)
    {
        $testAttempt = null;
        if (!is_null($testAttemptId)) {
            $testAttempt = $this->testAttemptService->get($testAttemptId, $userId);
        }

        return $this->storedExerciseRepository->findAllByTestAttempt($testAttempt);
    }

    /**
     * Add a new exercise model by owner exercise model id
     *
     * @param $emId
     *
     * @return StoredExercise
     */
    public function addByExerciseModel($emId)
    {
        $oem = $this->exerciseModelService->getParent($emId);

        $exercise = $this->exerciseService->generateExercise($oem);

        $this->em->persist($exercise);
        $this->em->flush();

        return $exercise;
    }
}
